<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Models\StudentProfileModel;
use App\Models\StudentFeeModel;
use App\Models\TimetableModel;
use App\Models\AttendanceModel;
use App\Models\SemesterModel;
use App\Models\SubjectModel;
use App\Models\TeacherAssignmentModel;
use App\Models\UserModel;
use App\Models\PasswordResetRequestModel;
use App\Models\ProgramModel;
use App\Models\SystemConfigModel;
use App\Models\EmailChangeRequestModel;
use App\Models\EmailChangeVerificationModel;
use App\Services\MailService;
use DateTimeImmutable;
use DateTimeZone;

class StudentController extends BaseController
{
    private StudentProfileModel $studentProfile;
    private StudentFeeModel $studentFee;
    private TimetableModel $timetable;
    private AttendanceModel $attendance;
    private SemesterModel $semester;
    private SubjectModel $subject;
    private TeacherAssignmentModel $assignment;
    private UserModel $user;
    private PasswordResetRequestModel $resetRequest;
    private ProgramModel $program;
    private EmailChangeRequestModel $emailChangeRequests;
    private EmailChangeVerificationModel $emailChangeVerifications;
    private MailService $mailService;

    public function __construct()
    {
        $this->studentProfile = new StudentProfileModel();
        $this->studentFee = new StudentFeeModel();
        $this->timetable = new TimetableModel();
        $this->attendance = new AttendanceModel();
        $this->semester = new SemesterModel();
        $this->subject = new SubjectModel();
        $this->assignment = new TeacherAssignmentModel();
        $this->user = new UserModel();
        $this->resetRequest = new PasswordResetRequestModel();
        $this->program = new ProgramModel();
        $this->emailChangeRequests = new EmailChangeRequestModel();
        $this->emailChangeVerifications = new EmailChangeVerificationModel();
        $this->mailService = new MailService();
    }

    // ============================================================================
    // DASHBOARD
    // ============================================================================

    public function showDashboard(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->redirect('/login');
            return;
        }

        $studentProfile = $this->studentProfile->findByUserId($userId);

        if (!$studentProfile) {
            $stats = [
                'current_semester'   => 'N/A',
                'attendance_percent' => 0,
                'fee_status'         => 'N/A',
                'pending_amount'     => '0.00',
            ];

            $this->view('student.dashboard', [
                'title'            => 'Student Dashboard',
                'user_name'        => (string) ($_SESSION['user_name'] ?? 'Student'),
                'stats'            => $stats,
                'current_semester' => null,
                'profile_not_found'=> true,
            ]);
            return;
        }

        // Spec: student_id in attendance/fees references users.id, NOT student_profiles.id
        $studentUserId = $userId;

        // Get current semester for THIS student's program
        $currentSemesters = $this->semester->where('is_current', 1);
        // Filter to student's program
        $currentSemester = null;
        foreach ($currentSemesters as $sem) {
            if ((int) $sem['program_id'] === (int) $studentProfile['program_id']) {
                $currentSemester = $sem;
                break;
            }
        }
        if (!$currentSemester && !empty($currentSemesters)) {
            $currentSemester = $currentSemesters[0];
        }

        $stats = $this->getDashboardStats($studentUserId, $currentSemester);

