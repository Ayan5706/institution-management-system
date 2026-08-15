<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\UserModel;
use App\Models\PasswordResetRequestModel;
use App\Models\ActivationTokenModel;
use App\Models\EmailChangeRequestModel;
use App\Models\EmailChangeVerificationModel;
use App\Models\SystemConfigModel;
use App\Models\StudentProfileModel;
use App\Models\TeacherAssignmentModel;
use App\Models\ProgramModel;
use App\Models\SemesterModel;
use App\Models\StudentFeeModel;
use App\Models\SubjectModel;
use App\Models\AttendanceModel;
use App\Services\MailService;
use App\Core\Database;
use DateTimeImmutable;
use DateTimeZone;

class PrincipalController extends BaseController
{
    private UserModel $userModel;
    private PasswordResetRequestModel $resetModel;
    private ActivationTokenModel $activationTokens;
    private EmailChangeRequestModel $emailChangeRequests;
    private EmailChangeVerificationModel $emailChangeVerifications;
    private SystemConfigModel $configModel;
    private StudentProfileModel $studentModel;
    private TeacherAssignmentModel $teacherAssignmentModel;
    private ProgramModel $programModel;
    private SemesterModel $semesterModel;
    private StudentFeeModel $feeModel;
    private SubjectModel $subjectModel;
    private AttendanceModel $attendanceModel;
    private MailService $mailService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->resetModel = new PasswordResetRequestModel();
        $this->activationTokens = new ActivationTokenModel();
        $this->emailChangeRequests = new EmailChangeRequestModel();
        $this->emailChangeVerifications = new EmailChangeVerificationModel();
        $this->configModel = new SystemConfigModel();
        $this->studentModel = new StudentProfileModel();
        $this->teacherAssignmentModel = new TeacherAssignmentModel();
        $this->programModel = new ProgramModel();
        $this->semesterModel = new SemesterModel();
        $this->feeModel = new StudentFeeModel();
        $this->subjectModel = new SubjectModel();
        $this->attendanceModel = new AttendanceModel();
        $this->mailService = new MailService();
    }

    // ============================================================================
    // DASHBOARD
    // ============================================================================

    public function showDashboard(): void
    {
        $stats = $this->getDashboardStats();
        $attendanceSeries = $this->getAttendanceSeries();
        $attendanceToday = count($attendanceSeries) ? end($attendanceSeries) : 0;
        $gradePercent = $this->getOverallAttendancePercent();

        // Spec: Principal only approves resets from VP, MANAGER, ACCOUNTANT (not students/teachers)
        $allPending    = $this->resetModel->where('status', 'PENDING');
        $enrichedResets = [];
        foreach ($allPending as $reset) {
            $user = $this->userModel->find((int) $reset['requested_by']);
            if ($user && in_array($user['role'], ['VP', 'MANAGER', 'ACCOUNTANT'], true)) {
                $reset['user_name'] = $user['full_name'];
                $reset['user_role'] = $user['role'];
                $reset['login_id']  = $user['login_id'];
                $enrichedResets[]   = $reset;
            }
        }

        $this->view('principal.dashboard', [
            'title'          => 'Principal Dashboard',
            'user_name'      => (string) ($_SESSION['user_name'] ?? 'Principal'),
            'user_role'      => 'PRINCIPAL',
            'stats'          => $stats,
            'pending_resets' => $enrichedResets,
            'attendance_series' => $attendanceSeries,
            'attendance_today' => $attendanceToday,
            'grade_percent' => $gradePercent,
        ]);
    }

    private function getDashboardStats(): array
    {
        // Total students
        $students = $this->userModel->where('role', 'STUDENT');
        $totalStudents = count($students);

        // Total teachers
        $teachers = $this->userModel->where('role', 'TEACHER');
        $totalTeachers = count($teachers);

        // Active programs
        $programs = $this->programModel->where('is_active', 1);
        $totalPrograms = count($programs);

        // Pending password resets (admin roles only)
        $pendingResets = $this->resetModel->where('status', 'PENDING');
        $pendingCount = 0;
        foreach ($pendingResets as $reset) {
            $user = $this->userModel->find((int) $reset['requested_by']);
            if ($user && in_array($user['role'], ['VP', 'MANAGER', 'ACCOUNTANT'], true)) {
                $pendingCount++;
            }
        }

        $pendingEmailChanges = $this->emailChangeRequests->where('status', 'PENDING');
        $pendingEmailCount = 0;
        foreach ($pendingEmailChanges as $request) {
            $user = $this->userModel->find((int) ($request['user_id'] ?? 0));
            if ($user && in_array($user['role'], ['VP', 'MANAGER', 'ACCOUNTANT'], true)) {
                $pendingEmailCount++;
            }
        }

        return [
            'total_students' => $totalStudents,
            'total_teachers' => $totalTeachers,
            'total_programs' => $totalPrograms,
            'pending_resets' => $pendingCount,
            'pending_email_changes' => $pendingEmailCount,
        ];
    }

    /** @return array<int, int> */
    private function getAttendanceSeries(): array
    {
        $records = $this->attendanceModel->all();
        $byDate = [];

        foreach ($records as $record) {
            $date = (string) ($record['date'] ?? '');
            if ($date === '') {
                continue;
            }

            if (!isset($byDate[$date])) {
                $byDate[$date] = ['total' => 0, 'present' => 0];
            }

            $byDate[$date]['total']++;

            if (strtoupper((string) ($record['status'] ?? '')) === 'PRESENT') {
                $byDate[$date]['present']++;
            }
        }

        $today = new \DateTimeImmutable('today');
        $series = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = $today->sub(new \DateInterval('P' . $i . 'D'))->format('Y-m-d');
            $counts = $byDate[$date] ?? ['total' => 0, 'present' => 0];
            $percent = $counts['total'] > 0
                ? (int) round(($counts['present'] / $counts['total']) * 100)
                : 0;
            $series[] = $percent;
        }

        return $series;
    }

    private function getOverallAttendancePercent(): int
    {
        $records = $this->attendanceModel->all();
        if ($records === []) {
            return 0;
        }

        $present = 0;
        foreach ($records as $record) {
            if (strtoupper((string) ($record['status'] ?? '')) === 'PRESENT') {
                $present++;
            }
        }

        return (int) round(($present / count($records)) * 100);
    }

    // ============================================================================
    // ACCOUNTS MANAGEMENT
    // ============================================================================

    public function showAccounts(): void
    {
        $vpAccounts = $this->userModel->where('role', 'VP');
        $managerAccounts = $this->userModel->where('role', 'MANAGER');
        $accountantAccounts = $this->userModel->where('role', 'ACCOUNTANT');

        $adminAccounts = array_merge($vpAccounts, $managerAccounts, $accountantAccounts);
        usort($adminAccounts, static function (array $a, array $b): int {
            return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
        });

        $this->view('principal.accounts', [
            'title' => 'Manage Accounts',
            'pageSubtitle' => 'Manage admin-level accounts',
            'admin_accounts' => $adminAccounts,
        ]);
    }

    public function createAccountForm(): void
    {
        $this->view('principal.create-account', [
            'title' => 'Create Admin Account',
            'pageSubtitle' => 'Add new VP, Manager, or Accountant',
        ]);
    }

    public function storeAccount(): void
    {
        // Parse JSON request body
        $data = json_decode((string) file_get_contents('php://input'), true) ?? [];

        $fullName = (string) ($data['full_name'] ?? $this->input('full_name', ''));
        $email = (string) ($data['email'] ?? $this->input('email', ''));
        $role = strtoupper((string) ($data['role'] ?? $this->input('role', '')));
        $phone = '';

        $errors = [];

        // Validation
        // Full Name: only letters and spaces
        if ($fullName === '') {
            $errors['fullName'] = 'Full Name is required.';
        } elseif (!preg_match('/^[a-zA-Z\s]+$/', $fullName)) {
            $errors['fullName'] = 'Please enter your complete name (e.g., John Doe).';
        } elseif (strlen(trim($fullName)) < 2) {
            $errors['fullName'] = 'Full Name must be at least 2 characters.';
        }

        // Email: alphanumeric before @, must be Gmail format
        if ($email === '') {
            $errors['email'] = 'Email is required.';
        } elseif (!preg_match('/^[a-zA-Z0-9]+@gmail\.com$/i', $email)) {
            $errors['email'] = 'Please enter a valid email.';
        }

        // Role validation
        if ($role === '' || !in_array($role, ['VP', 'MANAGER', 'ACCOUNTANT'])) {
            $this->json([
                'success' => false,
                'message' => 'Invalid role. Must be VP, MANAGER, or ACCOUNTANT.',
            ], 422);
            return;
        }

        $loginId = $this->generateNextLoginId($role);

        // Return errors if any
        if (!empty($errors)) {
            $this->json([
                'success' => false,
                'message' => 'Please fix the errors below.',
                'errors' => $errors,
            ], 422);
            return;
        }

        // Check if login_id already exists
        if ($this->userModel->firstWhere('login_id', $loginId)) {
            $this->json([
                'success' => false,
                'message' => 'Login ID already exists.',
                'errors' => ['loginId' => 'This Login ID is already in use.'],
            ], 409);
            return;
        }

        // Check if email already exists
        if ($this->userModel->firstWhere('email', $email)) {
            $this->json([
                'success' => false,
                'message' => 'Email already exists.',
                'errors' => ['email' => 'This email is already in use.'],
            ], 409);
            return;
        }

        // Generate temporary password
        $tempPassword = $this->generateTemporaryPassword();
        $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);

        // Create user
        try {
            $userId = $this->userModel->create([
                'role' => $role,
                'login_id' => $loginId,
                'email' => $email,
                'phone' => $phone,
                'full_name' => $fullName,
                'password_hash' => $passwordHash,
                'is_active' => 1,
                'must_change_password' => 1, // Force password change on first login
                'created_by' => (int) ($_SESSION['user_id'] ?? 0),
                'created_at' => date('Y-m-d H:i:s'),
            ]);


            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
                ->modify('+24 hours')
                ->format('Y-m-d H:i:s');

            $tokenId = $this->activationTokens->create([
                'user_id' => $userId,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
                'created_by' => (int) ($_SESSION['user_id'] ?? 0),
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

            $this->mailService->sendMail($email, $subject, $htmlBody, $textBody);


            $this->json([
                'success' => true,
                'message' => 'Account created. Activation email sent to the new user.',
                'data'    => [
                    'id'            => $userId,
                    'role'          => $role,
                    'temp_password' => $tempPassword,
                ],
            ], 201);
        } catch (\Exception $e) {
            if (isset($tokenId) && (int) $tokenId > 0) {
                $this->activationTokens->deleteById((int) $tokenId);
            }
            if (isset($userId) && (int) $userId > 0) {
                $this->userModel->deleteById((int) $userId);
            }
            $this->json([
                'success' => false,
                'message' => 'Error creating account: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function getNextLoginId(): void
    {
        $role = strtoupper((string) $this->input('role', ''));
        if ($role === '' || !in_array($role, ['VP', 'MANAGER', 'ACCOUNTANT'], true)) {
            $this->json([
                'success' => false,
                'message' => 'Invalid role. Must be VP, MANAGER, or ACCOUNTANT.',
            ], 422);
            return;
        }

        $loginId = $this->generateNextLoginId($role);

        $this->json([
            'success' => true,
            'data' => ['login_id' => $loginId],
        ]);
    }

    private function generateNextLoginId(string $role): string
    {
        $prefixMap = [
            'VP' => 'VP',
            'MANAGER' => 'MGR',
            'ACCOUNTANT' => 'ACC',
        ];

        $prefix = $prefixMap[$role] ?? '';
        if ($prefix === '') {
            return '';
        }

        $accounts = $this->userModel->where('role', $role);
        $maxNumber = 0;

        foreach ($accounts as $account) {
            $loginId = strtoupper((string) ($account['login_id'] ?? ''));
            if (strpos($loginId, $prefix) !== 0) {
                continue;
            }

            $suffix = substr($loginId, strlen($prefix));
            if ($suffix !== '' && ctype_digit($suffix)) {
                $number = (int) $suffix;
                if ($number > $maxNumber) {
                    $maxNumber = $number;
                }
            }
        }

        $nextNumber = $maxNumber + 1;

        while (true) {
            $candidate = sprintf('%s%03d', $prefix, $nextNumber);
            if (!$this->userModel->firstWhere('login_id', $candidate)) {
                return $candidate;
            }
            $nextNumber++;
        }
    }

    public function toggleAccountStatus(int $id): void
    {
        try {
            $user = $this->userModel->find($id);

            if (!$user) {
                $this->json([
                    'success' => false,
                    'message' => 'User not found.',
                ], 404);
                return;
            }

            // Only allow toggling for admin roles
            $role = $user['role'] ?? '';
            if (!in_array($role, ['VP', 'MANAGER', 'ACCOUNTANT'])) {
                $this->json([
                    'success' => false,
                    'message' => 'Can only toggle admin accounts.',
                ], 403);
                return;
            }

            $newStatus = $user['is_active'] ? 0 : 1;

            // Update only is_active - updated_at is managed by database trigger
            $updated = $this->userModel->updateById($id, ['is_active' => $newStatus]);

            if (!$updated) {
                $this->json([
                    'success' => false,
                    'message' => 'Failed to update account.',
                ], 400);
                return;
            }

            $this->json([
                'success' => true,
                'message' => $newStatus ? 'Account activated.' : 'Account deactivated.',
                'data'    => ['status' => $newStatus],
            ]);
        } catch (\Throwable $e) {
            error_log('Toggle account status error: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Server error occurred.',
            ], 500);
        }
    }

    // ============================================================================
    // STUDENTS (READ-ONLY)
    // ============================================================================

    public function showStudents(): void
    {
        // Get all programs and active semesters for filters
        $programs = $this->programModel->where('is_active', 1);
        $db = Database::connection();
        $semesterStmt = $db->query(
            'SELECT s.id, s.semester_number, s.academic_year, s.program_id, p.program_name, p.program_code
             FROM semesters s
             JOIN programs p ON p.id = s.program_id
             WHERE s.is_current = 1
               ORDER BY s.id ASC'
        );
        $semesters = $semesterStmt->fetchAll() ?: [];

        $this->view('principal.students', [
            'title' => 'Students',
            'pageSubtitle' => 'View all students (read-only)',
            'students' => [], // Populate via JS/API
            'programs' => $programs,
            'semesters' => $semesters,
        ]);
    }

    public function showStudentDetail(int $id): void
    {
        $user = $this->userModel->find($id);

        if (!$user || $user['role'] !== 'STUDENT') {
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

        // Get who created this student account
        $createdBy = null;
        $createdById = (int) ($user['created_by'] ?? 0);
        if ($createdById > 0) {
            $createdBy = $this->userModel->find($createdById);
        }

        // Get fee status for current semester
        $feeStatus = 'N/A';
        $feePaid = 0;
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
            } elseif ($currentSemester['fee_amount']) {
                $feeStatus = 'Pending';
            }
        }

        $this->view('principal.student-detail', [
            'title' => 'Student Details',
            'student' => $user,
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
    // TEACHERS (READ-ONLY)
    // ============================================================================

    public function showTeachers(): void
    {
        $status = (string) $this->input('status', '');

        $query = 'SELECT u.id, u.login_id, u.email, u.full_name, u.phone, u.is_active
                  FROM users u
                  WHERE u.role = "TEACHER"';

        if ($status !== '') {
            $query .= ' AND u.is_active = ' . ((int) ($status === 'active' ? 1 : 0));
        }

        $query .= ' ORDER BY u.id ASC';

        $programs = $this->programModel->where('is_active', 1);

        $this->view('principal.teachers', [
            'title' => 'Teachers',
            'pageSubtitle' => 'View all teachers (read-only)',
            'teachers' => [], // Populate via JS/API
            'programs' => $programs,
        ]);
    }

    public function showTeacherDetail(int $id): void
    {
        $user = $this->userModel->find($id);

        if (!$user || $user['role'] !== 'TEACHER') {
            http_response_code(404);
            echo 'Teacher not found';
            return;
        }

        $assignments = $this->teacherAssignmentModel->where('teacher_id', $id);
        $assignmentDetails = [];

        foreach ($assignments as $assignment) {
            $subject = $this->subjectModel->find((int) ($assignment['subject_id'] ?? 0));
            if (!$subject) {
                continue;
            }

            $semester = $this->semesterModel->find((int) ($subject['semester_id'] ?? 0));
            $program = $semester ? $this->programModel->find((int) ($semester['program_id'] ?? 0)) : null;

            $assignmentDetails[] = [
                'subject_code' => $subject['subject_code'] ?? 'N/A',
                'subject_name' => $subject['subject_name'] ?? 'N/A',
                'semester_number' => $semester['semester_number'] ?? null,
                'academic_year' => $semester['academic_year'] ?? null,
                'program_name' => $program['program_name'] ?? null,
                'program_code' => $program['program_code'] ?? null,
            ];
        }

        // Get who created this teacher account
        $createdBy = null;
        $createdById = (int) ($user['created_by'] ?? 0);
        if ($createdById > 0) {
            $createdBy = $this->userModel->find($createdById);
        }

        $this->view('principal.teacher-detail', [
            'title' => 'Teacher Details',
            'teacher' => $user,
            'assignments' => $assignmentDetails,
            'created_by' => $createdBy,
        ]);
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

        $emailVerification = $this->emailChangeVerifications->getActiveForUser($userId);
        $emailVerificationPending = $emailVerification !== null;
        $emailVerificationEmail = $emailVerificationPending ? (string) ($emailVerification['new_email'] ?? '') : '';
        $emailVerificationExpiresAt = $emailVerificationPending ? (string) ($emailVerification['expires_at'] ?? '') : '';

        $this->view('principal.profile', [
            'title' => 'My Profile',
            'pageSubtitle' => 'Manage your profile details',
            'user' => $user,
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

    public function updateEmail(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->userModel->find($userId);
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'PRINCIPAL') {
            $this->json(['success' => false, 'message' => 'Forbidden'], 403);
            return;
        }

        $currentEmail = trim((string) ($user['email'] ?? ''));
        $newEmail = trim((string) $this->input('new_email', ''));
        $confirmEmail = trim((string) $this->input('confirm_email', ''));

        if ($newEmail === '' || $confirmEmail === '') {
            $this->json(['success' => false, 'message' => 'New email and confirmation are required.'], 422);
            return;
        }

        if (!preg_match('/^[A-Za-z0-9]+@gmail\.com$/', $newEmail)) {
            $this->json(['success' => false, 'message' => 'Please enter a valid Gmail address (example: name@gmail.com).'], 422);
            return;
        }

        if (strcasecmp($newEmail, $confirmEmail) !== 0) {
            $this->json(['success' => false, 'message' => 'Email confirmation does not match.'], 422);
            return;
        }

        if ($currentEmail !== '' && strcasecmp($currentEmail, $newEmail) === 0) {
            $this->json(['success' => false, 'message' => 'New email must be different from the current email.'], 422);
            return;
        }

        if (!preg_match('/^[A-Za-z0-9]+@gmail\.com$/', $confirmEmail)) {
            $this->json(['success' => false, 'message' => 'Please enter a valid Gmail address (example: name@gmail.com).'], 422);
            return;
        }

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM users WHERE LOWER(email) = LOWER(?) AND id <> ? LIMIT 1');
        $stmt->execute([$newEmail, $userId]);
        if ($stmt->fetch()) {
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
            $name = (string) ($user['full_name'] ?? 'Principal');
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
            'message' => 'OTP sent to your new email. Enter it to verify and update your email.',
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
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'PRINCIPAL') {
            $this->json(['success' => false, 'message' => 'Forbidden'], 403);
            return;
        }

        $newEmail = trim((string) $this->input('new_email', ''));
        $otp = trim((string) $this->input('email_otp', ''));

        if ($newEmail === '' || $otp === '') {
            $this->json(['success' => false, 'message' => 'New email and OTP are required.'], 422);
            return;
        }

        if (!preg_match('/^[A-Za-z0-9]+@gmail\.com$/', $newEmail)) {
            $this->json(['success' => false, 'message' => 'Please enter a valid Gmail address (example: name@gmail.com).'], 422);
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

        $db = Database::connection();
        $stmt = $db->prepare('SELECT id FROM users WHERE LOWER(email) = LOWER(?) AND id <> ? LIMIT 1');
        $stmt->execute([$newEmail, $userId]);
        if ($stmt->fetch()) {
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

        $this->userModel->updateById($userId, ['email' => $newEmail]);
        $_SESSION['user_email'] = $newEmail;

        $this->json([
            'success' => true,
            'message' => 'Email verified and updated successfully.',
            'data' => ['user_email' => $newEmail],
        ]);
    }

    // ============================================================================
    // SYSTEM CONFIGURATION
    // ============================================================================

    public function showConfig(): void
    {
        $configValues = $this->configModel->getValues([
            'WORKING_DAYS',
            'DAY_START_TIME',
            'DAY_END_TIME',
        ]);

        $workingDayCodes = $this->configModel->getWorkingDayCodes();
        $workingDayCount = $workingDayCodes === [] ? null : (string) count($workingDayCodes);
        if ($workingDayCount !== null) {
            $configValues['WORKING_DAYS'] = $workingDayCount;
        }

        $this->view('principal.config', [
            'title' => 'System Configuration',
            'pageSubtitle' => 'Manage system configuration',
            'config' => $configValues,
        ]);
    }

    public function updateConfig(string $key): void
    {
        $updatedBy = (int) ($_SESSION['user_id'] ?? 0);
        if ($updatedBy === 0) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $allowedKeys = [
            'WORKING_DAYS',
            'DAY_START_TIME',
            'DAY_END_TIME',
        ];

        if (!in_array(strtoupper($key), $allowedKeys, true)) {
            $this->json([
                'success' => false,
                'message' => 'Invalid configuration key. Allowed: ' . implode(', ', $allowedKeys),
            ], 422);
            return;
        }

        $key = strtoupper($key);
        $value = trim((string) $this->input('value', ''));

        if ($value === '') {
            $this->json([
                'success' => false,
                'message' => 'Value cannot be empty.',
            ], 422);
            return;
        }

        if ($key === 'WORKING_DAYS') {
            if (!is_numeric($value)) {
                $this->json([
                    'success' => false,
                    'message' => 'Working days must be a number between 1 and 6.',
                ], 422);
                return;
            }

            $workingDays = (int) $value;
            if ($workingDays < 1 || $workingDays > 6) {
                $this->json([
                    'success' => false,
                    'message' => 'Working days cannot exceed 6.',
                ], 422);
                return;
            }
        }

        if ($key === 'DAY_START_TIME' || $key === 'DAY_END_TIME') {
            if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $value)) {
                $this->json([
                    'success' => false,
                    'message' => 'Time must be in 24-hour HH:MM format.',
                ], 422);
                return;
            }
        }

        try {
            $existing = $this->configModel->firstWhere('config_key', $key);

            if ($existing) {
                $this->configModel->updateById($existing['id'], [
                    'config_value' => $value,
                    'updated_by' => $updatedBy,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            } else {
                $this->configModel->create([
                    'config_key' => $key,
                    'config_value' => $value,
                    'updated_by' => $updatedBy,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }

            error_log('[CONFIG_DEBUG] Principal updateConfig saved: ' . json_encode([
                'key' => $key,
                'value' => $value,
                'user_id' => (int) ($_SESSION['user_id'] ?? 0),
            ]));

            $this->json([
                'success' => true,
                'message' => 'Configuration updated successfully.',
            ]);
        } catch (\Exception $e) {
            \App\Helpers\logger_helper('config_update_error', 'Principal updateConfig failed: ' . $e->getMessage());
            $this->json([
                'success' => false,
                'message' => 'Error updating configuration.',
            ], 500);
        }
    }

    // ============================================================================
    // PASSWORD RESET APPROVALS
    // ============================================================================

    public function showPasswordResets(): void
    {
        $pendingResets = $this->resetModel->where('status', 'PENDING');

        // Get user details for each reset
        $resets = [];
        foreach ($pendingResets as $reset) {
            $user = $this->userModel->find((int) $reset['requested_by']);
            if ($user && in_array($user['role'], ['VP', 'MANAGER', 'ACCOUNTANT'], true)) {
                $resets[] = [
                    'id' => $reset['id'] ?? null,
                    'requested_by' => $reset['requested_by'] ?? null,
                    'status' => $reset['status'] ?? 'PENDING',
                    'created_at' => $reset['created_at'] ?? null,
                    'user_name' => $user['full_name'] ?? 'N/A',
                    'user_email' => $user['email'] ?? 'N/A',
                    'user_role' => $user['role'] ?? 'N/A',
                    'is_admin_user' => true,
                ];
            }
        }

        $this->view('principal.password-resets', [
            'title' => 'Password Reset Requests',
            'pageSubtitle' => 'Approve password reset requests for admin accounts',
            'pending_resets' => $resets,
        ]);
    }

    // ============================================================================
    // EMAIL CHANGE APPROVALS
    // ============================================================================

    public function showEmailChangeRequests(): void
    {
        $pendingRequests = [];
        $requests = $this->emailChangeRequests->where('status', 'PENDING');
        foreach ($requests as $request) {
            $user = $this->userModel->find((int) ($request['user_id'] ?? 0));
            if ($user && in_array($user['role'], ['VP', 'MANAGER', 'ACCOUNTANT'], true)) {
                $pendingRequests[] = $request;
            }
        }
        $this->view('principal.email-requests', [
            'title' => 'Email Change Requests',
            'pageSubtitle' => 'Approve email change requests for admin accounts',
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
        if (!$user || !in_array($user['role'], ['VP', 'MANAGER', 'ACCOUNTANT'], true)) {
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
                . '<p>Your email change request has been approved.</p>'
                . '<p>Your new email address is: <strong>' . htmlspecialchars($newEmail, ENT_QUOTES, 'UTF-8') . '</strong></p>'
                . '<p>If you did not request this change, please contact support immediately.</p>'
                . '<p>— IMS Admin</p>';
            $textBody = "Hello {$name},\n\n"
                . "Your email change request has been approved.\n"
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
        if (!$user || !in_array($user['role'], ['VP', 'MANAGER', 'ACCOUNTANT'], true)) {
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
                    . '<p>Your email change request has been rejected.</p>'
                    . '<p>If you believe this is a mistake, please contact support.</p>'
                    . '<p>— IMS Admin</p>';
                $textBody = "Hello {$name},\n\n"
                    . "Your email change request has been rejected.\n"
                    . "If you believe this is a mistake, please contact support.\n\n"
                    . "— IMS Admin";
                $this->mailService->sendMail($recipient, $subject, $htmlBody, $textBody);
            }

            $this->json([
                'success' => true,
                'message' => 'Email change request dismissed.',
            ]);
        } catch (\Throwable $e) {
            $this->json(['success' => false, 'message' => 'Error dismissing email change request.'], 500);
        }
    }

    public function approvePasswordReset(int $id): void
    {
        $reset = $this->resetModel->find($id);

        if (!$reset) {
            $this->json([
                'success' => false,
                'message' => 'Reset request not found.',
            ], 404);
            return;
        }

        if ($reset['status'] !== 'PENDING') {
            $this->json([
                'success' => false,
                'message' => 'This request has already been processed.',
            ], 409);
            return;
        }

        $user = $this->userModel->find((int) $reset['requested_by']);
        if (!$user || !in_array($user['role'], ['VP', 'MANAGER', 'ACCOUNTANT'])) {
            $this->json([
                'success' => false,
                'message' => 'Invalid reset request.',
            ], 403);
            return;
        }

        // Generate temporary password
        $tempPassword = $this->generateTemporaryPassword();
        $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);

        try {
            // Update user password
            $this->userModel->updateById((int) $user['id'], [
                'password_hash' => $passwordHash,
                'must_change_password' => 1,
            ]);

            // Mark reset as approved
            $this->resetModel->updateById($id, [
                'status' => 'APPROVED',
                'resolved_by' => (int) ($_SESSION['user_id'] ?? 0),
                'resolved_at' => date('Y-m-d H:i:s'),
            ]);

            $emailSent = true;
            $emailError = '';
            try {
                $subject = 'IMS Password Reset Approved';
                $name = (string) ($user['full_name'] ?? 'User');
                $htmlBody = '<p>Hello ' . htmlspecialchars($name, ENT_QUOTES, 'UTF-8') . ',</p>'
                    . '<p>Your password reset request has been approved. Use the temporary password below to log in:</p>'
                    . '<p><strong>' . htmlspecialchars($tempPassword, ENT_QUOTES, 'UTF-8') . '</strong></p>'
                    . '<p>Please change your password immediately after logging in.</p>'
                    . '<p>— IMS Admin</p>';
                $textBody = "Hello {$name},\n\n"
                    . "Your password reset request has been approved. Use this temporary password to log in:\n"
                    . "{$tempPassword}\n\n"
                    . "Please change your password immediately after logging in.\n\n"
                    . "— IMS Admin";
                $this->mailService->sendMail((string) $user['email'], $subject, $htmlBody, $textBody);
            } catch (\Throwable $mailError) {
                $emailSent = false;
                $emailError = $mailError->getMessage();
            }

            if ($emailSent) {
                // Email sent to the target user; do not include the temporary password in the response
                $this->json([
                    'success' => true,
                    'message' => 'Password reset approved and email sent to the user.',
                ]);
                return;
            }

            // Email failed — return temporary password so the principal can notify the user manually
            $this->json([
                'success' => true,
                'message' => 'Password reset approved, but email delivery failed. Use the temporary password below to notify the user manually.',
                'data'    => [
                    'temp_password' => $tempPassword,
                    'user_email'    => $user['email'],
                    'email_error'   => $emailError,
                ],
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error approving password reset.',
            ], 500);
        }
    }

    public function rejectPasswordReset(int $id): void
    {
        $reset = $this->resetModel->find($id);

        if (!$reset) {
            $this->json([
                'success' => false,
                'message' => 'Reset request not found.',
            ], 404);
            return;
        }

        if ($reset['status'] !== 'PENDING') {
            $this->json([
                'success' => false,
                'message' => 'This request has already been processed.',
            ], 409);
            return;
        }

        $user = $this->userModel->find((int) $reset['requested_by']);
        if (!$user || !in_array($user['role'], ['VP', 'MANAGER', 'ACCOUNTANT'])) {
            $this->json([
                'success' => false,
                'message' => 'Invalid reset request.',
            ], 403);
            return;
        }

        try {
            // Spec: status ENUM only has PENDING, APPROVED, EXPIRED — no REJECTED
            $this->resetModel->updateById($id, [
                'status'      => 'EXPIRED',
                'resolved_by' => (int) ($_SESSION['user_id'] ?? 0),
                'resolved_at' => date('Y-m-d H:i:s'),
            ]);

            $this->json([
                'success' => true,
                'message' => 'Password reset request dismissed.',
                'data'    => ['user_email' => $user['email']],
            ]);
        } catch (\Exception $e) {
            $this->json([
                'success' => false,
                'message' => 'Error dismissing password reset.',
            ], 500);
        }
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

    public function apiGetAdminUsers(): void
    {
        $role = strtoupper((string) $this->input('role', ''));

        if ($role === '') {
            $accounts = [];
            foreach (['VP', 'MANAGER', 'ACCOUNTANT'] as $r) {
                $accounts = array_merge($accounts, $this->userModel->where('role', $r));
            }
        } else {
            if (!in_array($role, ['VP', 'MANAGER', 'ACCOUNTANT'])) {
                $this->json([
                    'success' => false,
                    'message' => 'Invalid role filter.',
                ], 422);
                return;
            }
            $accounts = $this->userModel->where('role', $role);
        }

        usort($accounts, static function (array $a, array $b): int {
            return (int) ($a['id'] ?? 0) <=> (int) ($b['id'] ?? 0);
        });

        $this->json([
            'success' => true,
            'data' => $accounts,
        ]);
    }

    public function apiGetStudents(): void
    {
        $program = (string) $this->input('program', '');
        $status = (string) $this->input('status', '');
         $semesterId = (int) $this->input('semester_id', 0);

                $db = Database::connection();
                $sql = 'SELECT u.id, u.login_id, u.full_name, u.email, u.phone, u.is_active,
                                    sp.registration_number, p.program_code, p.program_name,
                                    s.id AS semester_id, s.semester_number, s.academic_year
                                FROM users u
                                LEFT JOIN student_profiles sp ON u.id = sp.user_id
                                LEFT JOIN programs p ON sp.program_id = p.id
                                LEFT JOIN semesters s ON s.id = sp.enrollment_semester_id
                                WHERE u.role = "STUDENT"';

        $params = [];

        if ($program !== '') {
            $sql .= ' AND p.program_code = :program';
            $params['program'] = $program;
        }

        if ($status !== '') {
            $sql .= ' AND u.is_active = :status';
            $params['status'] = (int) ($status === 'active' ? 1 : 0);
        }

        if ($semesterId > 0) {
            $sql .= ' AND s.id = :semester_id';
            $params['semester_id'] = $semesterId;
        }

        $sql .= ' ORDER BY COALESCE(sp.registration_number, u.login_id) ASC, u.id ASC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $students = $stmt->fetchAll() ?: [];

        foreach ($students as &$student) {
            $semesterNumber = (int) ($student['semester_number'] ?? 0);
            $academicYear = (string) ($student['academic_year'] ?? '');
            if ($semesterNumber > 0 && $academicYear !== '') {
                $student['semester_label'] = $academicYear . ' - S' . $semesterNumber;
            } elseif ($semesterNumber > 0) {
                $student['semester_label'] = 'S' . $semesterNumber;
            } else {
                $student['semester_label'] = 'N/A';
            }
        }
        unset($student);

        $this->json([
            'success' => true,
            'data' => array_values($students),
        ]);
    }

    public function apiGetTeachers(): void
    {
        $status = (string) $this->input('status', '');

        $db = Database::connection();
        $sql = 'SELECT u.id, u.login_id, u.full_name, u.email, u.phone, u.is_active,
                  GROUP_CONCAT(DISTINCT p.program_name ORDER BY p.id SEPARATOR ", ") AS program_names,
                       GROUP_CONCAT(DISTINCT p.id ORDER BY p.id SEPARATOR ",") AS program_ids
                FROM users u
                LEFT JOIN teacher_assignments ta ON ta.teacher_id = u.id
                LEFT JOIN subjects sub ON sub.id = ta.subject_id
                LEFT JOIN semesters s ON s.id = sub.semester_id
                LEFT JOIN programs p ON p.id = s.program_id
                WHERE u.role = "TEACHER"';

        $params = [];

        if ($status !== '') {
            $sql .= ' AND u.is_active = :status';
            $params['status'] = (int) ($status === 'active' ? 1 : 0);
        }

        $sql .= ' GROUP BY u.id, u.login_id, u.full_name, u.email, u.phone, u.is_active
              ORDER BY u.id ASC';

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $teachers = $stmt->fetchAll() ?: [];

        $this->json([
            'success' => true,
            'data' => array_values($teachers),
        ]);
    }

    public function apiGetPasswordResets(): void
    {
        $pendingResets = $this->resetModel->where('status', 'PENDING');

        // Filter for admin-level requests only
        $resets = [];
        foreach ($pendingResets as $reset) {
            $user = $this->userModel->find((int) $reset['requested_by']);
            if ($user && in_array($user['role'], ['VP', 'MANAGER', 'ACCOUNTANT'])) {
                $reset['user_name'] = $user['full_name'];
                $reset['user_email'] = $user['email'];
                $reset['user_role'] = $user['role'];
                $reset['is_admin_user'] = true;
                $resets[] = $reset;
            }
        }

        $this->json([
            'success' => true,
            'data' => $resets,
        ]);
    }

    public function apiGetEmailChangeRequests(): void
    {
        $pendingRequests = $this->emailChangeRequests->where('status', 'PENDING');
        $requests = [];

        foreach ($pendingRequests as $request) {
            $user = $this->userModel->find((int) ($request['user_id'] ?? 0));
            if (!$user || !in_array($user['role'], ['VP', 'MANAGER', 'ACCOUNTANT'], true)) {
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
    // HELPER METHODS
    // ============================================================================

    private function generateTemporaryPassword(): string
    {
        return bin2hex(random_bytes(6)); // 12-character hex password
    }

}
