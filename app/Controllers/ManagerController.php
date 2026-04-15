<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\ActivationTokenModel;
use App\Models\EmailChangeRequestModel;
use App\Models\UserModel;
use App\Models\StudentProfileModel;
use App\Models\PasswordResetRequestModel;
use App\Models\StudentFeeModel;
use App\Models\AttendanceModel;
use App\Models\ProgramModel;
use App\Services\MailService;
use App\Services\StudentFeeService;
use DateTimeImmutable;
use DateTimeZone;

class ManagerController extends BaseController
{
    private UserModel $userModel;
    private ActivationTokenModel $activationTokens;
    private EmailChangeRequestModel $emailChangeRequests;
    private StudentProfileModel $studentModel;
    private PasswordResetRequestModel $resetModel;
    private StudentFeeModel $feeModel;
    private AttendanceModel $attendanceModel;
    private ProgramModel $programModel;
    private StudentFeeService $studentFeeService;
    private MailService $mailService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->activationTokens = new ActivationTokenModel();
        $this->emailChangeRequests = new EmailChangeRequestModel();
        $this->studentModel = new StudentProfileModel();
        $this->resetModel = new PasswordResetRequestModel();
        $this->feeModel = new StudentFeeModel();
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

        $this->view('manager.dashboard', [
            'title' => 'Manager Dashboard',
            'pageSubtitle' => 'Student lifecycle management overview',
            'user_name' => (string) ($_SESSION['user_name'] ?? 'Manager'),
            'stats' => $stats,
            'pending_resets' => $enrichedResets,
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

        $this->view('manager.profile', [
            'title' => 'My Profile',
            'pageSubtitle' => 'Manage your profile details',
            'user' => $user,
            'email_request_pending' => $emailRequestPending,
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

        if ($phone !== '' && !preg_match('/^\d+$/', $phone)) {
            $this->json(['success' => false, 'message' => 'Phone number must contain digits only.'], 422);
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
            $this->json(['success' => false, 'message' => 'Please enter a valid email address.'], 422);
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

        $this->emailChangeRequests->create([
            'user_id' => $userId,
            'new_email' => $newEmail,
            'status' => 'PENDING',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $this->json([
            'success' => true,
            'message' => 'Email change request submitted for principal approval.',
        ]);
    }

    // ============================================================================
    // STUDENT MANAGEMENT
    // ============================================================================

    public function showStudents(): void
    {
        // Get all programs for filter
        $programs = $this->programModel->all();

        $this->view('manager.students', [
            'title' => 'Student Management',
            'pageSubtitle' => 'View and manage all students',
            'programs' => $programs,
        ]);
    }

    public function showStudentDetail(int $id): void
    {
        // Get student user info
        $student = $this->userModel->find($id);
        if (!$student) {
            $this->json(['error' => 'Student not found'], 404);
            return;
        }

        if (strtoupper($student['role']) !== 'STUDENT') {
            $this->json(['error' => 'User is not a student'], 403);
            return;
        }

        // Get student profile
        $profile = $this->studentModel->firstWhere('user_id', $id);

        // Get program info if profile exists
        $program = null;
        if ($profile && isset($profile['program_id'])) {
            $program = $this->programModel->find((int) $profile['program_id']);
        }

        // Get fee summary
        $fees = $this->feeModel->where('student_id', $id);
        $feeSummary = [
            'total_fees' => array_sum(array_map(static fn($f) => (float) ($f['amount'] ?? 0), $fees)),
            'paid_amount' => array_sum(array_map(static fn($f) => (float) ($f['paid_amount'] ?? 0), $fees)),
            'due_amount' => array_sum(array_map(static fn($f) => (float) (($f['amount'] ?? 0) - ($f['paid_amount'] ?? 0)), $fees)),
        ];

        // Get attendance summary
        $attendance = $this->attendanceModel->where('student_id', $id);
        $attendanceSummary = [
            'total_classes' => count($attendance),
            'present' => count(array_filter($attendance, static fn($a) => strtoupper($a['status'] ?? '') === 'PRESENT')),
            'absent' => count(array_filter($attendance, static fn($a) => strtoupper($a['status'] ?? '') === 'ABSENT')),
            'late' => count(array_filter($attendance, static fn($a) => strtoupper($a['status'] ?? '') === 'LATE')),
        ];

        $this->view('manager.student-detail', [
            'title' => $student['full_name'] ?? 'Student Details',
            'pageSubtitle' => 'View student profile and academic information',
            'student' => $student,
            'profile' => $profile,
            'program' => $program,
            'fee_summary' => $feeSummary,
            'attendance_summary' => $attendanceSummary,
        ]);
    }

    // ============================================================================
    // STUDENT CREATION (Manual Add)
    // ============================================================================

    public function createStudent(): void
    {
        $fullName = (string) $this->input('full_name', '');
        $loginId = (string) $this->input('login_id', '');
        $registrationNumber = (string) $this->input('registration_number', '');
        $dateOfBirth = (string) $this->input('date_of_birth', '');
        $programId = (int) $this->input('program_id', 0);
        $email = (string) $this->input('email', '');
        $phone = (string) $this->input('phone', '');

        $errors = [];

        // Validation
        if ($fullName === '') {
            $errors['full_name'] = 'Full Name is required.';
        }

        if ($registrationNumber === '') {
            $errors['registration_number'] = 'Registration Number is required.';
        } elseif ($this->studentModel->firstWhere('registration_number', $registrationNumber)) {
            $errors['registration_number'] = 'Registration Number already exists.';
        }

        if ($loginId !== '' && $registrationNumber !== '' && $loginId !== $registrationNumber) {
            $errors['registration_number'] = 'Login ID must be same as Registration Number.';
        }

        $loginId = $registrationNumber;
        if ($loginId !== '' && $this->userModel->firstWhere('login_id', $loginId)) {
            $errors['registration_number'] = 'Registration Number already exists.';
        }

        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Invalid email format.';
        } elseif ($this->userModel->firstWhere('email', $email)) {
            $errors['email'] = 'Email already exists.';
        }

        if ($dateOfBirth === '') {
            $errors['date_of_birth'] = 'Date of birth is required.';
        }

        if ($programId <= 0) {
            $errors['program_id'] = 'Program is required.';
        }

        if (!empty($errors)) {
            $this->json(['success' => false, 'errors' => $errors], 422);
            return;
        }

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
                'registration_number' => $registrationNumber,
                'date_of_birth'       => $dateOfBirth,
                'program_id'          => $programId,
            ]);

            // Spec: auto-create student_fee rows for all current semesters in this program
            // Using StudentFeeService per spec Part 3 Table 9 FLOW section 1
            try {
                $this->studentFeeService->createFeesForNewStudent($userId, $programId);
            } catch (\Exception $e) {
                // Log error but don't fail - fees can be created later
                \App\Helpers\logger_helper('fee_creation_error', 'Failed to create fees for student ' . $userId . ': ' . $e->getMessage());
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
                'data' => ['id' => $userId],
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
            
            if ($profile) {
                $regNum = $profile['registration_number'] ?? 'N/A';
                if ($profile['program_id']) {
                    $program = $this->programModel->find((int) $profile['program_id']);
                }
            }

            $enriched[] = [
                'id' => $student['id'],
                'name' => $student['full_name'],
                'registration_number' => $regNum,
                'program' => $program['program_name'] ?? 'N/A',
                'program_code' => $program['program_code'] ?? '',
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
        $subject = 'Activate your EduHub account';
        $htmlBody = sprintf(
            '<p>Hello %s,</p><p>Your %s account has been created. Please activate your account using the link below (valid for 24 hours):</p><p><a href="%s">Activate Account</a></p><p>Login ID: <strong>%s</strong></p><p>If you did not expect this email, please contact support.</p>',
            e($fullName),
            e($role),
            e($activationLink),
            e($loginId)
        );
        $textBody = "Hello {$fullName},\n\nYour {$role} account has been created. Activate your account using the link below (valid for 24 hours):\n{$activationLink}\n\nLogin ID: {$loginId}\n\nIf you did not expect this email, please contact support.";

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
