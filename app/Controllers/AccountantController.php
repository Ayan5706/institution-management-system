<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\StudentFeeModel;
use App\Models\ProgramModel;
use App\Models\SemesterModel;
use App\Models\StudentProfileModel;
use App\Models\UserModel;
use App\Models\EmailChangeRequestModel;
use App\Models\EmailChangeVerificationModel;
use App\Core\Database;
use App\Services\MailService;
use DateTimeImmutable;
use DateTimeZone;

class AccountantController extends BaseController
{
    private StudentFeeModel $feeModel;
    private ProgramModel $programModel;
    private SemesterModel $semesterModel;
    private StudentProfileModel $studentProfileModel;
    private UserModel $userModel;
    private EmailChangeRequestModel $emailChangeRequests;
    private EmailChangeVerificationModel $emailChangeVerifications;
    private MailService $mailService;

    public function __construct()
    {
        $this->feeModel            = new StudentFeeModel();
        $this->programModel        = new ProgramModel();
        $this->semesterModel       = new SemesterModel();
        $this->studentProfileModel = new StudentProfileModel();
        $this->userModel           = new UserModel();
        $this->emailChangeRequests = new EmailChangeRequestModel();
        $this->emailChangeVerifications = new EmailChangeVerificationModel();
        $this->mailService = new MailService();
    }

    // ============================================================================
    // DASHBOARD
    // ============================================================================

    public function showDashboard(): void
    {
        $stats = $this->getDashboardStats();

        $this->view('accountant.dashboard', [
            'title'     => 'Accountant Dashboard',
            'user_name' => (string) ($_SESSION['user_name'] ?? 'Accountant'),
            'user_role' => 'ACCOUNTANT',
            'stats'     => $stats,
            'programs'  => $this->getActivePrograms(),
            'semester_fees' => $this->getAllSemesterFees(),
        ]);
    }

