<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\ActivationTokenModel;
use App\Models\EmailChangeRequestModel;
use App\Models\SystemConfigModel;
use App\Models\UserModel;
use App\Models\ProgramModel;
use App\Models\SemesterModel;
use App\Models\SubjectModel;
use App\Models\TeacherAssignmentModel;
use App\Models\PasswordResetRequestModel;
use App\Services\MailService;
use App\Services\SemesterService;
use DateTimeImmutable;
use DateTimeZone;
use PDO;

class VPController extends BaseController
{
    private UserModel $userModel;
    private ActivationTokenModel $activationTokens;
    private EmailChangeRequestModel $emailChangeRequests;
    private ProgramModel $programModel;
    private SemesterModel $semesterModel;
    private SubjectModel $subjectModel;
    private TeacherAssignmentModel $assignmentModel;
    private PasswordResetRequestModel $resetModel;
    private SemesterService $semesterService;
    private SystemConfigModel $configModel;
    private MailService $mailService;

    public function __construct()
    {
        $this->userModel = new UserModel();
        $this->activationTokens = new ActivationTokenModel();
        $this->emailChangeRequests = new EmailChangeRequestModel();
        $this->programModel = new ProgramModel();
        $this->semesterModel = new SemesterModel();
        $this->subjectModel = new SubjectModel();
        $this->assignmentModel = new TeacherAssignmentModel();
        $this->resetModel = new PasswordResetRequestModel();
        $this->semesterService = new SemesterService();
        $this->configModel = new SystemConfigModel();
        $this->mailService = new MailService();
    }

    // ============================================================================
    // DASHBOARD
    // ============================================================================

    public function showDashboard(): void
    {
        $stats = $this->getDashboardStats();
        $pendingResets = $this->getTeacherPendingResets();
        $academicSeries = $this->getAcademicSeries();
        $facultyPercent = $this->getFacultyUtilizationPercent();

        $this->view('vp.dashboard', [
            'title' => 'Vice Principal Dashboard',
            'user_name' => (string) ($_SESSION['user_name'] ?? 'Vice Principal'),
            'user_role' => 'VP',
            'stats' => $stats,
            'pending_resets' => $pendingResets,
            'academic_series' => $academicSeries,
            'faculty_percent' => $facultyPercent,
        ]);
    }

    private function getDashboardStats(): array
    {
        $programs = $this->programModel->where('is_active', 1);
        $totalPrograms = count($programs);

        $semesters = $this->semesterModel->where('is_current', 1);
        $currentSemesters = count($semesters);

        $teachers = $this->userModel->where('role', 'TEACHER');
        $totalTeachers = count($teachers);

        $resets = $this->resetModel->where('status', 'PENDING');
        $pendingCount = 0;
        // Count only teacher requests
        foreach ($resets as $reset) {
            $user = $this->userModel->find($reset['requested_by'] ?? 0);
            if ($user && $user['role'] === 'TEACHER') {
                $pendingCount++;
            }
        }

        return [
            'total_programs' => $totalPrograms,
            'current_semesters' => $currentSemesters,
            'total_teachers' => $totalTeachers,
            'pending_resets' => $pendingCount,
        ];
    }

    private function getTeacherPendingResets(): array
    {
        $resets = $this->resetModel->where('status', 'PENDING');
        $result = [];

        foreach ($resets as $reset) {
            $user = $this->userModel->find($reset['requested_by'] ?? 0);
            if ($user && $user['role'] === 'TEACHER') {
                $result[] = [
                    'id' => $reset['id'],
                    'requested_by' => $reset['requested_by'],
                    'full_name' => $user['full_name'],
                    'login_id' => $user['login_id'],
                    'email' => $user['email'],
                    'created_at' => $reset['created_at'],
                ];
            }
        }

        return $result;
    }