        $this->view('student.dashboard', [
            'title'            => 'Student Dashboard',
            'user_name'        => (string) ($_SESSION['user_name'] ?? 'Student'),
            'stats'            => $stats,
            'current_semester' => $currentSemester,
            'profile_not_found'=> false,
        ]);
    }

    private function getDashboardStats(int $studentUserId, ?array $currentSemester): array
    {
        $semesterDisplay  = 'N/A';
        $currentSemesterId = null;
        $semesterFeeAmount = '0.00';
        if ($currentSemester) {
            $semesterDisplay   = 'Semester ' . $currentSemester['semester_number'];
            $currentSemesterId = (int) $currentSemester['id'];
            $semesterFeeAmount = (string) ($currentSemester['fee_amount'] ?? '0.00');
        }

        $attendancePercent = 0;
        if ($currentSemesterId) {
            $attendancePercent = $this->getAttendancePercentage($studentUserId, $currentSemesterId);
        }

        $attendanceSeries = $this->getAttendanceSeries($studentUserId);

        $feeStatus    = 'N/A';
        $pendingAmount = '0.00';
        if ($currentSemesterId) {
            $feeData      = $this->getFeeStatus($studentUserId, $currentSemesterId, $semesterFeeAmount);
            $feeStatus    = $feeData['status'];
            $pendingAmount = $feeData['pending'];
        }

        return [
            'current_semester'   => $semesterDisplay,
            'attendance_percent' => $attendancePercent,
            'attendance_series'  => $attendanceSeries,
            'fee_status'         => $feeStatus,
            'pending_amount'     => $pendingAmount,
        ];
    }

    private function getCurrentSemesterForProgram(int $programId): ?array
    {
        $currentSemesters = $this->semester->where('is_current', 1);
        $currentSemester = null;

        foreach ($currentSemesters as $sem) {
            if ((int) ($sem['program_id'] ?? 0) === $programId) {
                $currentSemester = $sem;
                break;
            }
        }

        if (!$currentSemester && !empty($currentSemesters)) {
            $currentSemester = $currentSemesters[0];
        }

        return $currentSemester;
    }

    private function getAttendancePercentage(int $studentId, int $semesterId): int
    {
        $records = $this->attendance->where('student_id', $studentId);
        $studentAttendance = [];

        foreach ($records as $record) {
            if ((int) ($record['student_id'] ?? 0) === $studentId) {
                $studentAttendance[] = $record;
            }
        }

        if ($studentAttendance === []) {
            return 0;
        }

        $total = 0;
        $present = 0;

        foreach ($studentAttendance as $record) {
            $timetableSlotId = (int) ($record['timetable_slot_id'] ?? 0);
            $timetableSlot = $this->timetable->find($timetableSlotId);
            if (!$timetableSlot) {
                continue;
            }

            $assignmentId = (int) ($timetableSlot['teacher_assignment_id'] ?? 0);
            $assignment = $this->assignment->find($assignmentId);
            if (!$assignment) {
                continue;
            }

            $subjectId = (int) ($assignment['subject_id'] ?? 0);
            $subject = $this->subject->find($subjectId);
            if (!$subject) {
                continue;
            }

            if ((int) ($subject['semester_id'] ?? 0) !== $semesterId) {
                continue;
            }

            $total++;
            if (strtoupper((string) ($record['status'] ?? '')) === 'PRESENT') {
                $present++;
            }
        }

        if ($total === 0) {
            return 0;
        }

        return (int) round(($present / $total) * 100);
    }

    private function getAttendanceSeries(int $studentId): array
    {
        $records = $this->attendance->where('student_id', $studentId);
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

    private function getFeeStatus(int $studentUserId, int $semesterId, string $semesterFeeAmount): array
    {
        $feeRecords = $this->studentFee->forStudent($studentUserId);
        $studentSemesterFee = null;

        foreach ($feeRecords as $fee) {
            if ((int) $fee['semester_id'] === $semesterId) {
                $studentSemesterFee = $fee;
                break;
            }
        }

        if (!$studentSemesterFee) {
            return [
                'status'  => 'PENDING',
                'pending' => $semesterFeeAmount,
            ];
        }

        // Spec: BCMath for all fee arithmetic
        $amountPaid  = (string) ($studentSemesterFee['amount_paid'] ?? '0.00');
        $totalFee    = (string) $semesterFeeAmount;
        $pending     = bcsub($totalFee, $amountPaid, 2);
        if (bccomp($pending, '0', 2) < 0) {
            $pending = '0.00';
        }
        $status = bccomp($pending, '0', 2) <= 0 ? 'PAID' : 'PENDING';

        return ['status' => $status, 'pending' => $pending];
    }

    // ============================================================================
    // TIMETABLE
    // ============================================================================

    public function showSubjects(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->redirect('/login');
            return;
        }

        $studentProfile = $this->studentProfile->findByUserId($userId);
        if (!$studentProfile) {
            $this->view('student.subjects', [
                'title' => 'Subjects',
                'subjects' => [],
                'current_semester' => null,
                'program' => null,
                'profile_not_found' => true,
            ]);
            return;
        }

        $programId = (int) $studentProfile['program_id'];
        $program = $this->program->find($programId);
        $currentSemester = $this->getCurrentSemesterForProgram($programId);

        $subjects = [];
        if ($currentSemester) {
            $subjects = $this->getStudentSubjects((int) $currentSemester['id']);
        }

        $this->view('student.subjects', [
            'title' => 'Subjects',
            'subjects' => $subjects,
            'current_semester' => $currentSemester,
            'program' => $program,
            'profile_not_found' => false,
        ]);
    }

    private function getStudentSubjects(int $semesterId): array
    {
        $allSubjects = $this->subject->all();
        $subjectsForSemester = [];
        $subjectIds = [];

        foreach ($allSubjects as $subject) {
            if ((int) ($subject['semester_id'] ?? 0) === $semesterId) {
                $subjectsForSemester[] = $subject;
                $subjectIds[(int) $subject['id']] = true;
            }
        }

        if ($subjectsForSemester === []) {
            return [];
        }

        $teacherCache = [];
        $subjectTeachers = [];
        $assignments = $this->assignment->all();

        foreach ($assignments as $assignment) {
            $subjectId = (int) ($assignment['subject_id'] ?? 0);
            if ($subjectId === 0 || !isset($subjectIds[$subjectId])) {
                continue;
            }

            $teacherId = (int) ($assignment['teacher_id'] ?? 0);
            if ($teacherId <= 0) {
                continue;
            }

            if (!isset($teacherCache[$teacherId])) {
                $teacher = $this->user->find($teacherId);
                $teacherCache[$teacherId] = (string) ($teacher['full_name'] ?? '');
            }

            $teacherName = $teacherCache[$teacherId];
            if ($teacherName === '') {
                continue;
            }

            $subjectTeachers[$subjectId] = $subjectTeachers[$subjectId] ?? [];
            $subjectTeachers[$subjectId][] = $teacherName;
        }

        $rows = [];

        foreach ($subjectsForSemester as $subject) {
            $subjectId = (int) ($subject['id'] ?? 0);
            $teacherNames = $subjectTeachers[$subjectId] ?? [];
            $teacherNames = array_values(array_unique($teacherNames));

            $rows[] = [
                'subject_id' => $subjectId,
                'subject_name' => (string) ($subject['subject_name'] ?? ''),
                'subject_code' => (string) ($subject['subject_code'] ?? ''),
                'teacher_names' => $teacherNames,
                'teacher_display' => $teacherNames !== [] ? implode(', ', $teacherNames) : 'TBA',
            ];
        }

        usort($rows, static function (array $a, array $b): int {
            return (int) ($a['subject_id'] ?? 0) <=> (int) ($b['subject_id'] ?? 0);
        });

        return $rows;
    }

    public function showTimetable(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->redirect('/login');
            return;
        }

        $studentProfile = $this->studentProfile->findByUserId($userId);
        if (!$studentProfile) {
            $this->view('student.timetable', [
                'title' => 'Timetable',
                'timetable' => [],
                'current_semester' => null,
                'profile_not_found' => true,
            ]);
            return;
        }

        $programId = (int) $studentProfile['program_id'];
        $program = $this->program->find($programId);
        $currentSemesters = $this->semester->where('is_current', 1);
        $currentSemester = !empty($currentSemesters) ? $currentSemesters[0] : null;

        if (!$currentSemester) {
            $timetableData = [];
        } else {
            $timetableData = $this->getStudentTimetable($programId, (int) $currentSemester['id']);
        }

        $this->view('student.timetable', [
            'title' => 'Timetable',
            'timetable' => $timetableData,
            'program' => $program,
            'current_semester' => $currentSemester,
            'profile_not_found' => false,
        ]);
    }

    private function getStudentTimetable(int $programId, int $semesterId): array
    {
        // Get all subjects for the program and semester
        $allSubjects = $this->subject->all();
        $subjectsForSemester = [];

        foreach ($allSubjects as $subject) {
            if ((int) $subject['semester_id'] === $semesterId) {
                $subjectsForSemester[] = $subject;
            }
        }

        $configModel = new SystemConfigModel();
        $workingDayCodes = $configModel->getWorkingDayCodes();
        if ($workingDayCodes === []) {
            error_log('[CONFIG_DEBUG] StudentController missing WORKING_DAYS config');
            return [];
        }

        $dayLabelMap = [
            'MON' => 'Monday',
            'TUE' => 'Tuesday',
            'WED' => 'Wednesday',
            'THU' => 'Thursday',
            'FRI' => 'Friday',
            'SAT' => 'Saturday',
        ];

        $dayOrder = [];
        foreach ($workingDayCodes as $index => $code) {
            $dayOrder[$code] = $index;
        }

        $rows = [];

        // Get all timetable entries
        $allTimetables = $this->timetable->all();

        foreach ($allTimetables as $slot) {
            $assignmentId = (int) $slot['teacher_assignment_id'];
            $assignment = $this->assignment->find($assignmentId);

            if (!$assignment) {
                continue;
            }

            $subjectId = (int) $assignment['subject_id'];
            $subject = null;

            foreach ($subjectsForSemester as $s) {
                if ((int) $s['id'] === $subjectId) {
                    $subject = $s;
                    break;
                }
            }

            if (!$subject) {
                continue;
            }

            $code = strtoupper((string) ($slot['day'] ?? ''));
            if ($code === '' || !isset($dayLabelMap[$code])) {
                continue;
            }
            $label = $dayLabelMap[$code];

            $teacherName = '';
            $teacherId = (int) ($assignment['teacher_id'] ?? 0);
            if ($teacherId > 0) {
                $teacher = $this->user->find($teacherId);
                $teacherName = (string) ($teacher['full_name'] ?? '');
            }

            $rows[] = [
                'id' => $slot['id'],
                'day' => $label,
                'day_code' => $code,
                'subject_name' => $subject['subject_name'],
                'subject_code' => $subject['subject_code'],
                'start_time' => $slot['start_time'],
                'end_time' => $slot['end_time'],
                'teacher_name' => $teacherName,
            ];
        }

        usort($rows, static function ($a, $b) use ($dayOrder) {
            $aOrder = $dayOrder[$a['day_code']] ?? 99;
            $bOrder = $dayOrder[$b['day_code']] ?? 99;
            if ($aOrder !== $bOrder) {
                return $aOrder <=> $bOrder;
            }
            return strcmp((string) $a['start_time'], (string) $b['start_time']);
        });

        return $rows;
    }

    // ============================================================================
    // ATTENDANCE
    // ============================================================================

    public function showAttendance(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->redirect('/login');
            return;
        }

        // Spec: student_id in attendance references users.id
        $attendanceSummary = $this->getAttendanceSummary($userId);

        $this->view('student.attendance', [
            'title'              => 'Attendance',
            'attendance_summary' => $attendanceSummary,
            'profile_not_found'  => false,
        ]);
    }

    private function getAttendanceSummary(int $studentId): array
    {
        $allAttendance = $this->attendance->all();
        $studentAttendance = [];

        foreach ($allAttendance as $record) {
            if ((int) $record['student_id'] === $studentId) {
                $studentAttendance[] = $record;
            }
        }

        // Group by subject
        $bySubject = [];

        foreach ($studentAttendance as $record) {
            $timetableSlotId = (int) $record['timetable_slot_id'];
            $timetableSlot = $this->timetable->find($timetableSlotId);

            if (!$timetableSlot) {
                continue;
            }

            $assignmentId = (int) $timetableSlot['teacher_assignment_id'];
            $assignment = $this->assignment->find($assignmentId);

            if (!$assignment) {
                continue;
            }

            $subjectId = (int) $assignment['subject_id'];
            $subject = $this->subject->find($subjectId);

            if (!$subject) {
                continue;
            }

            $subjectKey = (int) $subject['id'];
            $subjectName = $subject['subject_name'];

            if (!isset($bySubject[$subjectKey])) {
                $bySubject[$subjectKey] = [
                    'subject_id' => $subjectKey,
                    'subject_name' => $subjectName,
                    'subject_code' => $subject['subject_code'],
                    'total_sessions' => 0,
                    'present_count' => 0,
                    'absent_count' => 0,
                ];
            }

            $bySubject[$subjectKey]['total_sessions']++;

            if ($record['status'] === 'PRESENT') {
                $bySubject[$subjectKey]['present_count']++;
            } else {
                $bySubject[$subjectKey]['absent_count']++;
            }
        }

        // Calculate percentages
        foreach ($bySubject as &$subject) {
            if ($subject['total_sessions'] > 0) {
                $subject['attendance_percent'] = (int) round(($subject['present_count'] / $subject['total_sessions']) * 100);
            } else {
                $subject['attendance_percent'] = 0;
            }
        }

        return array_values($bySubject);
    }

    // ============================================================================
    // FEES
    // ============================================================================

    public function showFees(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->redirect('/login');
            return;
        }

        $studentProfile = $this->studentProfile->findByUserId($userId);
        if (!$studentProfile) {
            $this->view('student.fees', [
                'title'           => 'Fees',
                'fee_records'     => [],
                'profile_not_found' => true,
            ]);
            return;
        }

        // Spec: fees reference users.id via student_id
        $feeRecords = $this->getStudentFeeRecords($userId);

        $this->view('student.fees', [
            'title'           => 'Fees',
            'fee_records'     => $feeRecords,
            'profile_not_found' => false,
        ]);
    }

    private function getStudentFeeRecords(int $studentUserId): array
    {
        $feeRecords     = $this->studentFee->forStudent($studentUserId);
        $enrichedRecords = [];

        foreach ($feeRecords as $fee) {
            $semesterId = (int) $fee['semester_id'];
            $semester   = $this->semester->find($semesterId);

            if (!$semester) {
                continue;
            }

            // Spec: BCMath for all fee arithmetic
            $amountPaid = (string) ($fee['amount_paid'] ?? '0.00');
            $totalFee   = (string) ($semester['fee_amount'] ?? '0.00');
            $pending    = bcsub($totalFee, $amountPaid, 2);
            if (bccomp($pending, '0', 2) < 0) {
                $pending = '0.00';
            }
            $status = bccomp($pending, '0', 2) <= 0 ? 'PAID' : 'PENDING';

            $enrichedRecords[] = [
                'semester_id'    => $semesterId,
                'academic_year'  => $semester['academic_year'],
                'semester_number'=> $semester['semester_number'],
                'fee_amount'     => $totalFee,
                'amount_paid'    => $amountPaid,
                'pending_amount' => $pending,
                'status'         => $status,
            ];
        }

        return $enrichedRecords;
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

        $user = $this->user->find($userId);
        $studentProfile = $this->studentProfile->findByUserId($userId);

        if (!$user) {
            $this->json(['error' => 'User not found'], 404);
            return;
        }

        $program = null;
        $profileNotFound = false;

        if (!$studentProfile) {
            $profileNotFound = true;
        } else {
            $program = $this->program->find((int) $studentProfile['program_id']);
        }

        $emailRequestPending = $this->emailChangeRequests->hasPendingForUser($userId);
        $emailVerification = $this->emailChangeVerifications->getActiveForUser($userId);
        $emailVerificationPending = $emailVerification !== null;
        $emailVerificationEmail = $emailVerificationPending ? (string) ($emailVerification['new_email'] ?? '') : '';
        $emailVerificationExpiresAt = $emailVerificationPending ? (string) ($emailVerification['expires_at'] ?? '') : '';

        $this->view('student.profile', [
            'title' => 'My Profile',
            'user' => $user,
            'student_profile' => $studentProfile ?? [],
            'program' => $program,
            'profile_not_found' => $profileNotFound,
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
        $fatherName = trim((string) $this->input('father_name', ''));
        $dateOfBirth = trim((string) $this->input('date_of_birth', ''));

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

        if ($fatherName === '') {
            $this->json(['success' => false, 'message' => 'Father name is required.'], 422);
            return;
        }

        if (!preg_match('/^[A-Za-z\s\.]+$/', $fatherName)) {
            $this->json(['success' => false, 'message' => 'Father name must contain letters only.'], 422);
            return;
        }

        if ($dateOfBirth === '') {
            $this->json(['success' => false, 'message' => 'Date of birth is required.'], 422);
            return;
        }

        $dateError = \App\Helpers\validate_date_format($dateOfBirth);
        if ($dateError !== null) {
            $this->json(['success' => false, 'message' => $dateError], 422);
            return;
        }

        $user = $this->user->find($userId);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found'], 404);
            return;
        }

        $studentProfile = $this->studentProfile->findByUserId($userId);
        if (!$studentProfile) {
            $this->json(['success' => false, 'message' => 'Student profile not found.'], 404);
            return;
        }

        // Check if new email is taken by another user
        // Spec: update users table (name + phone only — student cannot change login_id, role, etc.)
        $this->user->updateById($userId, [
            'full_name' => $fullName,
            'phone' => $phone,
        ]);

        $this->studentProfile->updateById((int) $studentProfile['id'], [
            'father_name' => $fatherName,
            'date_of_birth' => $dateOfBirth,
        ]);

        $_SESSION['user_name'] = $fullName;

        $this->json([
            'success' => true,
            'message' => 'Profile updated successfully.',
            'data'    => [
                'full_name' => $fullName,
                'phone' => $phone,
                'father_name' => $fatherName,
                'date_of_birth' => $dateOfBirth,
            ],
        ]);
    }

    public function requestEmailChange(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        $user = $this->user->find($userId);
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'STUDENT') {
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

        $existing = $this->user->firstWhere('email', $newEmail);
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

        $user = $this->user->find($userId);
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'STUDENT') {
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

        $existing = $this->user->firstWhere('email', $newEmail);
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
            'message' => 'Email verified. Your request is pending manager approval.',
        ]);
    }

    // ============================================================================
    // PASSWORD RESET REQUEST
    // ============================================================================

    public function requestPasswordReset(): void
    {
        $userId = (int) ($_SESSION['user_id'] ?? 0);
        if ($userId === 0) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        // Check if there is already a PENDING request for this student
        $db   = \App\Core\Database::connection();
        $stmt = $db->prepare(
            'SELECT id FROM password_reset_requests WHERE requested_by = :uid AND status = :status LIMIT 1'
        );
        $stmt->execute(['uid' => $userId, 'status' => 'PENDING']);
        if ($stmt->fetch()) {
            $this->json([
                'success' => false,
                'message' => 'You already have a pending password reset request. Please wait for your manager to approve it.',
            ], 409);
            return;
        }

        // Spec: INSERT new password_reset_request row
        $this->resetRequest->create([
            'requested_by' => $userId,
            'status'       => 'PENDING',
            'created_at'   => date('Y-m-d H:i:s'),
        ]);

        $this->json([
            'success' => true,
            'message' => 'Password reset request submitted successfully. Your manager will be notified.',
        ]);
    }
}