    private function getDashboardStats(): array
    {
        $allFees = $this->feeModel->all() ?? [];

        // Spec: ALL money arithmetic MUST use BCMath — never float
        $totalCollected = '0.00';
        $totalPending   = '0.00';

        // Verify totals from actual student_fees rows tied to current semesters.
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT sf.amount_paid, sem.fee_amount
            FROM student_fees sf
            JOIN users u ON u.id = sf.student_id AND u.role = "STUDENT"
            JOIN semesters sem ON sem.id = sf.semester_id AND sem.is_current = 1
        ');
        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        foreach ($rows as $row) {
            $semesterFee = (string) ($row['fee_amount'] ?? '0.00');
            if (bccomp($semesterFee, '0', 2) <= 0) {
                continue;
            }

            $amountPaid = (string) ($row['amount_paid'] ?? '0.00');
            $totalCollected = bcadd($totalCollected, $amountPaid, 2);

            $pending = bcsub($semesterFee, $amountPaid, 2);
            if (bccomp($pending, '0', 2) > 0) {
                $totalPending = bcadd($totalPending, $pending, 2);
            }
        }

        $activeSemesters     = $this->semesterModel->where('is_current', 1) ?? [];
        $activeSemesterCount = count($activeSemesters);

        $students      = $this->userModel->where('role', 'STUDENT') ?? [];
        $totalStudents = count($students);

        $semesters = $this->semesterModel->all('academic_year', 'DESC') ?? [];
        $semesterFees = [];
        foreach ($semesters as $semester) {
            $semesterFees[(int) $semester['id']] = (string) ($semester['fee_amount'] ?? '0.00');
        }

        $paidBySemester = [];
        $dueBySemester = [];
        foreach ($allFees as $fee) {
            $semesterId = (int) ($fee['semester_id'] ?? 0);
            if ($semesterId === 0) {
                continue;
            }

            $amountPaid = (string) ($fee['amount_paid'] ?? '0.00');
            $paidBySemester[$semesterId] = bcadd($paidBySemester[$semesterId] ?? '0.00', $amountPaid, 2);

            $semesterFee = $semesterFees[$semesterId] ?? '0.00';
            $dueBySemester[$semesterId] = bcadd($dueBySemester[$semesterId] ?? '0.00', $semesterFee, 2);
        }

        $collectionSeries = [];
        foreach (array_slice($semesters, 0, 7) as $semester) {
            $semesterId = (int) ($semester['id'] ?? 0);
            $paid = $paidBySemester[$semesterId] ?? '0.00';
            $due = $dueBySemester[$semesterId] ?? '0.00';
            if (bccomp($due, '0', 2) <= 0) {
                $collectionSeries[] = 0;
                continue;
            }

            $ratio = (float) bcdiv($paid, $due, 4);
            $collectionSeries[] = (int) round($ratio * 100);
        }

        while (count($collectionSeries) < 7) {
            $collectionSeries[] = 0;
        }

        return [
            'total_collected'  => $totalCollected,
            'total_pending'    => $totalPending,
            'active_semesters' => $activeSemesterCount,
            'total_students'   => $totalStudents,
            'collection_series' => $collectionSeries,
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

        $this->view('accountant.profile', [
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
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'ACCOUNTANT') {
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
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'ACCOUNTANT') {
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
    // FEES MANAGEMENT
    // ============================================================================

    public function showFees(): void
    {
        $this->redirect('/accountant/semester-fees');
    }

    public function showSemesterFees(): void
    {
        $this->view('accountant.semester-fees', [
            'title'               => 'Accountant - Semester Fees',
            'user_name'           => (string) ($_SESSION['user_name'] ?? 'Accountant'),
            'programs'            => $this->getActivePrograms(),
            'selected_program_id' => (int) $this->input('program_id', 0),
            'selected_semester_id'=> (int) $this->input('semester_id', 0),
            'semester_fees'       => $this->getAllSemesterFees(),
        ]);
    }

    public function showStudentFees(): void
    {
        $this->view('accountant.student-fees', [
            'title'               => 'Accountant - Student Fees',
            'user_name'           => (string) ($_SESSION['user_name'] ?? 'Accountant'),
            'programs'            => $this->getActivePrograms(),
            'selected_program_id' => (int) $this->input('program_id', 0),
            'selected_semester_id'=> (int) $this->input('semester_id', 0),
            'selected_status'     => strtoupper(trim((string) $this->input('status', ''))),
        ]);
    }

    // ============================================================================
    // FEE MANAGEMENT API ENDPOINTS
    // ============================================================================

    public function apiGetSemesters(): void
    {
        $semesters = $this->semesterModel->all() ?? [];
        $db        = Database::connection();

        foreach ($semesters as &$semester) {
            $stmt = $db->prepare('SELECT COUNT(*) FROM student_fees WHERE semester_id = :sid');
            $stmt->execute(['sid' => $semester['id']]);
            $semester['student_count'] = (int) $stmt->fetchColumn();
        }

        $this->json(['success' => true, 'data' => $semesters]);
    }

    public function apiGetPrograms(): void
    {
        $this->json(['success' => true, 'data' => $this->getActivePrograms()]);
    }

    public function apiGetProgramSemesters(int $programId): void
    {
        if ($programId <= 0) {
            $this->json(['success' => false, 'message' => 'Program not found.'], 404);
            return;
        }

        $activeOnly = (int) $this->input('active', 0) === 1;
        $semesters  = $this->getProgramSemesters($programId, $activeOnly);
        $this->json(['success' => true, 'data' => $semesters]);
    }

    public function apiGetStudentFees(int $semesterId): void
    {
        $studentFees = $this->buildStudentFeeList($semesterId);
        $this->json(['success' => true, 'data' => $studentFees]);
    }

    public function apiGetFilteredStudentFees(): void
    {
        try {
            $programId  = (int) $this->input('program_id', 0);
            $semesterId = (int) $this->input('semester_id', 0);
            $registration = trim((string) $this->input('registration', ''));
            $registrationLower = strtolower($registration);
            $statusFilter = strtoupper(trim((string) $this->input('status', '')));

            $db = Database::connection();

            if ($registration !== '') {
                $studentStmt = $db->prepare('
                    SELECT u.id AS student_id, sp.program_id
                    FROM users u
                    LEFT JOIN student_profiles sp ON sp.user_id = u.id
                    WHERE u.role = "STUDENT"
                      AND LOWER(TRIM(COALESCE(sp.registration_number, u.login_id))) = :reg
                    LIMIT 1
                ');
                $studentStmt->execute(['reg' => $registrationLower]);
                $student = $studentStmt->fetch(\PDO::FETCH_ASSOC) ?: null;

                if ($student) {
                    $studentId = (int) ($student['student_id'] ?? 0);
                    $semesterId = 0;
                    $programIdFromProfile = (int) ($student['program_id'] ?? 0);

                    $feeSemStmt = $db->prepare('
                        SELECT semester_id
                        FROM student_fees
                        WHERE student_id = ?
                        ORDER BY id DESC
                        LIMIT 1
                    ');
                    $feeSemStmt->execute([$studentId]);
                    $feeSem = $feeSemStmt->fetch(\PDO::FETCH_ASSOC) ?: null;
                    if ($feeSem) {
                        $semesterId = (int) ($feeSem['semester_id'] ?? 0);
                    }

                if ($semesterId <= 0 && $programIdFromProfile > 0) {
                    $semStmt = $db->prepare('
                        SELECT id
                        FROM semesters
                        WHERE program_id = ? AND is_current = 1
                        ORDER BY semester_number ASC
                        LIMIT 1
                    ');
                    $semStmt->execute([$programIdFromProfile]);
                    $semester = $semStmt->fetch(\PDO::FETCH_ASSOC) ?: null;
                    if ($semester) {
                        $semesterId = (int) $semester['id'];
                    }
                }

                if ($semesterId <= 0 && $programIdFromProfile > 0) {
                    $semStmt = $db->prepare('
                        SELECT id
                        FROM semesters
                        WHERE program_id = ? AND semester_number = 1
                        ORDER BY (start_date IS NULL) ASC, start_date ASC, academic_year ASC
                        LIMIT 1
                    ');
                    $semStmt->execute([$programIdFromProfile]);
                    $semester = $semStmt->fetch(\PDO::FETCH_ASSOC) ?: null;
                    if ($semester) {
                        $semesterId = (int) $semester['id'];
                    }
                }

                    if ($semesterId > 0) {
                        $insertStmt = $db->prepare('
                            INSERT IGNORE INTO student_fees (student_id, semester_id, amount_paid)
                            VALUES (?, ?, "0.00")
                        ');
                        $insertStmt->execute([$studentId, $semesterId]);
                    }
                }
            }

            $where = ["u.role = 'STUDENT'"];
            $params = [];

            $applyProgramFilter = $programId > 0 && $registration === '';
            $applySemesterFilter = $semesterId > 0 && $registration === '';

            if ($applyProgramFilter) {
                $where[] = 'sp.program_id = :program_id';
                $params['program_id'] = $programId;
            }

            if ($applySemesterFilter) {
                $where[] = 'sf.semester_id = :semester_id';
                $params['semester_id'] = $semesterId;
            }

            if ($registration !== '') {
                $where[] = 'LOWER(TRIM(COALESCE(sp.registration_number, u.login_id))) LIKE :reg_like';
                $params['reg_like'] = '%' . $registrationLower . '%';
            }

            $sql = "SELECT sf.id AS fee_id, sf.amount_paid, sf.student_id,
                           u.full_name, u.login_id,
                           sp.registration_number,
                           sem.id AS semester_id, sem.semester_number, sem.academic_year, sem.fee_amount
                    FROM student_fees sf
                    JOIN users u ON u.id = sf.student_id
                    JOIN semesters sem ON sem.id = sf.semester_id
                    LEFT JOIN student_profiles sp ON sp.user_id = u.id";

            if ($where) {
                $sql .= ' WHERE ' . implode(' AND ', $where);
            }

            $sql .= ' ORDER BY COALESCE(sp.registration_number, u.login_id) ASC, u.id ASC';

            $stmt = $db->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

            $result = [];
            foreach ($rows as $row) {
                $semesterFee = (string) ($row['fee_amount'] ?? '0.00');
                $amountPaid  = (string) ($row['amount_paid'] ?? '0.00');
                [$pending, $status] = $this->computePendingAndStatus($semesterFee, $amountPaid);

                if ($statusFilter !== '' && $status !== $statusFilter) {
                    continue;
                }

                $result[] = [
                    'fee_id'              => (int) $row['fee_id'],
                    'student_id'          => (int) $row['student_id'],
                    'student_name'        => (string) ($row['full_name'] ?? 'Unknown'),
                    'registration_number' => (string) ($row['registration_number'] ?? $row['login_id'] ?? 'N/A'),
                    'semester_id'         => (int) $row['semester_id'],
                    'semester_label'      => (string) ($row['academic_year'] ?? '') . ' - S' . (int) ($row['semester_number'] ?? 0),
                    'fee_amount'          => $semesterFee,
                    'amount_paid'         => $amountPaid,
                    'pending'             => $pending,
                    'status'              => $status,
                ];
            }

            $this->json(['success' => true, 'data' => $result]);
        } catch (\Throwable $e) {
            $this->json([
                'success' => false,
                'message' => 'Unable to load student fees. ' . $e->getMessage(),
            ], 500);
        }
    }


    public function updateSemesterFeeAmount(int $semesterId): void
    {
        $feeAmountRaw = (string) $this->input('fee_amount', '0');

        // Spec: validate with BCMath — must be > 0
        if (!is_numeric($feeAmountRaw) || bccomp($feeAmountRaw, '0', 2) <= 0) {
            $this->json(['success' => false, 'message' => 'Fee amount must be a positive number.'], 422);
            return;
        }

        $feeAmount = number_format((float) $feeAmountRaw, 2, '.', '');

        $semester = $this->semesterModel->find($semesterId);
        if (!$semester) {
            $programId = (int) $this->input('program_id', 0);
            $semesterNumber = (int) $this->input('semester_number', 0);
            $academicYear = trim((string) $this->input('academic_year', ''));

            if ($programId > 0 && $semesterNumber > 0) {
                $db = Database::connection();
                $sql = 'SELECT * FROM semesters WHERE program_id = :program_id AND semester_number = :semester_number';
                $params = ['program_id' => $programId, 'semester_number' => $semesterNumber];

                if ($academicYear !== '') {
                    $sql .= ' AND academic_year = :academic_year';
                    $params['academic_year'] = $academicYear;
                }

                $sql .= ' ORDER BY id DESC LIMIT 1';
                $stmt = $db->prepare($sql);
                $stmt->execute($params);
                $semester = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
                if ($semester) {
                    $semesterId = (int) ($semester['id'] ?? $semesterId);
                }
            }

            if (!$semester) {
                $this->json(['success' => false, 'message' => 'Semester not found.'], 404);
                return;
            }
        }

        $this->semesterModel->updateById($semesterId, ['fee_amount' => $feeAmount]);

        $this->json([
            'success' => true,
            'message' => 'Semester fee amount updated successfully.',
            'data'    => ['semester_id' => $semesterId, 'fee_amount' => $feeAmount],
        ]);
    }

    public function updateStudentPayment(int $feeId): void
    {
        $amountPaidRaw = (string) $this->input('amount_paid', '0');
        $studentId = (int) $this->input('student_id', 0);
        $semesterId = (int) $this->input('semester_id', 0);

        // Spec: must use BCMath; cannot be negative
        if (!is_numeric($amountPaidRaw) || $this->compareAmounts($amountPaidRaw, '0') < 0) {
            $this->json(['success' => false, 'message' => 'Amount paid cannot be negative.'], 422);
            return;
        }

        $fee = $this->feeModel->find($feeId);
        if (!$fee) {
            if ($studentId > 0 && $semesterId > 0) {
                $db = Database::connection();
                $stmt = $db->prepare('SELECT * FROM student_fees WHERE student_id = ? AND semester_id = ? LIMIT 1');
                $stmt->execute([$studentId, $semesterId]);
                $fee = $stmt->fetch(\PDO::FETCH_ASSOC) ?: null;
                if ($fee) {
                    $feeId = (int) ($fee['id'] ?? $feeId);
                }
            }

            if (!$fee) {
                $this->json(['success' => false, 'message' => 'Fee record not found.'], 404);
                return;
            }
        }

        $semester    = $this->semesterModel->find((int) $fee['semester_id']);
        $semesterFee = (string) ($semester['fee_amount'] ?? '0.00');
        $amountPaid  = number_format((float) $amountPaidRaw, 2, '.', '');

        // Spec: amount_paid must not exceed semester fee_amount
        if ($this->compareAmounts($amountPaid, $semesterFee) > 0) {
            $this->json([
                'success' => false,
                'message' => 'Amount paid cannot exceed semester fee of ' . $semesterFee,
            ], 422);
            return;
        }

        $this->feeModel->updateById($feeId, ['amount_paid' => $amountPaid]);

        [$pending, $status] = $this->computePendingAndStatus($semesterFee, $amountPaid);

        $this->json([
            'success' => true,
            'message' => 'Student payment updated successfully.',
            'data'    => [
                'fee_id'      => $feeId,
                'amount_paid' => $amountPaid,
                'pending'     => $pending,
                'status'      => $status,
            ],
        ]);
    }

    // ============================================================================
    // PRIVATE HELPERS
    // ============================================================================

    /** Build enriched student fee records for a given semester using BCMath */
    private function buildStudentFeeList(int $semesterId): array
    {
        $semester    = $this->semesterModel->find($semesterId);
        $semesterFee = (string) ($semester['fee_amount'] ?? '0.00');

        $db   = Database::connection();
        $stmt = $db->prepare('SELECT * FROM student_fees WHERE semester_id = :sid ORDER BY student_id ASC');
        $stmt->execute(['sid' => $semesterId]);
        $allFees = $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];

        $result = [];
        foreach ($allFees as $fee) {
            $student        = $this->userModel->find((int) $fee['student_id']);
            $studentProfile = $this->studentProfileModel->findByUserId((int) $fee['student_id']);
            $amountPaid     = (string) ($fee['amount_paid'] ?? '0.00');
            [$pending, $status] = $this->computePendingAndStatus($semesterFee, $amountPaid);

            $result[] = [
                'id'                  => $fee['id'],
                'student_id'          => $fee['student_id'],
                'student_name'        => $student['full_name'] ?? 'Unknown',
                'registration_number' => $studentProfile['registration_number'] ?? ($student['login_id'] ?? 'N/A'),
                'fee_amount'          => $semesterFee,
                'amount_paid'         => $amountPaid,
                'pending'             => $pending,
                'status'              => $status,
            ];
        }

        return $result;
    }

    /** @return array<int, array<string, mixed>> */
    private function getActivePrograms(): array
    {
        $db = Database::connection();
        $stmt = $db->query('SELECT * FROM programs WHERE is_active = 1 ORDER BY id ASC');
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int, array<string, mixed>> */
    private function getProgramSemesters(int $programId, bool $activeOnly): array
    {
        $db = Database::connection();
        $sql = 'SELECT id, program_id, semester_number, academic_year, is_current, fee_amount
                FROM semesters
                WHERE program_id = :program_id';

        if ($activeOnly) {
            $sql .= ' AND is_current = 1';
        }

        $sql .= ' ORDER BY id ASC';

        $stmt = $db->prepare($sql);
        $stmt->execute(['program_id' => $programId]);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int, array<string, mixed>> */
    private function getAllSemesterFees(): array
    {
        $db = Database::connection();
        $sql = 'SELECT s.id, s.program_id, s.semester_number, s.academic_year, s.is_current, s.fee_amount,
                       p.program_name
                FROM semesters s
                JOIN programs p ON p.id = s.program_id
                ORDER BY s.id ASC';

        $stmt = $db->query($sql);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array{0: string, 1: string} */
    private function computePendingAndStatus(string $semesterFee, string $amountPaid): array
    {
        // If fee amount is not set (0 or empty), mark as PENDING
        if ($this->compareAmounts($semesterFee, '0') <= 0) {
            return ['0.00', 'PENDING'];
        }

        $pending = $this->subtractAmounts($semesterFee, $amountPaid);
        if ($this->compareAmounts($pending, '0') < 0) {
            $pending = '0.00';
        }

        $status = $this->compareAmounts($pending, '0') <= 0 ? 'PAID' : 'PENDING';
        return [$pending, $status];
    }

    private function compareAmounts(string $left, string $right): int
    {
        if (function_exists('bccomp')) {
            return bccomp($left, $right, 2);
        }

        $leftFloat = (float) $left;
        $rightFloat = (float) $right;
        return $leftFloat < $rightFloat ? -1 : ($leftFloat > $rightFloat ? 1 : 0);
    }

    private function subtractAmounts(string $left, string $right): string
    {
        if (function_exists('bcsub')) {
            return bcsub($left, $right, 2);
        }

        $result = (float) $left - (float) $right;
        return number_format($result, 2, '.', '');
    }

}