    /** @return array<int, int> */
    private function getAcademicSeries(): array
    {
        $semesters = $this->semesterModel->all('academic_year', 'DESC');
        usort($semesters, static function (array $a, array $b): int {
            $yearCompare = strcmp((string) ($b['academic_year'] ?? ''), (string) ($a['academic_year'] ?? ''));
            if ($yearCompare !== 0) {
                return $yearCompare;
            }
            return (int) ($b['semester_number'] ?? 0) <=> (int) ($a['semester_number'] ?? 0);
        });

        $subjects = $this->subjectModel->all();
        $subjectsBySemester = [];
        foreach ($subjects as $subject) {
            $semesterId = (int) ($subject['semester_id'] ?? 0);
            if ($semesterId === 0) {
                continue;
            }
            $subjectsBySemester[$semesterId] = ($subjectsBySemester[$semesterId] ?? 0) + 1;
        }

        $seriesCounts = [];
        foreach (array_slice($semesters, 0, 7) as $semester) {
            $semesterId = (int) ($semester['id'] ?? 0);
            $seriesCounts[] = $subjectsBySemester[$semesterId] ?? 0;
        }

        $maxCount = max($seriesCounts ?: [1]);
        $series = [];
        foreach ($seriesCounts as $count) {
            $series[] = $maxCount > 0 ? (int) round(($count / $maxCount) * 100) : 0;
        }

        while (count($series) < 7) {
            $series[] = 0;
        }

        return $series;
    }

