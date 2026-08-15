<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\ActivationTokenModel;
use App\Models\EmailChangeRequestModel;
use App\Models\EmailChangeVerificationModel;
use App\Models\UserModel;
use App\Models\StudentProfileModel;
use App\Models\PasswordResetRequestModel;
use App\Models\StudentFeeModel;
use App\Models\SemesterModel;
use App\Models\PromotionLogModel;
use App\Models\AttendanceModel;
use App\Models\ProgramModel;
use App\Services\MailService;
use App\Services\StudentFeeService;
use DateTimeImmutable;
use DateTimeZone;
use PDO;

class ManagerController extends BaseController
{
    private UserModel $userModel;
    private ActivationTokenModel $activationTokens;
    private EmailChangeRequestModel $emailChangeRequests;
    private EmailChangeVerificationModel $emailChangeVerifications;
    private StudentProfileModel $studentModel;
    private PasswordResetRequestModel $resetModel;
    private StudentFeeModel $feeModel;
    private SemesterModel $semesterModel;
    private PromotionLogModel $promotionLogModel;
    private AttendanceModel $attendanceModel;
    private ProgramModel $programModel;
    private StudentFeeService $studentFeeService;
    private MailService $mailService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->activationTokens = new ActivationTokenModel();
        $this->emailChangeRequests = new EmailChangeRequestModel();
        $this->emailChangeVerifications = new EmailChangeVerificationModel();
        $this->studentModel = new StudentProfileModel();
        $this->resetModel = new PasswordResetRequestModel();
        $this->feeModel = new StudentFeeModel();
        $this->semesterModel = new SemesterModel();
        $this->promotionLogModel = new PromotionLogModel();
        $this->attendanceModel = new AttendanceModel();
        $this->programModel = new ProgramModel();
        $this->studentFeeService = new StudentFeeService();
        $this->mailService = new MailService();
    }

    // ============================================================================
    // DASHBOARD
    // ============================================================================

    public function showDashboard(): void
    {
        $stats = $this->getDashboardStats();
        $enrollmentSeries = $stats['enrollment_series'] ?? [];
        $statusPercent = $stats['status_percent'] ?? 0;
        
        // Get pending student password resets only (not teacher/admin)
        $pendingResets = $this->resetModel->where('status', 'PENDING');
        
        // Enrich pending resets with student information (filter for STUDENT role only)
        $enrichedResets = [];
        foreach ($pendingResets as $reset) {
            $user = $this->userModel->find((int) $reset['requested_by']);
            if ($user && strtoupper($user['role']) === 'STUDENT') {
                $reset['full_name'] = $user['full_name'];
                $reset['login_id'] = $user['login_id'];
                $enrichedResets[] = $reset;
            }
        }

        $pendingEmailChanges = [];
        $emailRequests = $this->emailChangeRequests->where('status', 'PENDING');
        foreach ($emailRequests as $request) {
            $user = $this->userModel->find((int) ($request['user_id'] ?? 0));
            if ($user && strtoupper($user['role']) === 'STUDENT') {
                $pendingEmailChanges[] = $request;
            }
        }

        $this->view('manager.dashboard', [
            'title' => 'Manager Dashboard',
            'pageSubtitle' => 'Student lifecycle management overview',
            'user_name' => (string) ($_SESSION['user_name'] ?? 'Manager'),
            'stats' => $stats,
            'pending_resets' => $enrichedResets,
            'pending_email_changes' => $pendingEmailChanges,
            'enrollment_series' => $enrollmentSeries,
            'status_percent' => $statusPercent,
        ]);
    }

    private function getDashboardStats(): array
    {
        // Get all students
        $students = $this->userModel->where('role', 'STUDENT');
        $totalStudents = count($students);

        // Count active students (filter from results, don't chain where())
        $activeStudents = array_filter($students, static fn($s) => (int) $s['is_active'] === 1);
        $totalActive = count($activeStudents);

        // Count inactive students
        $inactiveStudents = array_filter($students, static fn($s) => (int) $s['is_active'] === 0);
        $totalInactive = count($inactiveStudents);

        // Pending student password resets
        $allResets = $this->resetModel->where('status', 'PENDING');
        $pendingStudentResets = [];
        foreach ($allResets as $reset) {
            $user = $this->userModel->find((int) $reset['requested_by']);
            if ($user && strtoupper($user['role']) === 'STUDENT') {
                $pendingStudentResets[] = $reset;
            }
        }
        $pendingCount = count($pendingStudentResets);

        $pendingEmailRequests = $this->emailChangeRequests->where('status', 'PENDING');
        $pendingEmailCount = 0;
        foreach ($pendingEmailRequests as $request) {
            $user = $this->userModel->find((int) ($request['user_id'] ?? 0));
            if ($user && strtoupper($user['role']) === 'STUDENT') {
                $pendingEmailCount++;
            }
        }

        $profiles = $this->studentModel->all();
        $programCounts = [];
        foreach ($profiles as $profile) {
            $programId = (int) ($profile['program_id'] ?? 0);
            if ($programId === 0) {
                continue;
            }
            $programCounts[$programId] = ($programCounts[$programId] ?? 0) + 1;
        }

        arsort($programCounts);
        $topCounts = array_slice($programCounts, 0, 7, true);
        $maxCount = max($topCounts ?: [1]);
        $enrollmentSeries = [];
        foreach ($topCounts as $count) {
            $enrollmentSeries[] = (int) round(($count / $maxCount) * 100);
        }
        while (count($enrollmentSeries) < 7) {
            $enrollmentSeries[] = 0;
        }

        $statusPercent = $totalStudents > 0
            ? (int) round(($totalActive / $totalStudents) * 100)
            : 0;

        return [
            'total_students' => $totalStudents,
            'active_students' => $totalActive,
            'inactive_students' => $totalInactive,
            'pending_resets' => $pendingCount,
            'pending_email_changes' => $pendingEmailCount,
            'enrollment_series' => $enrollmentSeries,
            'status_percent' => $statusPercent,
        ];
    }

    // ============================================================================
    // PROFILE
    // ============================================================================

    public function showProfile(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->redirect('/login');
            return;
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            http_response_code(404);
            echo 'User not found';
            return;
        }

        $emailRequestPending = $this->emailChangeRequests->hasPendingForUser($userId);
        $emailVerification = $this->emailChangeVerifications->getActiveForUser($userId);
        $emailVerificationPending = $emailVerification !== null;
        $emailVerificationEmail = $emailVerificationPending ? (string) ($emailVerification['new_email'] ?? '') : '';
        $emailVerificationExpiresAt = $emailVerificationPending ? (string) ($emailVerification['expires_at'] ?? '') : '';

        $this->view('manager.profile', [
            'title' => 'My Profile',
            'pageSubtitle' => 'Manage your profile details',
            'user' => $user,
            'email_request_pending' => $emailRequestPending,
            'email_verification_pending' => $emailVerificationPending,
            'email_verification_email' => $emailVerificationEmail,
            'email_verification_expires_at' => $emailVerificationExpiresAt,
        ]);
    }

    public function updateProfile(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $fullName = trim((string) $this->input('full_name', ''));
        $phone = trim((string) $this->input('phone', ''));

        if ($fullName === '') {
            $this->json(['success' => false, 'message' => 'Full name is required.'], 422);
            return;
        }

        if (strlen($fullName) < 2 || strlen($fullName) > 150) {
            $this->json(['success' => false, 'message' => 'Full name must be between 2 and 150 characters.'], 422);
            return;
        }

        if (!preg_match('/^[A-Za-z\s]+$/', $fullName)) {
            $this->json(['success' => false, 'message' => 'Full name must contain letters only.'], 422);
            return;
        }

        if ($phone !== '' && !preg_match('/^\d{10}$/', $phone)) {
            $this->json(['success' => false, 'message' => 'Phone number must be exactly 10 digits.'], 422);
            return;
        }

        $user = $this->userModel->find($userId);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found.'], 404);
            return;
        }

        $this->userModel->updateById($userId, [
            'full_name' => $fullName,
            'phone' => $phone,
        ]);

        $_SESSION['user_name'] = $fullName;

        $this->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data' => ['full_name' => $fullName, 'phone' => $phone],
        ]);
    }

    public function requestEmailChange(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->userModel->find($userId);
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'MANAGER') {
            $this->json(['success' => false, 'message' => 'Forbidden'], 403);
            return;
        }

        if ($this->emailChangeRequests->hasPendingForUser($userId)) {
            $this->json(['success' => false, 'message' => 'You already have a pending email change request.'], 409);
            return;
        }

        $newEmail = trim((string) $this->input('new_email', ''));
        $confirmEmail = trim((string) $this->input('confirm_email', ''));

        if ($newEmail === '' || $confirmEmail === '') {
            $this->json(['success' => false, 'message' => 'New email and confirmation are required.'], 422);
            return;
        }

        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Please enter a valid email'], 422);
            return;
        }

        if (strcasecmp($newEmail, $confirmEmail) !== 0) {
            $this->json(['success' => false, 'message' => 'Email confirmation does not match.'], 422);
            return;
        }

        $currentEmail = (string) ($user['email'] ?? '');
        if ($currentEmail !== '' && strcasecmp($currentEmail, $newEmail) === 0) {
            $this->json(['success' => false, 'message' => 'New email must be different from the current email.'], 422);
            return;
        }

        $existing = $this->userModel->firstWhere('email', $newEmail);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'That email address is already in use.'], 409);
            return;
        }

        $this->emailChangeVerifications->clearActiveForUser($userId);

        $otp = (string) random_int(100000, 999999);
        $otpHash = hash('sha256', $otp);
        $expiresAt = gmdate('Y-m-d H:i:s', time() + 600);

        $this->emailChangeVerifications->create([
            'user_id' => $userId,
            'new_email' => $newEmail,
            'otp_hash' => $otpHash,
            'expires_at' => $expiresAt,
            'verified_at' => null,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);

        try {
            $name = (string) ($user['full_name'] ?? 'User');
            $subject = 'Verify Your New Email - IMS';
            $htmlBody = '<p>Hello ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>'
                . '<p>Use the OTP below to verify your new email address for your account.</p>'
                . '<p><strong>OTP: ' . htmlspecialchars($otp, ENT_QUOTES, 'UTF-8') . '</strong></p>'
                . '<p>This OTP expires in 10 minutes.</p>'
                . '<p>If you did not request this change, please ignore this email.</p>'
                . '<p>— IMS Admin</p>';
            $textBody = "Hello {$name},\n\n"
                . "Use the OTP below to verify your new email address:\n"
                . "OTP: {$otp}\n\n"
                . "This OTP expires in 10 minutes.\n\n"
                . "If you did not request this change, please ignore this email.\n\n"
                . "— IMS Admin";
            $this->mailService->sendMail($newEmail, $subject, $htmlBody, $textBody);
        } catch (\Throwable $e) {
            $this->emailChangeVerifications->clearActiveForUser($userId);
            $this->json(['success' => false, 'message' => 'Unable to send OTP. Please try again later.'], 500);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'OTP sent to your new email. Enter it to verify and submit your request.',
        ]);
    }

    public function verifyEmailChangeOtp(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->userModel->find($userId);
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'MANAGER') {
            $this->json(['success' => false, 'message' => 'Forbidden'], 403);
            return;
        }

        if ($this->emailChangeRequests->hasPendingForUser($userId)) {
            $this->json(['success' => false, 'message' => 'You already have a pending email change request.'], 409);
            return;
        }

        $newEmail = trim((string) $this->input('new_email', ''));
        $otp = trim((string) $this->input('email_otp', ''));

        if ($newEmail === '' || $otp === '') {
            $this->json(['success' => false, 'message' => 'New email and OTP are required.'], 422);
            return;
        }

        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Please enter a valid email'], 422);
            return;
        }

        if (!preg_match('/^\d{6}$/', $otp)) {
            $this->json(['success' => false, 'message' => 'OTP must be a 6-digit code.'], 422);
            return;
        }

        $currentEmail = (string) ($user['email'] ?? '');
        if ($currentEmail !== '' && strcasecmp($currentEmail, $newEmail) === 0) {
            $this->json(['success' => false, 'message' => 'New email must be different from the current email.'], 422);
            return;
        }

        $existing = $this->userModel->firstWhere('email', $newEmail);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'That email address is already in use.'], 409);
            return;
        }

        $verification = $this->emailChangeVerifications->getActiveForUser($userId);
        if (!$verification) {
            $this->json(['success' => false, 'message' => 'No active OTP found. Please request a new OTP.'], 404);
            return;
        }

        $verificationEmail = (string) ($verification['new_email'] ?? '');
        if ($verificationEmail === '' || strcasecmp($verificationEmail, $newEmail) !== 0) {
            $this->json(['success' => false, 'message' => 'OTP does not match the requested email. Please request a new OTP.'], 409);
            return;
        }

        $expiresAtRaw = (string) ($verification['expires_at'] ?? '');
        $expiresAt = null;
        if ($expiresAtRaw !== '') {
            $expiresAt = (new DateTimeImmutable($expiresAtRaw, new DateTimeZone('UTC')))->getTimestamp();
        }

        if ($expiresAt !== null && $expiresAt < time()) {
            $this->emailChangeVerifications->clearActiveForUser($userId);
            $this->json(['success' => false, 'message' => 'OTP has expired. Please request a new OTP.'], 410);
            return;
        }

        $otpHash = hash('sha256', $otp);
        if (!hash_equals((string) ($verification['otp_hash'] ?? ''), $otpHash)) {
            $this->json(['success' => false, 'message' => 'Invalid OTP. Please try again.'], 422);
            return;
        }

        $this->emailChangeVerifications->updateById((int) $verification['id'], [
            'verified_at' => gmdate('Y-m-d H:i:s'),
        ]);

        $this->emailChangeRequests->create([
            'user_id' => $userId,
            'new_email' => $newEmail,
            'status' => 'PENDING',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->json([
            'success' => true,
            'message' => 'Email verified. Your request is pending principal approval.',
        ]);
    }

    // ============================================================================
    // EMAIL CHANGE REQUESTS
    // ============================================================================

    public function showEmailChangeRequests(): void
    {
        $pendingRequests = [];
        $requests = $this->emailChangeRequests->where('status', 'PENDING');
        foreach ($requests as $request) {
            $user = $this->userModel->find((int) ($request['user_id'] ?? 0));
            if ($user && strtoupper((string) ($user['role'] ?? '')) === 'STUDENT') {
                $pendingRequests[] = $request;
            }
        }

        $this->view('manager.email-requests', [
            'title' => 'Email Change Requests',
            'pageSubtitle' => 'Approve email change requests',
            'pending_requests' => $pendingRequests,
        ]);
    }

    public function approveEmailChange(int $id): void
    {
        $request = $this->emailChangeRequests->find($id);

        if (!$request) {
            $this->json(['success' => false, 'message' => 'Request not found.'], 404);
            return;
        }

        if ($request['status'] !== 'PENDING') {
            $this->json(['success' => false, 'message' => 'This request has already been processed.'], 409);
            return;
        }

        $userId = (int) ($request['user_id'] ?? 0);
        $user = $this->userModel->find($userId);
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'STUDENT') {
            $this->json(['success' => false, 'message' => 'Invalid email change request.'], 403);
            return;
        }

        $newEmail = (string) ($request['new_email'] ?? '');
        if ($newEmail === '') {
            $this->json(['success' => false, 'message' => 'New email is missing.'], 422);
            return;
        }

        $existing = $this->userModel->firstWhere('email', $newEmail);
        if ($existing && (int) $existing['id'] !== $userId) {
            $this->json(['success' => false, 'message' => 'Email already in use.'], 409);
            return;
        }

        try {
            $this->userModel->updateById($userId, ['email' => $newEmail]);

            $this->emailChangeRequests->updateById($id, [
                'status' => 'APPROVED',
                'resolved_by' => (int) ($_SESSION['user_id'] ?? 0),
                'resolved_at' => date('Y-m-d H:i:s'),
            ]);

            $name = (string) ($user['full_name'] ?? 'User');
            $subject = 'Email Change Approved - IMS';
            $htmlBody = '<p>Hello ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>'
                . '<p>Your email change request has been approved by the Manager.</p>'
                . '<p>Your new email address is: <strong>' . htmlspecialchars($newEmail, ENT_QUOTES, 'UTF-8') . '</strong></p>'
                . '<p>If you did not request this change, please contact support immediately.</p>'
                . '<p>— IMS Admin</p>';
            $textBody = "Hello {$name},\n\n"
                . "Your email change request has been approved by the Manager.\n"
                . "Your new email address is: {$newEmail}\n\n"
                . "If you did not request this change, please contact support immediately.\n\n"
                . "— IMS Admin";
            $this->mailService->sendMail($newEmail, $subject, $htmlBody, $textBody);

            $this->json([
                'success' => true,
                'message' => 'Email change approved and updated.',
                'data' => ['user_email' => $newEmail],
            ]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => 'Error approving email change.'], 500);
        }
    }

    public function rejectEmailChange(int $id): void
    {
        $request = $this->emailChangeRequests->find($id);

        if (!$request) {
            $this->json(['success' => false, 'message' => 'Request not found.'], 404);
            return;
        }

        if ($request['status'] !== 'PENDING') {
            $this->json(['success' => false, 'message' => 'This request has already been processed.'], 409);
            return;
        }

        $userId = (int) ($request['user_id'] ?? 0);
        $user = $this->userModel->find($userId);
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'STUDENT') {
            $this->json(['success' => false, 'message' => 'Invalid email change request.'], 403);
            return;
        }

        try {
            $this->emailChangeRequests->updateById($id, [
                'status' => 'EXPIRED',
                'resolved_by' => (int) ($_SESSION['user_id'] ?? 0),
                'resolved_at' => date('Y-m-d H:i:s'),
            ]);

            $newEmail = (string) ($request['new_email'] ?? '');
            $recipient = $newEmail !== '' ? $newEmail : (string) ($user['email'] ?? '');
            if ($recipient !== '') {
                $name = (string) ($user['full_name'] ?? 'User');
                $subject = 'Email Change Rejected - IMS';
                $htmlBody = '<p>Hello ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>'
                    . '<p>Your email change request has been rejected by the Manager.</p>'
                    . '<p>If you believe this is a mistake, please contact support.</p>'
                    . '<p>— IMS Admin</p>';
                $textBody = "Hello {$name},\n\n"
                    . "Your email change request has been rejected by the Manager.\n"
                    . "If you believe this is a mistake, please contact support.\n\n"
                    . "— IMS Admin";
                $this->mailService->sendMail($recipient, $subject, $htmlBody, $textBody);
            }

            $this->json(['success' => true, 'message' => 'Email change request rejected.']);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => 'Error rejecting email change.'], 500);
        }
    }

    public function apiGetEmailChangeRequests(): void
    {
        $pendingRequests = $this->emailChangeRequests->where('status', 'PENDING');
        $requests = [];

        foreach ($pendingRequests as $request) {
            $user = $this->userModel->find((int) ($request['user_id'] ?? 0));
            if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'STUDENT') {
                continue;
            }

            $requests[] = [
                'id' => $request['id'],
                'user_id' => $request['user_id'],
                'user_name' => $user['full_name'] ?? 'N/A',
                'user_role' => $user['role'] ?? 'N/A',
                'current_email' => $user['email'] ?? 'N/A',
                'requested_email' => $request['new_email'] ?? 'N/A',
                'created_at' => $request['created_at'] ?? null,
            ];
        }

        $this->json([
            'success' => true,
            'data' => $requests,
        ]);
    }

    // ============================================================================
    // STUDENT MANAGEMENT
    // ============================================================================

    public function showStudents(): void
    {
        // Get all programs for filter
        $programs = $this->programModel->all();
        $semesters = $this->semesterModel->all();

        $this->view('manager.students', [
            'title' => 'Student Management',
            'pageSubtitle' => 'View and manage all students',
            'programs' => $programs,
            'semesters' => $semesters,
        ]);
    }

    public function showStudentDetail(int $id): void
    {
        $student = $this->userModel->find($id);

        if (!$student || strtoupper((string) ($student['role'] ?? '')) !== 'STUDENT') {
            http_response_code(404);
            echo 'Student not found';
            return;
        }

        $studentProfile = $this->studentModel->findByUserId($id);
        $program = null;
        $currentSemester = null;

        if ($studentProfile && isset($studentProfile['program_id'])) {
            $program = $this->programModel->find((int) $studentProfile['program_id']);

            if ($program) {
                $semesters = $this->semesterModel->where('program_id', (int) $program['id']);
                foreach ($semesters as $semester) {
                    if ((int) ($semester['is_current'] ?? 0) === 1) {
                        $currentSemester = $semester;
                        break;
                    }
                }
            }
        }

        $attendanceRecords = $this->attendanceModel->where('student_id', $id);
        $attendanceTotal = count($attendanceRecords);
        $attendancePresent = 0;
        foreach ($attendanceRecords as $record) {
            if (strtoupper((string) ($record['status'] ?? '')) === 'PRESENT') {
                $attendancePresent++;
            }
        }
        $attendanceRate = $attendanceTotal > 0
            ? (int) round(($attendancePresent / $attendanceTotal) * 100)
            : 0;

        $createdBy = null;
        $createdById = (int) ($student['created_by'] ?? 0);
        if ($createdById > 0) {
            $createdBy = $this->userModel->find($createdById);
        }

        $feeStatus = 'N/A';
        $feePaid = 0.0;
        if ($currentSemester) {
            $fee = $this->feeModel->firstWhere('student_id', $id);
            $fee = $fee && ((int) ($fee['semester_id'] ?? 0) === (int) ($currentSemester['id'] ?? 0)) ? $fee : null;

            if ($fee) {
                $feePaid = (float) ($fee['amount_paid'] ?? 0);
                $feeAmount = (float) ($currentSemester['fee_amount'] ?? 0);
                if ($feeAmount > 0) {
                    if ($feePaid >= $feeAmount) {
                        $feeStatus = 'Paid';
                    } elseif ($feePaid > 0) {
                        $feeStatus = 'Partial';
                    } else {
                        $feeStatus = 'Pending';
                    }
                } else {
                    $feeStatus = 'Not Configured';
                }
            } elseif (!empty($currentSemester['fee_amount'])) {
                $feeStatus = 'Pending';
            }
        }

        $this->view('manager.student-detail', [
            'title' => 'Student Details',
            'student' => $student,
            'student_profile' => $studentProfile ?? [],
            'program' => $program,
            'current_semester' => $currentSemester,
            'attendance_summary' => [
                'total' => $attendanceTotal,
                'present' => $attendancePresent,
                'rate' => $attendanceRate,
            ],
            'created_by' => $createdBy,
            'fee_status' => $feeStatus,
            'fee_paid' => $feePaid,
        ]);
    }

    // ============================================================================
    // SEMESTER PROMOTIONS
    // ============================================================================

    public function showPromotions(): void
    {
        $activeSemesters = $this->getActiveSemesters();

        $this->view('manager.promotions', [
            'title' => 'Semester Promotions',
            'pageSubtitle' => 'Review approved and pending students by semester',
            'active_semesters' => $activeSemesters,
        ]);
    }

    public function apiGetPromotionLists(): void
    {
        $semesterId = (int) $this->input('semester_id', 0);
        if ($semesterId <= 0) {
            $this->json(['success' => false, 'message' => 'Semester is required.'], 422);
            return;
        }

        $semester = $this->semesterModel->find($semesterId);
        if (!$semester) {
            $this->json(['success' => false, 'message' => 'Semester not found.'], 404);
            return;
        }

        $students = $this->getSemesterStudents($semesterId);
        $approved = [];
        $pending = [];

        foreach ($students as $student) {
            if (($student['fee_status'] ?? '') === 'Paid') {
                $approved[] = $student;
            } else {
                $pending[] = $student;
            }
        }

        $this->json([
            'success' => true,
            'data' => [
                'semester' => [
                    'id' => (int) ($semester['id'] ?? 0),
                    'program_id' => (int) ($semester['program_id'] ?? 0),
                    'semester_number' => (int) ($semester['semester_number'] ?? 0),
                    'academic_year' => (string) ($semester['academic_year'] ?? ''),
                    'start_date' => $semester['start_date'] ?? null,
                    'end_date' => $semester['end_date'] ?? null,
                ],
                'approved' => $approved,
                'pending' => $pending,
            ],
        ]);
    }

    public function apiPromoteStudents(): void
    {
        $semesterId = (int) $this->input('semester_id', 0);
        $studentIds = $this->input('student_ids', []);

        if ($semesterId <= 0 || !is_array($studentIds) || $studentIds === []) {
            $this->json(['success' => false, 'message' => 'Semester and students are required.'], 422);
            return;
        }

        $semester = $this->semesterModel->find($semesterId);
        if (!$semester) {
            $this->json(['success' => false, 'message' => 'Semester not found.'], 404);
            return;
        }

        $nextSemester = $this->findNextSemester($semester);
        if (!$nextSemester) {
            $this->json(['success' => false, 'message' => 'Next semester is not configured for this program.'], 409);
            return;
        }

        $students = $this->getSemesterStudents($semesterId);
        $studentMap = [];
        foreach ($students as $student) {
            $studentMap[(int) $student['student_id']] = $student;
        }

        $uniqueIds = array_values(array_unique(array_map('intval', $studentIds)));
        $promoted = [];
        $skipped = [];
        $emailFailures = [];

        $db = Database::connection();
        $db->beginTransaction();

        try {
            $insertStmt = $db->prepare('INSERT IGNORE INTO student_fees (student_id, semester_id, amount_paid) VALUES (?, ?, "0.00")');

            foreach ($uniqueIds as $studentId) {
                if ($studentId <= 0 || !isset($studentMap[$studentId])) {
                    $skipped[] = ['student_id' => $studentId, 'reason' => 'Student not found in semester'];
                    continue;
                }

                $student = $studentMap[$studentId];
                if (($student['fee_status'] ?? '') !== 'Paid') {
                    $skipped[] = ['student_id' => $studentId, 'reason' => 'Fee status not paid'];
                    continue;
                }

                $insertStmt->execute([$studentId, (int) $nextSemester['id']]);

                $this->promotionLogModel->create([
                    'semester_id' => $semesterId,
                    'student_id' => $studentId,
                    'status' => 'PROMOTED',
                    'performed_by' => (int) ($_SESSION['user_id'] ?? 0),
                    'notes' => 'Promoted to semester ' . ($nextSemester['semester_number'] ?? ''),
                ]);

                $promoted[] = $studentId;

                try {
                    $this->sendPromotionEmail($student, $semester, $nextSemester);
                } catch (\Throwable $e) {
                    $emailFailures[] = ['student_id' => $studentId, 'error' => $e->getMessage()];
                }
            }

            $db->commit();
        } catch (\Throwable $e) {
            $db->rollBack();
            $this->json(['success' => false, 'message' => 'Failed to promote students.'], 500);
            return;
        }

        $this->json([
            'success' => true,
            'message' => 'Promotion completed.',
            'data' => [
                'promoted' => $promoted,
                'skipped' => $skipped,
                'email_failures' => $emailFailures,
            ],
        ]);
    }

    public function apiRemindStudents(): void
    {
        $semesterId = (int) $this->input('semester_id', 0);
        $studentIds = $this->input('student_ids', []);

        if ($semesterId <= 0 || !is_array($studentIds) || $studentIds === []) {
            $this->json(['success' => false, 'message' => 'Semester and students are required.'], 422);
            return;
        }

        $semester = $this->semesterModel->find($semesterId);
        if (!$semester) {
            $this->json(['success' => false, 'message' => 'Semester not found.'], 404);
            return;
        }

        $students = $this->getSemesterStudents($semesterId);
        $studentMap = [];
        foreach ($students as $student) {
            $studentMap[(int) $student['student_id']] = $student;
        }

        $uniqueIds = array_values(array_unique(array_map('intval', $studentIds)));
        $reminded = [];
        $skipped = [];
        $emailFailures = [];

        foreach ($uniqueIds as $studentId) {
            if ($studentId <= 0 || !isset($studentMap[$studentId])) {
                $skipped[] = ['student_id' => $studentId, 'reason' => 'Student not found in semester'];
                continue;
            }

            $student = $studentMap[$studentId];
            if (($student['fee_status'] ?? '') === 'Paid') {
                $skipped[] = ['student_id' => $studentId, 'reason' => 'Already paid'];
                continue;
            }

            $this->promotionLogModel->create([
                'semester_id' => $semesterId,
                'student_id' => $studentId,
                'status' => 'REMINDED',
                'performed_by' => (int) ($_SESSION['user_id'] ?? 0),
                'notes' => 'Fee reminder sent',
            ]);

            $reminded[] = $studentId;

            try {
                $this->sendReminderEmail($student, $semester);
            } catch (\Throwable $e) {
                $emailFailures[] = ['student_id' => $studentId, 'error' => $e->getMessage()];
            }
        }

        $this->json([
            'success' => true,
            'message' => 'Reminder emails sent.',
            'data' => [
                'reminded' => $reminded,
                'skipped' => $skipped,
                'email_failures' => $emailFailures,
            ],
        ]);
    }

    private function getActiveSemesters(): array
    {
        $today = (new DateTimeImmutable('now', new DateTimeZone('Asia/Manila')))->format('Y-m-d');
        $db = Database::connection();

        $sql = 'SELECT s.id, s.program_id, s.semester_number, s.academic_year, s.start_date, s.end_date, s.is_current,
                       p.program_name
                FROM semesters s
                JOIN programs p ON p.id = s.program_id
                WHERE (s.start_date IS NOT NULL AND s.end_date IS NOT NULL AND s.start_date <= ? AND s.end_date >= ?)
                   OR ((s.start_date IS NULL OR s.end_date IS NULL) AND s.is_current = 1)
                ORDER BY s.id ASC';

        $stmt = $db->prepare($sql);
        $stmt->execute([$today, $today]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as &$row) {
            $row['label'] = (string) ($row['program_name'] ?? 'Program')
                . ' · Sem ' . (string) ($row['semester_number'] ?? 'N/A')
                . ' · ' . (string) ($row['academic_year'] ?? '');
        }
        unset($row);

        return $rows;
    }

    /** @return array<int, array<string, mixed>> */
    private function getSemesterStudents(int $semesterId): array
    {
        $db = Database::connection();
        $sql = 'SELECT u.id AS student_id, u.full_name, u.email, u.login_id,
                       sp.registration_number,
                       sf.amount_paid,
                       sem.fee_amount, sem.semester_number, sem.academic_year, sem.program_id,
                       p.program_name
                FROM student_fees sf
                JOIN users u ON u.id = sf.student_id
                JOIN semesters sem ON sem.id = sf.semester_id
                LEFT JOIN student_profiles sp ON sp.user_id = u.id
                JOIN programs p ON p.id = sem.program_id
                WHERE sf.semester_id = :semester_id AND u.role = "STUDENT"
                ORDER BY COALESCE(sp.registration_number, u.login_id) ASC, u.id ASC';

        $stmt = $db->prepare($sql);
        $stmt->execute(['semester_id' => $semesterId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $result = [];
        foreach ($rows as $row) {
            $feeAmount = $row['fee_amount'] !== null ? (string) $row['fee_amount'] : null;
            $amountPaid = (string) ($row['amount_paid'] ?? '0.00');

            $formatted = \App\Helpers\format_student_fee_response([
                'amount_paid' => $amountPaid,
                'fee_amount' => $feeAmount,
            ], $feeAmount);

            $pendingAmount = $formatted['pending_amount'];
            if ($pendingAmount !== null && bccomp($pendingAmount, '0.00', 2) < 0) {
                $pendingAmount = '0.00';
            }

            $result[] = [
                'student_id' => (int) ($row['student_id'] ?? 0),
                'full_name' => (string) ($row['full_name'] ?? 'N/A'),
                'email' => (string) ($row['email'] ?? ''),
                'login_id' => (string) ($row['login_id'] ?? 'N/A'),
                'registration_number' => (string) ($row['registration_number'] ?? $row['login_id'] ?? 'N/A'),
                'program_name' => (string) ($row['program_name'] ?? 'N/A'),
                'semester_number' => (int) ($row['semester_number'] ?? 0),
                'academic_year' => (string) ($row['academic_year'] ?? ''),
                'fee_amount' => $feeAmount,
                'amount_paid' => $amountPaid,
                'pending_amount' => $pendingAmount,
                'fee_status' => (string) ($formatted['fee_status'] ?? 'Pending'),
            ];
        }

        return $result;
    }

    private function findNextSemester(array $semester): ?array
    {
        $programId = (int) ($semester['program_id'] ?? 0);
        $nextNumber = (int) ($semester['semester_number'] ?? 0) + 1;
        if ($programId === 0 || $nextNumber <= 0) {
            return null;
        }

        $db = Database::connection();
        $sql = 'SELECT * FROM semesters
                WHERE program_id = :program_id AND semester_number = :semester_number
                ORDER BY (start_date IS NULL) ASC, start_date ASC, academic_year ASC
                LIMIT 1';

        $stmt = $db->prepare($sql);
        $stmt->execute(['program_id' => $programId, 'semester_number' => $nextNumber]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return is_array($row) ? $row : null;
    }

    private function sendPromotionEmail(array $student, array $semester, array $nextSemester): void
    {
        $email = (string) ($student['email'] ?? '');
        if ($email === '') {
            return;
        }

        $studentName = (string) ($student['full_name'] ?? 'Student');
        $nextLabel = 'Semester ' . (string) ($nextSemester['semester_number'] ?? '');
        $academicYear = (string) ($nextSemester['academic_year'] ?? '');

        $subject = 'Promotion Approved - ' . $nextLabel;
        $htmlBody = sprintf(
            '<p>Hello %s,</p><p>Congratulations! You have been promoted to <strong>%s</strong> for %s.</p><p>Please check your dashboard for next steps.</p>',
            e($studentName),
            e($nextLabel),
            e($academicYear)
        );
        $textBody = "Hello {$studentName},\n\nCongratulations! You have been promoted to {$nextLabel} for {$academicYear}.\n\nPlease check your dashboard for next steps.";

        $this->mailService->sendMail($email, $subject, $htmlBody, $textBody);
    }

    private function sendReminderEmail(array $student, array $semester): void
    {
        $email = (string) ($student['email'] ?? '');
        if ($email === '') {
            return;
        }

        $studentName = (string) ($student['full_name'] ?? 'Student');
        $semesterLabel = 'Semester ' . (string) ($semester['semester_number'] ?? '') . ' (' . (string) ($semester['academic_year'] ?? '') . ')';
        $pending = (string) ($student['pending_amount'] ?? '0.00');

        $subject = 'Fee Reminder - ' . $semesterLabel;
        $htmlBody = sprintf(
            '<p>Hello %s,</p><p>This is a reminder that your fees for <strong>%s</strong> are pending.</p><p>Pending amount: <strong>%s</strong>.</p><p>Please settle the dues to proceed with promotion.</p>',
            e($studentName),
            e($semesterLabel),
            e($pending)
        );
        $textBody = "Hello {$studentName},\n\nThis is a reminder that your fees for {$semesterLabel} are pending.\nPending amount: {$pending}.\n\nPlease settle the dues to proceed with promotion.";

        $this->mailService->sendMail($email, $subject, $htmlBody, $textBody);
    }

    // ============================================================================
    // STUDENT CREATION (Manual Add)
    // ============================================================================

    public function createStudent(): void
    {
        $fullName = (string) $this->input('full_name', '');
        $programId = (int) $this->input('program_id', 0);
        $email = (string) $this->input('email', '');
        $phone = (string) $this->input('phone', '');

        $errors = [];

        // Validation
        if ($fullName === '') {
            $errors['full_name'] = 'Please enter your complete name (e.g., John Doe).';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email';
        } elseif ($this->userModel->firstWhere('email', $email)) {
            $errors['email'] = 'Email already exists.';
        }

        if ($programId <= 0) {
            $errors['program_id'] = 'Program is required.';
        }

        $programCode = '';
        if ($programId > 0) {
            $program = $this->programModel->find($programId);
            $programCode = strtoupper((string) ($program['program_code'] ?? ''));
            if ($programCode === '') {
                $errors['program_id'] = 'Selected program is invalid.';
            }
        }

        $entrySemesterId = null;
        if ($programId > 0) {
            $db = Database::connection();
            $entryStmt = $db->prepare('
                SELECT id
                FROM semesters
                WHERE program_id = ? AND semester_number = 1
                ORDER BY (start_date IS NULL) ASC, start_date ASC, academic_year ASC
                LIMIT 1
            ');
            $entryStmt->execute([$programId]);
            $entrySemester = $entryStmt->fetch();
            if ($entrySemester) {
                $entrySemesterId = (int) $entrySemester['id'];
            } else {
                $errors['program_id'] = 'Selected program has no Semester 1 configured.';
            }
        }

        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
            return;
        }

        $registrationNumber = $this->generateRegistrationNumber($programCode);
        if ($registrationNumber === '') {
            $this->json(['success' => false, 'message' => 'Unable to generate registration number.'], 500);
            return;
        }

        $loginId = $registrationNumber;

        // Default placeholders until student updates profile
        $fatherName = 'Not Provided';
        $dateOfBirth = '2000-01-01';

        // Generate temporary password
        $tempPassword = bin2hex(random_bytes(5));
        $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        // Create user account
        try {
            $createdBy = (int) ($_SESSION['user_id'] ?? 0);
            $userId = $this->userModel->create([
                'role'                 => 'STUDENT',
                'login_id'             => $loginId,
                'password_hash'        => $passwordHash,
                'full_name'            => $fullName,
                'email'                => $email,
                'phone'                => $phone,
                'is_active'            => 1,
                'must_change_password' => 1,
                'created_by'           => $createdBy,
            ]);

            // Create student profile
            $studentProfileId = $this->studentModel->create([
                'user_id'             => $userId,
                'father_name'         => $fatherName,
                'registration_number' => $registrationNumber,
                'date_of_birth'       => $dateOfBirth,
                'program_id'          => $programId,
                'enrollment_semester_id' => $entrySemesterId,
            ]);

            try {
                $feeStmt = $db->prepare('
                    INSERT INTO student_fees (student_id, semester_id, amount_paid)
                    VALUES (?, ?, "0.00")
                    ON DUPLICATE KEY UPDATE id = LAST_INSERT_ID(id)
                ');
                $feeStmt->execute([$userId, $entrySemesterId]);
            } catch (\Exception $e) {
                // Log error but don't fail - fees can be created later
                \App\Helpers\logger_helper('fee_creation_error', 'Failed to create entry semester fee for student ' . $userId . ': ' . $e->getMessage());
            }

            $tokenId = $this->sendActivationEmail(
                $userId,
                $email,
                $fullName,
                'STUDENT',
                $loginId,
                $createdBy
            );

            $this->json([
                'success' => true,
                'message' => 'Student created. Activation email sent.',
                'data' => ['id' => $userId, 'registration_number' => $registrationNumber],
            ], 201);
        } catch (\Exception $e) {
            if (isset($tokenId) && (int) $tokenId > 0) {
                $this->activationTokens->deleteById((int) $tokenId);
            }
            if (isset($studentProfileId) && (int) $studentProfileId > 0) {
                $this->studentModel->deleteById((int) $studentProfileId);
            }
            if (isset($userId) && (int) $userId > 0) {
                $this->deleteStudentArtifacts((int) $userId);
                $this->userModel->deleteById((int) $userId);
            }

            $this->json([
                'success' => false,
                'message' => 'Failed to create student: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function generateRegistrationNumber(string $programCode): string
    {
        if ($programCode === '') {
            return '';
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT registration_number FROM student_profiles WHERE registration_number LIKE :prefix ORDER BY registration_number DESC LIMIT 1');
        $stmt->execute(['prefix' => $programCode . '%']);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        $yearPart = date('Y');
        $sequence = 1;
        $padLength = 3;

        if (!empty($row['registration_number']) && preg_match('/^([A-Z]+)(\d{4})(\d+)$/', (string) $row['registration_number'], $matches)) {
            $yearPart = $matches[2];
            $sequence = (int) $matches[3] + 1;
            $padLength = strlen($matches[3]);
        }

        for ($attempts = 0; $attempts < 10; $attempts++) {
            $candidate = $programCode . $yearPart . str_pad((string) $sequence, $padLength, '0', STR_PAD_LEFT);

            if (!$this->studentModel->firstWhere('registration_number', $candidate)
                && !$this->userModel->firstWhere('login_id', $candidate)) {
                return $candidate;
            }

            $sequence++;
        }

        return '';
    }

    // ============================================================================
    // STUDENT ACTIVATION / DEACTIVATION
    // ============================================================================

    public function toggleStudentStatus(int $id): void
    {
        $student = $this->userModel->find($id);
        if (!$student) {
            $this->json(['error' => 'Student not found'], 404);
            return;
        }

        if (strtoupper($student['role']) !== 'STUDENT') {
            $this->json(['error' => 'User is not a student'], 403);
            return;
        }

        $newStatus = $student['is_active'] ? 0 : 1;
        $this->userModel->update($id, ['is_active' => $newStatus]);

        $this->json([
            'success' => true,
            'message' => $newStatus ? 'Student activated.' : 'Student deactivated.',
            'new_status' => $newStatus,
        ]);
    }

    // ============================================================================
    // API ENDPOINTS
    // ============================================================================

    public function apiDashboard(): void
    {
        $stats = $this->getDashboardStats();

        $this->json([
            'success' => true,
            'data' => $stats,
        ]);
    }

    public function apiGetStudents(): void
    {
        $filter = (string) $this->input('filter', '');
        $programFilter = (string) $this->input('program', '');
        $statusFilter = (string) $this->input('status', '');

        // Get all students
        $students = $this->userModel->where('role', 'STUDENT');

        // Apply program filter
        if ($programFilter !== '') {
            $filteredStudents = [];
            foreach ($students as $student) {
                $profile = $this->studentModel->firstWhere('user_id', $student['id']);
                if ($profile && $profile['program_id']) {
                    $program = $this->programModel->find((int) $profile['program_id']);
                    if ($program && $program['program_code'] === $programFilter) {
                        $filteredStudents[] = $student;
                    }
                }
            }
            $students = $filteredStudents;
        }

        // Apply status filter
        if ($statusFilter !== '') {
            $isActive = strtolower($statusFilter) === 'active' ? 1 : 0;
            $students = array_filter($students, static fn($s) => (int) $s['is_active'] === $isActive);
        }

        // Apply search filter
        if ($filter !== '') {
            $search = strtolower($filter);
            $students = array_filter($students, static function ($s) use ($search) {
                return strpos(strtolower($s['full_name'] ?? ''), $search) !== false
                    || strpos(strtolower($s['login_id'] ?? ''), $search) !== false;
            });
        }

        // Enrich with program and registration number
        $enriched = [];
        foreach ($students as $student) {
            $profile = $this->studentModel->firstWhere('user_id', $student['id']);
            $program = null;
            $regNum = 'N/A';
            $semesterNumber = null;
            
            if ($profile) {
                $regNum = $profile['registration_number'] ?? 'N/A';
                if ($profile['program_id']) {
                    $program = $this->programModel->find((int) $profile['program_id']);
                }
                if (!empty($profile['enrollment_semester_id'])) {
                    $semester = $this->semesterModel->find((int) $profile['enrollment_semester_id']);
                    if ($semester) {
                        $semesterNumber = $semester['semester_number'] ?? null;
                    }
                }
            }

            $enriched[] = [
                'id' => $student['id'],
                'name' => $student['full_name'],
                'registration_number' => $regNum,
                'program' => $program['program_name'] ?? 'N/A',
                'program_code' => $program['program_code'] ?? '',
                'semester_number' => $semesterNumber,
                'status' => $student['is_active'] ? 'active' : 'inactive',
                'email' => $student['email'],
                'phone' => $student['phone'],
                'login_id' => $student['login_id'],
            ];
        }

        $this->json(['success' => true, 'data' => $enriched]);
    }

    public function apiGetStudentResetRequests(): void
    {
        $allResets = $this->resetModel->where('status', 'PENDING');
        
        // Filter for student resets only
        $studentResets = [];
        foreach ($allResets as $reset) {
            $user = $this->userModel->find((int) $reset['requested_by']);
            if ($user && strtoupper($user['role']) === 'STUDENT') {
                $reset['user_name'] = $user['full_name'];
                $reset['user_role'] = $user['role'];
                $reset['login_id'] = $user['login_id'];
                $studentResets[] = $reset;
            }
        }

        $this->json(['success' => true, 'data' => $studentResets]);
    }

    // ============================================================================
    // PASSWORD RESET APPROVAL
    // ============================================================================

    public function showPasswordResets(): void
    {
        $this->view('manager.password-resets', [
            'title' => 'Student Password Reset Approvals',
            'pageSubtitle' => 'Review and approve student password reset requests',
        ]);
    }

    public function approvePasswordReset(int $id): void
    {
        $reset = $this->resetModel->find($id);
        if (!$reset) {
            $this->json(['error' => 'Password reset request not found'], 404);
            return;
        }

        // Verify this is a student reset request
        $user = $this->userModel->find((int) $reset['requested_by']);
        if (!$user || strtoupper($user['role']) !== 'STUDENT') {
            $this->json(['error' => 'Only student password resets can be approved by Manager'], 403);
            return;
        }

        // Generate new temporary password
        $tempPassword = bin2hex(random_bytes(5));
        $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        // Update user password and mark as must change
        $this->userModel->updateById((int) $reset['requested_by'], [
            'password_hash'        => $passwordHash,
            'must_change_password' => 1,
        ]);

        // Update reset request status — spec columns: resolved_by, resolved_at
        $this->resetModel->updateById($id, [
            'status'      => 'APPROVED',
            'resolved_by' => (int) ($_SESSION['user_id'] ?? 0),
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);


        $this->json([
            'success' => true,
            'message' => 'Password reset approved.',
            'temporary_password' => $tempPassword,
        ]);
    }

    public function rejectPasswordReset(int $id): void
    {
        $reset = $this->resetModel->find($id);
        if (!$reset) {
            $this->json(['error' => 'Password reset request not found'], 404);
            return;
        }

        // Verify this is a student reset request
        $user = $this->userModel->find((int) $reset['requested_by']);
        if (!$user || strtoupper($user['role']) !== 'STUDENT') {
            $this->json(['error' => 'Only student password resets can be rejected by Manager'], 403);
            return;
        }

        // Spec: no REJECTED status in ENUM. Use EXPIRED for dismissed requests.
        $this->resetModel->updateById($id, [
            'status'      => 'EXPIRED',
            'resolved_by' => (int) ($_SESSION['user_id'] ?? 0),
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);

        $this->json([
            'success' => true,
            'message' => 'Password reset request dismissed.',
        ]);
    }

    // ============================================================================
    // CSV UPLOAD
    // ============================================================================

    public function showCsvUpload(): void
    {
        $programs = $this->programModel->all();

        $this->view('manager.csv-upload', [
            'title' => 'Import Students via CSV',
            'pageSubtitle' => '4-step import process: Upload → Validate → Confirm → Complete',
            'programs' => $programs,
        ]);
    }

    public function processCsvUpload(): void
    {
        // Get uploaded file
        $file = $_FILES['csv_file'] ?? null;
        if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
            $this->json(['success' => false, 'message' => 'No file uploaded or upload error'], 400);
            return;
        }

        // Read CSV
        $csvData = [];
        $handle = fopen($file['tmp_name'], 'r');
        if (!$handle) {
            $this->json(['success' => false, 'message' => 'Cannot read CSV file'], 400);
            return;
        }

        $validRows = [];
        $invalidRows = [];
        $rowNumber = 0;

        while (($row = fgetcsv($handle)) !== false) {
            $rowNumber++;
            if ($rowNumber === 1) {
                continue; // Skip header row
            }

            // Expected columns: full_name, login_id, registration_number, date_of_birth, program_code, email, phone
            if (count($row) < 7) {
                $invalidRows[] = [
                    'row' => $rowNumber,
                    'data' => $row,
                    'errors' => ['Not enough columns (expected 7)'],
                ];
                continue;
            }

            $programCode = trim($row[4] ?? '');
            $programId = $this->resolveProgramId($programCode);
            $rowData = [
                'full_name' => trim($row[0] ?? ''),
                'login_id' => trim($row[1] ?? ''),
                'registration_number' => trim($row[2] ?? ''),
                'date_of_birth' => trim($row[3] ?? ''),
                'program_code' => $programCode,
                'program_id' => $programId,
                'email' => trim($row[5] ?? ''),
                'phone' => trim($row[6] ?? ''),
            ];

            if ($rowData['registration_number'] !== '') {
                $rowData['login_id'] = $rowData['registration_number'];
            }

            $errors = $this->validateStudentRow($rowData);
            if (!empty($errors)) {
                $invalidRows[] = [
                    'row' => $rowNumber,
                    'data' => $rowData,
                    'errors' => $errors,
                ];
            } else {
                $validRows[] = [
                    'row' => $rowNumber,
                    'data' => $rowData,
                ];
            }
        }
        fclose($handle);

        $this->json([
            'success' => true,
            'valid_count' => count($validRows),
            'invalid_count' => count($invalidRows),
            'valid_rows' => $validRows,
            'invalid_rows' => $invalidRows,
        ]);
    }

    private function resolveProgramId(string $programCode): ?int
    {
        $programCode = trim($programCode);
        if ($programCode === '') {
            return null;
        }

        $program = $this->programModel->firstWhere('program_code', $programCode);
        if (!$program) {
            return null;
        }

        return (int) ($program['id'] ?? 0) ?: null;
    }

    private function validateStudentRow(array $row): array
    {
        $errors = [];

        if (empty($row['full_name'])) {
            $errors[] = 'Full name is required';
        }
        if (empty($row['registration_number'])) {
            $errors[] = 'Registration number is required';
        } elseif ($this->studentModel->firstWhere('registration_number', $row['registration_number'])) {
            $errors[] = 'Registration number already exists';
        }
        if (!empty($row['registration_number'])) {
            $row['login_id'] = $row['registration_number'];
            if ($this->userModel->firstWhere('login_id', $row['login_id'])) {
                $errors[] = 'Registration number already exists';
            }
        }
        if (empty($row['email'])) {
            $errors[] = 'Email is required';
        } elseif (!filter_var($row['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Invalid email format';
        } elseif ($this->userModel->firstWhere('email', $row['email'])) {
            $errors[] = 'Email already exists';
        }
        if (empty($row['date_of_birth'])) {
            $errors[] = 'Date of birth is required';
        }
        if (empty($row['program_code'])) {
            $errors[] = 'Program Code is required';
        } elseif (empty($row['program_id'])) {
            $errors[] = 'Invalid Program Code';
        }

        return $errors;
    }

    public function confirmCsvImport(): void
    {
        $validRows = (array) $this->input('valid_rows', []);

        if (empty($validRows)) {
            $this->json(['success' => false, 'message' => 'No valid rows to import'], 400);
            return;
        }

        $imported = 0;
        $rejected = 0;

        foreach ($validRows as $row) {
            $rowNumber = (int) ($row['row'] ?? 0);
            $data = $row['data'] ?? [];
            if (empty($data)) {
                $rejected++;
                continue;
            }

            if (empty($data['program_id'])) {
                $rejected++;
                continue;
            }

            try {
                $createdBy = (int) ($_SESSION['user_id'] ?? 0);
                // Generate temporary password
                $tempPassword = bin2hex(random_bytes(5));
                $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT, ['cost' => 12]);

                if (!empty($data['registration_number'])) {
                    $data['login_id'] = $data['registration_number'];
                }

                // Create user via create() — BaseModel uses create() not insert()
                $userId = $this->userModel->create([
                    'role'                 => 'STUDENT',
                    'login_id'             => $data['login_id'],
                    'password_hash'        => $passwordHash,
                    'full_name'            => $data['full_name'],
                    'email'                => $data['email'],
                    'phone'                => $data['phone'] ?? '',
                    'is_active'            => 1,
                    'must_change_password' => 1,
                    'created_by'           => $createdBy,
                ]);

                // Create student profile
                $studentProfileId = $this->studentModel->create([
                    'user_id'             => $userId,
                    'registration_number' => $data['registration_number'],
                    'date_of_birth'       => $data['date_of_birth'],
                    'program_id'          => (int) $data['program_id'],
                ]);

                // Spec: auto-create student_fee rows for current semesters in this program
                // Using StudentFeeService per spec Part 3 Table 9 FLOW section 1
                $this->studentFeeService->createFeesForNewStudent($userId, (int) $data['program_id']);

                $tokenId = $this->sendActivationEmail(
                    $userId,
                    (string) $data['email'],
                    (string) $data['full_name'],
                    'STUDENT',
                    (string) $data['login_id'],
                    $createdBy
                );

                $imported++;
            } catch (\Exception $e) {
                if (isset($tokenId) && (int) $tokenId > 0) {
                    $this->activationTokens->deleteById((int) $tokenId);
                }
                if (isset($studentProfileId) && (int) $studentProfileId > 0) {
                    $this->studentModel->deleteById((int) $studentProfileId);
                }
                if (isset($userId) && (int) $userId > 0) {
                    $this->deleteStudentArtifacts((int) $userId);
                    $this->userModel->deleteById((int) $userId);
                }

                \App\Helpers\logger_helper(
                    'csv_import_error',
                    'CSV import error for ' . ($data['login_id'] ?? 'unknown') . ' (row ' . $rowNumber . '): ' . $e->getMessage()
                );
                $rejected++;

                $safeMessage = $rowNumber > 0
                    ? 'Error in row ' . $rowNumber . ': Unable to import student. Please verify the data.'
                    : 'Unable to import student. Please verify the data.';

                $this->json([
                    'success' => false,
                    'message' => $safeMessage,
                ], 500);
                return;
            }
        }

        $this->json([
            'success' => true,
            'imported' => $imported,
            'rejected' => $rejected,
            'message' => "Import complete: $imported students imported, $rejected failed. Activation emails sent.",
        ]);
    }

    private function sendActivationEmail(
        int $userId,
        string $email,
        string $fullName,
        string $role,
        string $loginId,
        int $createdBy
    ): int {
        $token = bin2hex(random_bytes(32));
        $tokenHash = hash('sha256', $token);
        $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
            ->modify('+24 hours')
            ->format('Y-m-d H:i:s');

        $tokenId = $this->activationTokens->create([
            'user_id' => $userId,
            'token_hash' => $tokenHash,
            'expires_at' => $expiresAt,
            'created_by' => $createdBy,
            'created_at' => gmdate('Y-m-d H:i:s'),
        ]);

        $activationLink = url('activate/' . $token);
        $subject = 'Activate your IMS account';

        $roleLabel = $role === 'VP' ? 'Vice principal' : $role;
        $htmlBody = sprintf(
            '<p>Hello %s,</p><p>Your %s account has been created. Please activate your account using the link below (valid for 24 hours):</p><p><a href="%s">Activate Account</a></p><p>Login ID: <strong>%s</strong></p><p>If you did not expect this email, please contact support.</p>',
            e($fullName),
            e($roleLabel),
            e($activationLink),
            e($loginId)
        );
        $textBody = "Hello {$fullName},\n\nYour {$roleLabel} account has been created. Activate your account using the link below (valid for 24 hours):\n{$activationLink}\n\nLogin ID: {$loginId}\n\nIf you did not expect this email, please contact support.";

        try {
            $this->mailService->sendMail($email, $subject, $htmlBody, $textBody);
        } catch (\Throwable $e) {
            if ((int) $tokenId > 0) {
                $this->activationTokens->deleteById((int) $tokenId);
            }
            throw $e;
        }

        return (int) $tokenId;
    }

    private function deleteStudentArtifacts(int $userId): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('DELETE FROM student_profiles WHERE user_id = :user_id');
        $stmt->execute(['user_id' => $userId]);

        $stmt = $db->prepare('DELETE FROM student_fees WHERE student_id = :student_id');
        $stmt->execute(['student_id' => $userId]);
    }
}