    private function getFacultyUtilizationPercent(): int
    {
        $subjects = $this->subjectModel->all();
        $totalSubjects = count($subjects);
        if ($totalSubjects === 0) {
            return 0;
        }

        $assignments = $this->assignmentModel->all();
        $assignedSubjectIds = [];
        foreach ($assignments as $assignment) {
            $subjectId = (int) ($assignment['subject_id'] ?? 0);
            if ($subjectId > 0) {
                $assignedSubjectIds[$subjectId] = true;
            }
        }

        $assignedCount = count($assignedSubjectIds);

        return (int) round(($assignedCount / $totalSubjects) * 100);
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

        $this->view('vp.profile', [
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
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'VP') {
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
    // PROGRAMS MANAGEMENT
    // ============================================================================

    public function showPrograms(): void
    {
        $programs = $this->programModel->all('program_name', 'ASC');

        $this->view('vp.programs', [
            'title' => 'Programs',
            'programs' => $programs,
        ]);
    }

    // ============================================================================
    // SEMESTERS MANAGEMENT
    // ============================================================================

    public function showSemesters(): void
    {
        $programs = $this->programModel->where('is_active', 1);
        $semesterRows = $this->semesterModel->all('academic_year', 'DESC');

        // Enrich semester data with program names and computed term using helper function
        $semesters = [];
        foreach ($semesterRows as $sem) {
            $program = $this->programModel->find($sem['program_id'] ?? 0);
            
            // Use computed helper to add 'term' field (Odd/Even) per spec Part 4.4
            $enriched = [
                'id' => $sem['id'],
                'program_id' => $sem['program_id'],
                'program_name' => $program['program_name'] ?? 'N/A',
                'semester_number' => $sem['semester_number'],
                'academic_year' => $sem['academic_year'],
                'is_current' => $sem['is_current'],
                'fee_amount' => $sem['fee_amount'],
            ];
            
            // Add computed term using helper
            $enriched = \App\Helpers\format_semester_response($enriched);
            $semesters[] = $enriched;
        }

        $this->view('vp.semesters', [
            'title' => 'Semesters',
            'programs' => $programs,
            'semesters' => $semesters,
        ]);
    }

    // ============================================================================
    // SUBJECTS MANAGEMENT
    // ============================================================================

    public function showSubjects(): void
    {
        $db = Database::connection();
        $semesterRows = $db->query('
            SELECT sem.*, p.program_name
            FROM semesters sem
            JOIN programs p ON sem.program_id = p.id
            ORDER BY sem.academic_year DESC, sem.semester_number DESC
        ')->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $subjects = $db->query('
            SELECT
                su.id,
                su.subject_name,
                su.subject_code,
                su.semester_id,
                sem.semester_number,
                sem.academic_year,
                p.program_name,
                u.full_name as teacher_name,
                u.login_id as teacher_login
            FROM subjects su
            JOIN semesters sem ON su.semester_id = sem.id
            JOIN programs p ON sem.program_id = p.id
            LEFT JOIN teacher_assignments ta ON ta.subject_id = su.id
            LEFT JOIN users u ON ta.teacher_id = u.id
            ORDER BY p.program_name, sem.semester_number, su.subject_name
        ')->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $subjectTypes = [];
        $departments = [];
        $semesterNumbers = [];

        foreach ($subjects as &$subject) {
            $name = strtolower((string) ($subject['subject_name'] ?? ''));
            if (str_contains($name, 'lab') || str_contains($name, 'practical')) {
                $type = 'Lab';
            } elseif (str_contains($name, 'seminar')) {
                $type = 'Seminar';
            } elseif (str_contains($name, 'project')) {
                $type = 'Project';
            } else {
                $type = 'Theory';
            }

            $subject['subject_type'] = $type;
            $subjectTypes[$type] = true;

            if (!empty($subject['program_name'])) {
                $departments[(string) $subject['program_name']] = true;
            }

            if (!empty($subject['semester_number'])) {
                $semesterNumbers[(string) $subject['semester_number']] = true;
            }
        }
        unset($subject);

        $this->view('vp.subjects', [
            'title' => 'Subjects',
            'semesters' => $semesterRows,
            'subjects' => $subjects,
            'subject_types' => array_keys($subjectTypes),
            'departments' => array_keys($departments),
            'semester_numbers' => array_keys($semesterNumbers),
        ]);
    }

    // ============================================================================
    // TEACHERS MANAGEMENT
    // ============================================================================

    public function showTeachers(): void
    {
        $teachers = $this->userModel->where('role', 'TEACHER');

        $this->view('vp.teachers', [
            'title' => 'Teachers',
            'teachers' => $teachers,
        ]);
    }

    // ============================================================================
    // ASSIGNMENTS MANAGEMENT
    // ============================================================================

    public function showAssignments(): void
    {
        $db = Database::connection();

        // Get all assignments with related data
        $stmt = $db->query('
            SELECT 
                ta.id,
                ta.teacher_id,
                ta.subject_id,
                u.full_name as teacher_name,
                u.login_id as teacher_login,
                su.subject_name,
                su.subject_code,
                sem.semester_number,
                sem.academic_year,
                p.program_name,
                p.program_code
            FROM teacher_assignments ta
            JOIN users u ON ta.teacher_id = u.id
            JOIN subjects su ON ta.subject_id = su.id
            JOIN semesters sem ON su.semester_id = sem.id
            JOIN programs p ON sem.program_id = p.id
            ORDER BY p.program_name, sem.semester_number, su.subject_name
        ');

        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Get all teachers and subjects for dropdown
        $teachers = $this->userModel->where('role', 'TEACHER');
        $subjects = $this->subjectModel->all('subject_name', 'ASC');

        $this->view('vp.assignments', [
            'title' => 'Subject Assignments',
            'assignments' => $assignments,
            'teachers' => $teachers,
            'subjects' => $subjects,
        ]);
    }

    // ============================================================================
    // TIMETABLE MANAGEMENT
    // ============================================================================

    public function showTimetable(): void
    {
        $db = Database::connection();

        $workingDayCodes = $this->configModel->getWorkingDayCodes();
        $dayStartTime = $this->configModel->getValue('DAY_START_TIME');
        $dayEndTime = $this->configModel->getValue('DAY_END_TIME');

        $workingDayList = [];
        foreach ($workingDayCodes as $code) {
            $label = match ($code) {
                'MON' => 'Monday',
                'TUE' => 'Tuesday',
                'WED' => 'Wednesday',
                'THU' => 'Thursday',
                'FRI' => 'Friday',
                'SAT' => 'Saturday',
                default => '',
            };
            if ($label !== '') {
                $workingDayList[] = ['label' => $label, 'code' => $code];
            }
        }

        // Get all timetable entries with related data
        $stmt = $db->query('
            SELECT 
                t.id,
                t.teacher_assignment_id,
                t.day,
                t.start_time,
                t.end_time,
                u.full_name as teacher_name,
                u.login_id as teacher_login,
                su.subject_name,
                su.subject_code,
                sem.semester_number,
                sem.academic_year,
                p.program_name
            FROM timetables t
            JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
            JOIN users u ON ta.teacher_id = u.id
            JOIN subjects su ON ta.subject_id = su.id
            JOIN semesters sem ON su.semester_id = sem.id
            JOIN programs p ON sem.program_id = p.id
            ORDER BY t.day, t.start_time, u.full_name
        ');

        $timetable = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        // Get all teacher assignments for dropdown
        $stmt = $db->query('
            SELECT 
                ta.id,
                u.full_name as teacher_name,
                su.subject_name,
                su.subject_code
            FROM teacher_assignments ta
            JOIN users u ON ta.teacher_id = u.id
            JOIN subjects su ON ta.subject_id = su.id
            ORDER BY u.full_name, su.subject_name
        ');
        $assignments = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

        $this->view('vp.timetable', [
            'title' => 'Timetable',
            'timetable' => $timetable,
            'assignments' => $assignments,
            'working_days' => $workingDayList,
            'day_start_time' => $dayStartTime,
            'day_end_time' => $dayEndTime,
        ]);
    }

    // ============================================================================
    // PASSWORD REQUESTS
    // ============================================================================

    public function showPasswordRequests(): void
    {
        $pendingResets = $this->getTeacherPendingResets();

        $this->view('vp.password-requests', [
            'title' => 'Teacher Password Reset Requests',
            'pending_resets' => $pendingResets,
        ]);
    }

    public function approvePasswordReset(int $id): void
    {
        $reset = $this->resetModel->find($id);

        if (!$reset) {
            $this->json(['success' => false, 'message' => 'Request not found'], 404);
            return;
        }

        if ($reset['status'] !== 'PENDING') {
            $this->json(['success' => false, 'message' => 'This request has already been processed'], 409);
            return;
        }

        $user = $this->userModel->find($reset['requested_by'] ?? 0);

        if (!$user || $user['role'] !== 'TEACHER') {
            $this->json(['success' => false, 'message' => 'VP can only approve teacher password resets'], 403);
            return;
        }

        // Spec: temp password = bin2hex(random_bytes(5)) — 10 hex chars
        $tempPassword = bin2hex(random_bytes(5));
        $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        // Fix: correct column name is password_hash, not password
        $this->userModel->updateById((int)$user['id'], [
            'password_hash'        => $passwordHash,
            'must_change_password' => 1,
        ]);

        $this->resetModel->updateById($id, [
            'status'      => 'APPROVED',
            'resolved_by' => (int) ($_SESSION['user_id'] ?? 0),
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);

        $this->json(['success' => true, 'message' => 'Password reset approved. Temporary password: ' . $tempPassword, 'data' => ['temp_password' => $tempPassword]]);
    }

    // NOTE: Spec only defines PENDING, APPROVED, EXPIRED statuses.
    // There is no REJECTED status. This endpoint is kept for UI compatibility
    // but sets status to EXPIRED (the closest spec-valid state for a denied request).
    public function rejectPasswordReset(int $id): void
    {
        $reset = $this->resetModel->find($id);

        if (!$reset) {
            $this->json(['success' => false, 'message' => 'Request not found'], 404);
            return;
        }

        if ($reset['status'] !== 'PENDING') {
            $this->json(['success' => false, 'message' => 'This request has already been processed'], 409);
            return;
        }

        $user = $this->userModel->find($reset['requested_by'] ?? 0);

        if (!$user || $user['role'] !== 'TEACHER') {
            $this->json(['success' => false, 'message' => 'VP can only manage teacher password resets'], 403);
            return;
        }

        // Spec: status ENUM only has PENDING, APPROVED, EXPIRED
        $this->resetModel->updateById($id, [
            'status'      => 'EXPIRED',
            'resolved_by' => (int) ($_SESSION['user_id'] ?? 0),
            'resolved_at' => date('Y-m-d H:i:s'),
        ]);

        $this->json(['success' => true, 'message' => 'Password reset request dismissed']);
    }

    // ============================================================================
    // CREATION/MODIFICATION API ENDPOINTS
    // ============================================================================

    public function createProgram(): void
    {
        $data = json_decode((string) file_get_contents('php://input'), true);

        if (empty($data['program_name']) || empty($data['program_code']) || empty($data['duration_semesters'])) {
            $this->json(['success' => false, 'message' => 'Missing required fields: program_name, program_code, duration_semesters'], 400);
            return;
        }

        // Spec: program_code must be uppercase, regex /^[A-Z]{2,10}$/
        $code = strtoupper(trim((string) $data['program_code']));
        if (!preg_match('/^[A-Z]{2,10}$/', $code)) {
            $this->json(['success' => false, 'message' => 'Program code must be 2-10 uppercase letters only'], 400);
            return;
        }

        // Spec: duration_semesters >= 1
        $duration = (int) $data['duration_semesters'];
        if ($duration < 1) {
            $this->json(['success' => false, 'message' => 'Duration must be at least 1 semester'], 400);
            return;
        }

        // Check for duplicate code
        $existing = $this->programModel->firstWhere('program_code', $code);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Program code already exists'], 409);
            return;
        }

        // Check for duplicate name
        $existingName = $this->programModel->firstWhere('program_name', (string) $data['program_name']);
        if ($existingName) {
            $this->json(['success' => false, 'message' => 'Program name already exists'], 409);
            return;
        }

        $newId = $this->programModel->create([
            'program_name'       => (string) $data['program_name'],
            'program_code'       => $code,
            'duration_semesters' => $duration,
            'is_active'          => 1,
            'created_by'         => (int) ($_SESSION['user_id'] ?? 0),
        ]);

        if ($newId) {
            $this->json(['success' => true, 'message' => 'Program created successfully', 'data' => ['id' => $newId]]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create program'], 500);
        }
    }

    public function createSemester(): void
    {
        $data = json_decode((string) file_get_contents('php://input'), true);

        if (empty($data['program_id']) || empty($data['semester_number']) || empty($data['academic_year'])) {
            $this->json(['success' => false, 'message' => 'Missing required fields: program_id, semester_number, academic_year'], 400);
            return;
        }

        $programId      = (int) $data['program_id'];
        $semesterNumber = (int) $data['semester_number'];
        $academicYear   = (string) $data['academic_year'];

        // Spec: validate academic_year format YYYY-YYYY and year2 == year1 + 1
        if (!preg_match('/^(\d{4})-(\d{4})$/', $academicYear, $m) || (int)$m[2] !== (int)$m[1] + 1) {
            $this->json(['success' => false, 'message' => 'academic_year must be in YYYY-YYYY format where second year = first + 1'], 400);
            return;
        }

        // Spec: validate semester_number >= 1 and <= program.duration_semesters
        $program = $this->programModel->find($programId);
        if (!$program) {
            $this->json(['success' => false, 'message' => 'Program not found'], 404);
            return;
        }
        if ($semesterNumber < 1 || $semesterNumber > (int) $program['duration_semesters']) {
            $this->json(['success' => false, 'message' => 'Semester number must be between 1 and ' . $program['duration_semesters']], 400);
            return;
        }

        // Spec: CRITICAL - is_current NEVER set on INSERT. Only via /activate endpoint.
        // Spec: fee_amount NULL by default (not configured yet)
        $newId = $this->semesterModel->create([
            'program_id'      => $programId,
            'semester_number' => $semesterNumber,
            'academic_year'   => $academicYear,
            'is_current'      => 0,
            'fee_amount'      => null,
        ]);

        if ($newId) {
            $this->json(['success' => true, 'message' => 'Semester created successfully', 'data' => ['id' => $newId]]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create semester'], 500);
        }
    }

    public function activateSemester(int $id): void
    {
        $semester = $this->semesterModel->find($id);

        if (!$semester) {
            $this->json(['success' => false, 'message' => 'Semester not found'], 404);
            return;
        }

        try {
            // Use SemesterService to handle activation with transaction and auto-fee creation
            // Per spec Part 3 Table 4 and Table 9 FLOW section 2
            $this->semesterService->activate($id);

            $this->json(['success' => true, 'message' => 'Semester activated successfully']);
        } catch (\Exception $e) {
            $this->json(['success' => false, 'message' => 'Failed to activate semester: ' . $e->getMessage()], 500);
        }
    }

    public function createSubject(): void
    {
        $data = json_decode((string) file_get_contents('php://input'), true);

        if (empty($data['subject_name']) || empty($data['subject_code']) || empty($data['semester_id'])) {
            $this->json(['success' => false, 'message' => 'Missing required fields: subject_name, subject_code, semester_id'], 400);
            return;
        }

        $semesterId  = (int) $data['semester_id'];
        $subjectCode = (string) $data['subject_code'];

        // Spec: UNIQUE(semester_id, subject_code)
        $db = Database::connection();
        $chk = $db->prepare('SELECT id FROM subjects WHERE semester_id = :sid AND subject_code = :code LIMIT 1');
        $chk->execute(['sid' => $semesterId, 'code' => $subjectCode]);
        if ($chk->fetch()) {
            $this->json(['success' => false, 'message' => 'Subject code already exists for this semester'], 409);
            return;
        }

        $newId = $this->subjectModel->create([
            'subject_name' => (string) $data['subject_name'],
            'subject_code' => $subjectCode,
            'semester_id'  => $semesterId,
        ]);

        if ($newId) {
            $this->json(['success' => true, 'message' => 'Subject created successfully', 'data' => ['id' => $newId]]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create subject'], 500);
        }
    }

    public function createTeacher(): void
    {
        $data = json_decode((string) file_get_contents('php://input'), true);

        if (empty($data['login_id']) || empty($data['full_name'])) {
            $this->json(['success' => false, 'message' => 'Missing required fields: login_id, full_name'], 400);
            return;
        }

        $loginId = (string) $data['login_id'];
        $email = (string) ($data['email'] ?? '');

        if ($email === '') {
            $this->json(['success' => false, 'message' => 'Email is required.'], 422);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->json(['success' => false, 'message' => 'Please enter a valid email address.'], 422);
            return;
        }

        // Spec: login_id globally unique
        $existing = $this->userModel->firstWhere('login_id', $loginId);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Staff ID (login_id) already exists'], 409);
            return;
        }

        if ($this->userModel->firstWhere('email', $email)) {
            $this->json(['success' => false, 'message' => 'Email already exists.'], 409);
            return;
        }

        // Spec: temp password = bin2hex(random_bytes(5)) — 10 hex chars
        $tempPassword = bin2hex(random_bytes(5));
        $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT, ['cost' => 12]);

        try {
            $createdBy = (int) ($_SESSION['user_id'] ?? 0);
            $newId = $this->userModel->create([
                'login_id'             => $loginId,
                'full_name'            => (string) $data['full_name'],
                'email'                => $email,
                'phone'                => isset($data['phone']) ? (string) $data['phone'] : null,
                'password_hash'        => $passwordHash,
                'role'                 => 'TEACHER',
                'is_active'            => 1,
                'must_change_password' => 1,
                'created_by'           => $createdBy,
            ]);

            $token = bin2hex(random_bytes(32));
            $tokenHash = hash('sha256', $token);
            $expiresAt = (new DateTimeImmutable('now', new DateTimeZone('UTC')))
                ->modify('+24 hours')
                ->format('Y-m-d H:i:s');

            $tokenId = $this->activationTokens->create([
                'user_id' => $newId,
                'token_hash' => $tokenHash,
                'expires_at' => $expiresAt,
                'created_by' => $createdBy,
                'created_at' => gmdate('Y-m-d H:i:s'),
            ]);

            $activationLink = url('activate/' . $token);
            $subject = 'Activate your EduHub account';
            $htmlBody = sprintf(
                '<p>Hello %s,</p><p>Your TEACHER account has been created. Please activate your account using the link below (valid for 24 hours):</p><p><a href="%s">Activate Account</a></p><p>Login ID: <strong>%s</strong></p><p>If you did not expect this email, please contact support.</p>',
                e((string) $data['full_name']),
                e($activationLink),
                e($loginId)
            );
            $textBody = "Hello {$data['full_name']},\n\nYour TEACHER account has been created. Activate your account using the link below (valid for 24 hours):\n{$activationLink}\n\nLogin ID: {$loginId}\n\nIf you did not expect this email, please contact support.";

            $this->mailService->sendMail($email, $subject, $htmlBody, $textBody);

            $this->json([
                'success' => true,
                'message' => 'Teacher created. Activation email sent.',
                'data' => ['id' => $newId],
            ], 201);
        } catch (\Exception $e) {
            if (isset($tokenId) && (int) $tokenId > 0) {
                $this->activationTokens->deleteById((int) $tokenId);
            }
            if (isset($newId) && (int) $newId > 0) {
                $this->userModel->deleteById((int) $newId);
            }
            $this->json(['success' => false, 'message' => 'Failed to create teacher: ' . $e->getMessage()], 500);
        }
    }

    public function createAssignment(): void
    {
        $data = json_decode((string) file_get_contents('php://input'), true);

        if (empty($data['teacher_id']) || empty($data['subject_id'])) {
            $this->json(['success' => false, 'message' => 'Missing required fields: teacher_id, subject_id'], 400);
            return;
        }

        $teacherId = (int) $data['teacher_id'];
        $subjectId = (int) $data['subject_id'];

        // Spec: validate teacher role
        $teacher = $this->userModel->find($teacherId);
        if (!$teacher || $teacher['role'] !== 'TEACHER') {
            $this->json(['success' => false, 'message' => 'User is not a teacher'], 400);
            return;
        }

        $db = Database::connection();

        // Spec: UNIQUE on subject_id — one teacher per subject
        $chk1 = $db->prepare('SELECT id FROM teacher_assignments WHERE subject_id = :sid LIMIT 1');
        $chk1->execute(['sid' => $subjectId]);
        if ($chk1->fetch()) {
            $this->json(['success' => false, 'message' => 'This subject is already assigned to a teacher'], 409);
            return;
        }

        // Spec: PHP pre-INSERT check — teacher cannot have two assignments in same semester
        $chk2 = $db->prepare(
            'SELECT ta.id FROM teacher_assignments ta
             JOIN subjects s ON ta.subject_id = s.id
             WHERE ta.teacher_id = :tid
               AND s.semester_id = (SELECT semester_id FROM subjects WHERE id = :sid)
             LIMIT 1'
        );
        $chk2->execute(['tid' => $teacherId, 'sid' => $subjectId]);
        if ($chk2->fetch()) {
            $this->json(['success' => false, 'message' => 'Teacher already has an assignment in this semester'], 409);
            return;
        }

        $stmt = $db->prepare('INSERT INTO teacher_assignments (teacher_id, subject_id) VALUES (:teacher_id, :subject_id)');
        $result = $stmt->execute(['teacher_id' => $teacherId, 'subject_id' => $subjectId]);
        $newId  = (int) $db->lastInsertId();

        if ($result && $newId) {
            $this->json(['success' => true, 'message' => 'Assignment created successfully', 'data' => ['id' => $newId]]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create assignment'], 500);
        }
    }

    public function deleteAssignment(int $id): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('DELETE FROM teacher_assignments WHERE id = :id');
        $result = $stmt->execute(['id' => $id]);

        if ($result) {
            $this->json(['success' => true, 'message' => 'Assignment deleted successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete assignment'], 500);
        }
    }

    public function createTimetable(): void
    {
        $data = json_decode((string) file_get_contents('php://input'), true);

        if (empty($data['teacher_assignment_id']) || empty($data['day']) || empty($data['start_time']) || empty($data['end_time'])) {
            $this->json(['success' => false, 'message' => 'Missing required fields: teacher_assignment_id, day, start_time, end_time'], 400);
            return;
        }

        $assignmentId = (int) $data['teacher_assignment_id'];
        $day          = strtoupper((string) $data['day']);
        $startTime    = (string) $data['start_time'];
        $endTime      = (string) $data['end_time'];

        $validDays = $this->configModel->getWorkingDayCodes();
        $dayStartTime = $this->configModel->getValue('DAY_START_TIME');
        $dayEndTime = $this->configModel->getValue('DAY_END_TIME');

        if ($validDays === [] || $dayStartTime === null || $dayEndTime === null) {
            $this->json(['success' => false, 'message' => 'System configuration is incomplete. Please configure working days and day hours.'], 500);
            return;
        }

        if (!in_array($day, $validDays, true)) {
            $this->json(['success' => false, 'message' => 'Invalid day. Must be one of: ' . implode(',', $validDays)], 400);
            return;
        }

        $toMinutes = static function (string $value): ?int {
            if (!preg_match('/^([01]\d|2[0-3]):([0-5]\d)$/', $value, $match)) {
                return null;
            }
            return ((int) $match[1]) * 60 + (int) $match[2];
        };

        $startMinutes = $toMinutes($startTime);
        $endMinutes = $toMinutes($endTime);
        $minStart = $toMinutes($dayStartTime);
        $maxEnd = $toMinutes($dayEndTime);

        if ($startMinutes === null || $endMinutes === null) {
            $this->json(['success' => false, 'message' => 'Start and end time must use HH:MM (24-hour) format.'], 400);
            return;
        }

        // Validate end_time > start_time
        if ($endMinutes <= $startMinutes) {
            $this->json(['success' => false, 'message' => 'end_time must be after start_time'], 400);
            return;
        }

        if ($minStart !== null && $startMinutes < $minStart) {
            $this->json(['success' => false, 'message' => 'Start time must be within configured day hours.'], 400);
            return;
        }

        if ($maxEnd !== null && $endMinutes > $maxEnd) {
            $this->json(['success' => false, 'message' => 'End time must be within configured day hours.'], 400);
            return;
        }

        // Verify assignment exists
        $db = Database::connection();
        $aChk = $db->prepare('SELECT ta.teacher_id, s.semester_id FROM teacher_assignments ta JOIN subjects s ON ta.subject_id = s.id WHERE ta.id = :id LIMIT 1');
        $aChk->execute(['id' => $assignmentId]);
        $assignment = $aChk->fetch(PDO::FETCH_ASSOC);
        if (!$assignment) {
            $this->json(['success' => false, 'message' => 'Teacher assignment not found'], 404);
            return;
        }

        $semesterId = (int) $assignment['semester_id'];
        $teacherId  = (int) $assignment['teacher_id'];

        // Spec: Semester clash check — no two slots in same semester overlap on same day
        $clashSem = $db->prepare(
            'SELECT t.id FROM timetables t
             JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
             JOIN subjects s ON ta.subject_id = s.id
             WHERE s.semester_id = :semester_id
               AND t.day = :day
               AND t.start_time < :end_time
               AND t.end_time > :start_time
             LIMIT 1'
        );
        $clashSem->execute(['semester_id' => $semesterId, 'day' => $day, 'end_time' => $endTime, 'start_time' => $startTime]);
        if ($clashSem->fetch()) {
            $this->json(['success' => false, 'message' => 'A class for this semester already exists in this time slot'], 409);
            return;
        }

        // Spec: Teacher clash check — teacher not double-booked
        $clashTeacher = $db->prepare(
            'SELECT t.id FROM timetables t
             JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
             WHERE ta.teacher_id = :teacher_id
               AND ta.id != :assignment_id
               AND t.day = :day
               AND t.start_time < :end_time
               AND t.end_time > :start_time
             LIMIT 1'
        );
        $clashTeacher->execute(['teacher_id' => $teacherId, 'assignment_id' => $assignmentId, 'day' => $day, 'end_time' => $endTime, 'start_time' => $startTime]);
        if ($clashTeacher->fetch()) {
            $this->json(['success' => false, 'message' => 'Teacher is already scheduled at this time'], 409);
            return;
        }

        $stmt = $db->prepare('INSERT INTO timetables (teacher_assignment_id, day, start_time, end_time) VALUES (:ta, :day, :start, :end)');
        $result = $stmt->execute(['ta' => $assignmentId, 'day' => $day, 'start' => $startTime, 'end' => $endTime]);
        $newId  = (int) $db->lastInsertId();

        if ($result && $newId) {
            $this->json(['success' => true, 'message' => 'Timetable entry created successfully', 'data' => ['id' => $newId]]);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to create timetable entry'], 500);
        }
    }

    public function deleteTimetable(int $id): void
    {
        $db = Database::connection();
        $stmt = $db->prepare('DELETE FROM timetables WHERE id = :id');
        $result = $stmt->execute(['id' => $id]);

        if ($result) {
            $this->json(['success' => true, 'message' => 'Timetable entry deleted successfully']);
        } else {
            $this->json(['success' => false, 'message' => 'Failed to delete timetable entry'], 500);
        }
    }

    // ============================================================================
    // HELPER METHODS
    // ============================================================================

    private function generateTemporaryPassword(): string
    {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*';
        $password = '';
        for ($i = 0; $i < 12; $i++) {
            $password .= $chars[random_int(0, strlen($chars) - 1)];
        }
        return $password;
    }

}
