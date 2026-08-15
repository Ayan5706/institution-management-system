<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\SystemConfigModel;
use App\Models\UserModel;
use App\Models\EmailChangeRequestModel;
use App\Models\EmailChangeVerificationModel;
use App\Services\MailService;
use DateTimeImmutable;
use DateTimeZone;
use App\Models\TeacherAssignmentModel;
use App\Models\TimetableModel;
use App\Models\SubjectModel;
use App\Models\AttendanceModel;
use App\Models\StudentProfileModel;
use App\Models\ProgramModel;
use App\Models\SemesterModel;
use App\Models\StudentFeeModel;
use PDO;

class TeacherController extends BaseController
{
    private UserModel $user;
    private TeacherAssignmentModel $teacherAssignment;
    private TimetableModel $timetable;
    private SubjectModel $subject;
    private AttendanceModel $attendance;
    private StudentProfileModel $studentProfile;
    private ProgramModel $program;
    private SemesterModel $semester;
    private StudentFeeModel $fee;
    private EmailChangeRequestModel $emailChangeRequests;
    private EmailChangeVerificationModel $emailChangeVerifications;
    private MailService $mailService;

    public function __construct()
    {
        $this->user = new UserModel();
        $this->teacherAssignment = new TeacherAssignmentModel();
        $this->timetable = new TimetableModel();
        $this->subject = new SubjectModel();
        $this->attendance = new AttendanceModel();
        $this->studentProfile = new StudentProfileModel();
        $this->program = new ProgramModel();
        $this->semester = new SemesterModel();
        $this->fee = new StudentFeeModel();
        $this->emailChangeRequests = new EmailChangeRequestModel();
        $this->emailChangeVerifications = new EmailChangeVerificationModel();
        $this->mailService = new MailService();
    }

    /**
     * Show teacher dashboard with today's schedule
     */
    public function showDashboard(): void
    {
        $teacherId = (int) ($_SESSION['user_id'] ?? 0);
        $userName = (string) ($_SESSION['user_name'] ?? 'Teacher');

        if ($teacherId === 0) {
            $this->view('errors.401', ['title' => 'Unauthorized']);
            return;
        }

        // Get teacher's assignments with subject and semester info
        $assignments = $this->getTodayAssignments($teacherId);

        // Get all assignments for subjects list
        $allSubjects = $this->getAllTeacherSubjects($teacherId);

        $assignedClasses = array_unique(array_filter(array_map(
            static fn(array $subject): int => (int) ($subject['semester_id'] ?? 0),
            $allSubjects
        )));

        $pendingAttendance = 0;
        foreach ($assignments as $slot) {
            if (empty($slot['attendance_marked'])) {
                $pendingAttendance++;
            }
        }

        // Calculate dashboard stats
        $stats = [
            'assigned_subjects' => count($allSubjects),
            'assigned_classes' => count($assignedClasses),
            'today_classes' => count($assignments),
            'pending_attendance' => $pendingAttendance,
        ];

        $this->view('teacher/dashboard', [
            'title' => 'Dashboard',
            'pageSubtitle' => 'Manage your classes and attendance.',
            'activeNav' => 'teacher/dashboard',
            'user_name' => $userName,
            'assignments' => $assignments,
            'all_subjects' => $allSubjects,
            'stats' => $stats,
        ]);
    }

    /**
     * Show attendance sessions list for today
     */
    public function showAttendanceSessions(): void
    {
        $teacherId = (int) ($_SESSION['user_id'] ?? 0);
        $userName = (string) ($_SESSION['user_name'] ?? 'Teacher');

        if ($teacherId === 0) {
            $this->view('errors.401', ['title' => 'Unauthorized']);
            return;
        }

        $assignments = $this->getTodayAssignments($teacherId);

        $this->view('teacher/attendance-sessions', [
            'title' => 'Mark Attendance',
            'pageSubtitle' => 'Select a class session to mark attendance.',
            'activeNav' => 'teacher/attendance',
            'user_name' => $userName,
            'assignments' => $assignments,
        ]);
    }

    /**
     * Show mark attendance page for a specific slot
     */
    public function showMarkAttendance(int $slotId): void
    {
        $teacherId = (int) ($_SESSION['user_id'] ?? 0);
        
        if ($teacherId === 0) {
            $this->view('errors.401', ['title' => 'Unauthorized']);
            return;
        }

        // Get the slot with all related info
        $slot = $this->getTimetableSlotWithDetails($slotId, $teacherId);
        
        if (empty($slot)) {
            $this->view('errors.404', ['title' => 'Slot Not Found']);
            return;
        }

        // Check if current time is within attendance window
        $isWithinWindow = $this->isWithinAttendanceWindow($slot);
        $attendanceCompleted = $this->isAttendanceCompleted($slotId, date('Y-m-d'));
        
        // Get students for this subject/class
        $students = $this->getStudentsForSlot($slot['subject_id'] ?? 0);

        $this->view('teacher/mark-attendance', [
            'title' => 'Mark Attendance',
            'pageSubtitle' => 'Record student attendance for this session.',
            'activeNav' => 'teacher/attendance',
            'slot' => $slot,
            'students' => $students,
            'isWithinWindow' => $isWithinWindow,
            'attendanceCompleted' => $attendanceCompleted,
            'slot_id' => $slotId,
        ]);
    }

    /**
     * Show attendance history page
     */
    public function showAttendanceHistory(): void
    {
        $teacherId = (int) ($_SESSION['user_id'] ?? 0);
        
        if ($teacherId === 0) {
            $this->view('errors.401', ['title' => 'Unauthorized']);
            return;
        }

        // Get all subjects taught by this teacher
        $subjects = $this->getAllTeacherSubjects($teacherId);
        
        // Get attendance records based on filters
        $filters = [
            'subject_id' => (int) $this->input('subject_id', 0),
            'from_date' => (string) $this->input('from_date', ''),
            'to_date' => (string) $this->input('to_date', ''),
        ];

        $attendance_records = $this->getAttendanceHistory($teacherId, $filters);

        $this->view('teacher/attendance-history', [
            'title' => 'Attendance History',
            'pageSubtitle' => 'View past attendance records.',
            'activeNav' => 'teacher/attendance/history',
            'subjects' => $subjects,
            'attendance_records' => $attendance_records,
            'filters' => $filters,
        ]);
    }

    // ============================================================================
    // TIMETABLE + STUDENTS
    // ============================================================================

    public function showTimetable(): void
    {
        $teacherId = (int) ($_SESSION['user_id'] ?? 0);
        if ($teacherId === 0) {
            $this->view('errors.401', ['title' => 'Unauthorized']);
            return;
        }

        $timetable = $this->getTeacherTimetable($teacherId);

        $this->view('teacher.timetable', [
            'title' => 'Teacher Timetable',
            'pageSubtitle' => 'Your weekly teaching schedule',
            'activeNav' => 'teacher/timetable',
            'timetable' => $timetable,
        ]);
    }

    public function showStudents(): void
    {
        $teacherId = (int) ($_SESSION['user_id'] ?? 0);
        if ($teacherId === 0) {
            $this->view('errors.401', ['title' => 'Unauthorized']);
            return;
        }

        $programId = (int) $this->input('program_id', 0);
        $semesterId = (int) $this->input('semester_id', 0);
        $classSection = (int) $this->input('class_section', 0);

        $filters = [
            'program_id' => $programId,
            'semester_id' => $semesterId,
            'class_section' => $classSection,
        ];

        $students = $this->getTeacherStudents($teacherId, $filters);
        $programs = $this->getTeacherPrograms($teacherId);
        $semesters = $this->getTeacherSemesters($teacherId, $programId);

        $this->view('teacher.students', [
            'title' => 'Teacher Students',
            'pageSubtitle' => 'Students assigned to your classes',
            'activeNav' => 'teacher/students',
            'students' => $students,
            'programs' => $programs,
            'semesters' => $semesters,
            'filters' => $filters,
        ]);
    }

    public function showStudentDetail(int $id): void
    {
        $teacherId = (int) ($_SESSION['user_id'] ?? 0);
        if ($teacherId === 0) {
            $this->view('errors.401', ['title' => 'Unauthorized']);
            return;
        }

        if (!$this->teacherCanViewStudent($teacherId, $id)) {
            http_response_code(403);
            echo 'Forbidden';
            return;
        }

        $student = $this->user->find($id);

        if (!$student || strtoupper((string) ($student['role'] ?? '')) !== 'STUDENT') {
            http_response_code(404);
            echo 'Student not found';
            return;
        }

        $studentProfile = $this->studentProfile->findByUserId($id);
        $program = null;
        $currentSemester = null;

        if ($studentProfile && isset($studentProfile['program_id'])) {
            $program = $this->program->find((int) $studentProfile['program_id']);

            if ($program) {
                $semesters = $this->semester->where('program_id', (int) $program['id']);
                foreach ($semesters as $semester) {
                    if ((int) ($semester['is_current'] ?? 0) === 1) {
                        $currentSemester = $semester;
                        break;
                    }
                }
            }
        }

        $attendanceRecords = $this->attendance->where('student_id', $id);
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
            $createdBy = $this->user->find($createdById);
        }

        $feeStatus = 'N/A';
        $feePaid = 0.0;
        if ($currentSemester) {
            $fee = $this->fee->firstWhere('student_id', $id);
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

        $this->view('teacher.student-detail', [
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

        $this->view('teacher/profile', [
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

        $user = $this->user->find($userId);
        if (!$user) {
            $this->json(['success' => false, 'message' => 'User not found.'], 404);
            return;
        }

        $this->user->updateById($userId, [
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

        $user = $this->user->find($userId);
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'TEACHER') {
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
        if (strcasecmp($newEmail, $currentEmail) === 0) {
            $this->json(['success' => false, 'message' => 'New email must be different from current email.'], 422);
            return;
        }

        $existing = $this->user->firstWhere('email', $newEmail);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Email already exists.'], 409);
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
        if (!$user || strtoupper((string) ($user['role'] ?? '')) !== 'TEACHER') {
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
            $this->json(['success' => false, 'message' => 'New email must be different from current email.'], 422);
            return;
        }

        $existing = $this->user->firstWhere('email', $newEmail);
        if ($existing) {
            $this->json(['success' => false, 'message' => 'Email already exists.'], 409);
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
            'message' => 'Email verified. Your request is pending vice principal approval.',
        ]);
    }

    /**
     * API: Submit attendance for a session
     */
    public function submitAttendance(int $slotId): void
    {
        $teacherId = (int) ($_SESSION['user_id'] ?? 0);
        
        if ($teacherId === 0) {
            $this->json(['success' => false, 'message' => 'Unauthorized'], 401);
            return;
        }

        // Verify teacher owns this slot
        $slot = $this->getTimetableSlotWithDetails($slotId, $teacherId);
        if (empty($slot)) {
            $this->json(['success' => false, 'message' => 'Slot not found'], 404);
            return;
        }

        // Check attendance window
        if (!$this->isWithinAttendanceWindow($slot)) {
            $this->json(['success' => false, 'message' => 'Attendance can only be marked during class hours'], 400);
            return;
        }

        $todayDate = date('Y-m-d');
        if ($this->isAttendanceCompleted($slotId, $todayDate)) {
            $this->json(['success' => false, 'message' => 'Attendance is already completed for this session.'], 409);
            return;
        }

        // Get attendance data from request
        $attendanceData = $this->input('attendance', []);
        
        if (empty($attendanceData) || !is_array($attendanceData)) {
            $this->json(['success' => false, 'message' => 'No attendance data provided'], 422);
            return;
        }

        $students = $this->getStudentsForSlot((int) ($slot['subject_id'] ?? 0));
        $studentIds = array_map('intval', array_column($students, 'id'));
        $submittedIds = array_map('intval', array_keys($attendanceData));

        $missingIds = array_diff($studentIds, $submittedIds);
        if (!empty($missingIds)) {
            $this->json(['success' => false, 'message' => 'Please mark attendance for every student in this class.'], 422);
            return;
        }

        $extraIds = array_diff($submittedIds, $studentIds);
        if (!empty($extraIds)) {
            $this->json(['success' => false, 'message' => 'Invalid student selection for this class.'], 422);
            return;
        }

        // Process each student's attendance
        $saved_count = 0;
        $errors = [];
        
        foreach ($attendanceData as $studentId => $status) {
            $studentId = (int) $studentId;
            $status = strtoupper((string) $status);
            
            if (!in_array($status, ['PRESENT', 'ABSENT', 'LATE'], true)) {
                $errors[] = "Invalid status for student {$studentId}";
                continue;
            }

            // Check if record already exists for this date
            $existing = $this->getAttendanceRecord($studentId, $slotId, $todayDate);
            
            if ($existing && !empty($existing)) {
                // Update existing record
                if ($this->attendance->updateById((int) $existing['id'], [
                    'status' => $status,
                    'marked_by' => $teacherId,
                    'marked_at' => date('Y-m-d H:i:s'),
                ])) {
                    $saved_count++;
                } else {
                    $errors[] = "Failed to update record for student {$studentId}";
                }
            } else {
                // Create new record
                if ($this->attendance->create([
                    'student_id' => $studentId,
                    'timetable_slot_id' => $slotId,
                    'date' => $todayDate,
                    'status' => $status,
                    'marked_by' => $teacherId,
                    'marked_at' => date('Y-m-d H:i:s'),
                ])) {
                    $saved_count++;
                } else {
                    $errors[] = "Failed to create record for student {$studentId}";
                }
            }
        }

        $this->json([
            'success' => count($errors) === 0,
            'message' => $saved_count > 0 ? "{$saved_count} attendance record(s) saved" : 'No records saved',
            'saved_count' => $saved_count,
            'errors' => $errors,
        ], count($errors) === 0 ? 200 : 207);
    }

    /**
     * Get today's assignments (timetable slots) for teacher
     */
    private function getTodayAssignments(int $teacherId): array
    {
        $today = strtoupper(date('D'));
        $dayMap = [
            'Mon' => 'MON', 'Tue' => 'TUE', 'Wed' => 'WED',
            'Thu' => 'THU', 'Fri' => 'FRI', 'Sat' => 'SAT',
        ];
        $dayOfWeek = $dayMap[date('D')] ?? '';

        if (empty($dayOfWeek)) {
            return [];
        }

        $todayDate = date('Y-m-d');
        $query = "
            SELECT 
                t.id,
                t.start_time,
                t.end_time,
                t.day,
                s.subject_name,
                s.subject_code,
                sem.id as semester_id,
                sem.academic_year,
                sem.semester_number,
                sem.is_current,
                ta.id as assignment_id,
                COALESCE(att.attendance_count, 0) as attendance_count
            FROM timetables t
            JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
            JOIN subjects s ON ta.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            LEFT JOIN (
                SELECT timetable_slot_id, COUNT(*) as attendance_count
                FROM attendance
                WHERE date = :today
                GROUP BY timetable_slot_id
            ) att ON att.timetable_slot_id = t.id
            WHERE ta.teacher_id = :teacher_id
            AND t.day = :day
            ORDER BY t.start_time ASC
        ";

        $slots = [];
        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute([
            'teacher_id' => $teacherId,
            'day' => $dayOfWeek,
            'today' => $todayDate,
        ]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['is_enabled'] = $this->isWithinAttendanceWindow($row);
            $row['attendance_marked'] = ((int) ($row['attendance_count'] ?? 0)) > 0;
            $slots[] = $row;
        }

        return $slots;
    }

    /**
     * Get all subjects assigned to teacher
     */
    private function getAllTeacherSubjects(int $teacherId): array
    {
        $query = "
            SELECT DISTINCT
                s.id,
                s.subject_name,
                s.subject_code,
                sem.id as semester_id,
                sem.academic_year,
                sem.semester_number,
                sem.is_current
            FROM teacher_assignments ta
            JOIN subjects s ON ta.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            WHERE ta.teacher_id = :teacher_id
            ORDER BY s.id ASC
        ";

        $subjects = [];
        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute(['teacher_id' => $teacherId]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $subjects[] = $row;
        }

        return $subjects;
    }

    /**
     * Get timetable slot with full details (only if teacher owns it)
     */
    private function getTimetableSlotWithDetails(int $slotId, int $teacherId): array
    {
        $query = "
            SELECT 
                t.id,
                t.start_time,
                t.end_time,
                t.day,
                s.subject_name,
                s.subject_code,
                s.id as subject_id,
                sem.academic_year,
                sem.semester_number,
                sem.is_current,
                p.program_name,
                p.program_code,
                ta.id as assignment_id
            FROM timetables t
            JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
            JOIN subjects s ON ta.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            JOIN programs p ON sem.program_id = p.id
            WHERE t.id = :slot_id
            AND ta.teacher_id = :teacher_id
        ";

        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute([
            'slot_id' => $slotId,
            'teacher_id' => $teacherId,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    /**
     * Spec: Get students enrolled in the program for the given subject's semester.
     * Students are fetched by matching student_profiles.program_id to the subject's semester's program_id.
     * The attendance table uses users.id (not student_profiles.id) as student_id.
     */
    private function getStudentsForSlot(int $subjectId): array
    {
        $query = "
            SELECT
                u.id,
                u.full_name,
                sp.registration_number
            FROM users u
            JOIN student_profiles sp ON sp.user_id = u.id
            JOIN subjects sub        ON sub.id = :subject_id
            JOIN semesters sem        ON sem.id = sub.semester_id
            WHERE sp.program_id = sem.program_id
              AND u.role = 'STUDENT'
              AND u.is_active = 1
            ORDER BY COALESCE(sp.registration_number, u.login_id) ASC, u.id ASC
        ";

        $students = [];
        $db       = Database::connection();
        $stmt     = $db->prepare($query);
        $stmt->execute(['subject_id' => $subjectId]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $students[] = $row;
        }

        return $students;
    }

    /**
     * Spec: Attendance window = class start_time <= now <= class end_time (same day)
     */
    private function isWithinAttendanceWindow(array $slot): bool
    {
        $timezone = new \DateTimeZone(date_default_timezone_get());
        $now = new \DateTime('now', $timezone);

        $dayCodeMap = [
            'MON' => 'Mon',
            'TUE' => 'Tue',
            'WED' => 'Wed',
            'THU' => 'Thu',
            'FRI' => 'Fri',
            'SAT' => 'Sat',
        ];

        $slotDay = strtoupper(trim((string) ($slot['day'] ?? '')));
        $todayShort = $now->format('D');
        if (!isset($dayCodeMap[$slotDay]) || $dayCodeMap[$slotDay] !== $todayShort) {
            return false;
        }

        $allowSameDay = filter_var(getenv('ATTENDANCE_WINDOW_SAME_DAY') ?: 'false', FILTER_VALIDATE_BOOLEAN);
        if ($allowSameDay) {
            return true;
        }

        $datePrefix = $now->format('Y-m-d');
        $startTime = trim((string) ($slot['start_time'] ?? ''));
        $endTime = trim((string) ($slot['end_time'] ?? ''));
        $start = \DateTime::createFromFormat('Y-m-d H:i:s', $datePrefix . ' ' . $startTime, $timezone);
        if (!$start) {
            $start = \DateTime::createFromFormat('Y-m-d H:i', $datePrefix . ' ' . $startTime, $timezone);
        }
        if (!$start) {
            $start = \DateTime::createFromFormat('Y-m-d h:i A', $datePrefix . ' ' . $startTime, $timezone);
        }
        if (!$start) {
            $start = \DateTime::createFromFormat('Y-m-d h:i:s A', $datePrefix . ' ' . $startTime, $timezone);
        }

        $end = \DateTime::createFromFormat('Y-m-d H:i:s', $datePrefix . ' ' . $endTime, $timezone);
        if (!$end) {
            $end = \DateTime::createFromFormat('Y-m-d H:i', $datePrefix . ' ' . $endTime, $timezone);
        }
        if (!$end) {
            $end = \DateTime::createFromFormat('Y-m-d h:i A', $datePrefix . ' ' . $endTime, $timezone);
        }
        if (!$end) {
            $end = \DateTime::createFromFormat('Y-m-d h:i:s A', $datePrefix . ' ' . $endTime, $timezone);
        }

        if (!$start || !$end) {
            $startTs = strtotime($datePrefix . ' ' . $startTime);
            $endTs = strtotime($datePrefix . ' ' . $endTime);
            if ($startTs === false || $endTs === false) {
                return false;
            }
            $nowTs = $now->getTimestamp();
            return $nowTs >= $startTs && $nowTs <= $endTs;
        }

        return $now >= $start && $now <= $end;
    }

    /**
     * Get existing attendance record for student/slot/date
     */
    private function getAttendanceRecord(int $studentId, int $slotId, string $date): array
    {
        $query = "
            SELECT * FROM attendance
            WHERE student_id = :student_id
            AND timetable_slot_id = :slot_id
            AND date = :date
            LIMIT 1
        ";

        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute([
            'student_id' => $studentId,
            'slot_id' => $slotId,
            'date' => $date,
        ]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: [];
    }

    /**
     * Get attendance history records with filters
     */
    private function getAttendanceHistory(int $teacherId, array $filters): array
    {
        $where_clauses = [
            "ta.teacher_id = :teacher_id"
        ];
        $params = ['teacher_id' => $teacherId];

        if (!empty($filters['subject_id'])) {
            $where_clauses[] = "s.id = :subject_id";
            $params['subject_id'] = $filters['subject_id'];
        }

        if (!empty($filters['from_date'])) {
            $where_clauses[] = "att.date >= :from_date";
            $params['from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where_clauses[] = "att.date <= :to_date";
            $params['to_date'] = $filters['to_date'];
        }

        $where = implode(' AND ', $where_clauses);

        $query = "
            SELECT 
                DATE(att.date) as date,
                s.subject_name,
                s.subject_code,
                sem.semester_number,
                sem.academic_year,
                COUNT(DISTINCT att.student_id) as total_students,
                SUM(CASE WHEN att.status = 'PRESENT' THEN 1 ELSE 0 END) as present_count,
                SUM(CASE WHEN att.status = 'ABSENT' THEN 1 ELSE 0 END) as absent_count,
                SUM(CASE WHEN att.status = 'LATE' THEN 1 ELSE 0 END) as late_count
            FROM attendance att
            JOIN timetables t ON att.timetable_slot_id = t.id
            JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
            JOIN subjects s ON ta.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            WHERE {$where}
            GROUP BY DATE(att.date), s.id, s.subject_name, s.subject_code, sem.semester_number, sem.academic_year
            ORDER BY att.date DESC, s.subject_code ASC
        ";

        $records = [];
        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute($params);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $records[] = $row;
        }

        return $records;
    }

    /** @return array<int, array<string, mixed>> */
    private function getTeacherTimetable(int $teacherId): array
    {
        $query = "
            SELECT
                t.day,
                t.start_time,
                t.end_time,
                s.subject_name,
                s.subject_code,
                sem.semester_number,
                sem.academic_year,
                p.program_code,
                p.program_name
            FROM timetables t
            JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
            JOIN subjects s ON ta.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            JOIN programs p ON sem.program_id = p.id
            WHERE ta.teacher_id = :teacher_id
            ORDER BY FIELD(t.day, 'MON','TUE','WED','THU','FRI','SAT'), t.start_time ASC
        ";

        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute(['teacher_id' => $teacherId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int, array<string, mixed>> */
    private function getTeacherPrograms(int $teacherId): array
    {
        $query = "
            SELECT DISTINCT p.id, p.program_name, p.program_code
            FROM teacher_assignments ta
            JOIN subjects s ON ta.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            JOIN programs p ON sem.program_id = p.id
            WHERE ta.teacher_id = :teacher_id
            ORDER BY p.id ASC
        ";

        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute(['teacher_id' => $teacherId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int, array<string, mixed>> */
    private function getTeacherSemesters(int $teacherId, int $programId = 0): array
    {
        $where = 'ta.teacher_id = :teacher_id';
        $params = ['teacher_id' => $teacherId];

        if ($programId > 0) {
            $where .= ' AND sem.program_id = :program_id';
            $params['program_id'] = $programId;
        }

        $query = "
            SELECT DISTINCT sem.id, sem.semester_number, sem.academic_year, sem.program_id
            FROM teacher_assignments ta
            JOIN subjects s ON ta.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            WHERE {$where}
            ORDER BY sem.id ASC
        ";

        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /** @return array<int, array<string, mixed>> */
    private function getTeacherStudents(int $teacherId, array $filters): array
    {
        $where = ["ta.teacher_id = :teacher_id", "u.role = 'STUDENT'"];
        $params = ['teacher_id' => $teacherId];

        if (!empty($filters['program_id'])) {
            $where[] = 'p.id = :program_id';
            $params['program_id'] = (int) $filters['program_id'];
        }

        $semesterFilter = (int) ($filters['semester_id'] ?? 0);
        $classFilter = (int) ($filters['class_section'] ?? 0);
        $semesterId = $classFilter > 0 ? $classFilter : $semesterFilter;
        if ($semesterId > 0) {
            $where[] = 'sem.id = :semester_id';
            $params['semester_id'] = $semesterId;
        }

        $query = "
            SELECT
                u.id,
                u.full_name,
                u.is_active,
                sp.registration_number,
                p.program_name,
                p.program_code,
                sem.id as semester_id,
                sem.semester_number,
                sem.academic_year
            FROM teacher_assignments ta
            JOIN subjects s ON ta.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            JOIN programs p ON sem.program_id = p.id
            JOIN student_profiles sp ON sp.program_id = p.id
            JOIN users u ON u.id = sp.user_id
            WHERE " . implode(' AND ', $where) . "
            GROUP BY u.id, sem.id
            ORDER BY COALESCE(sp.registration_number, u.login_id) ASC, u.id ASC
        ";

        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    private function teacherCanViewStudent(int $teacherId, int $studentId): bool
    {
        $query = "
            SELECT 1
            FROM teacher_assignments ta
            JOIN subjects s ON ta.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            JOIN programs p ON sem.program_id = p.id
            JOIN student_profiles sp ON sp.program_id = p.id
            WHERE ta.teacher_id = :teacher_id
              AND sp.user_id = :student_id
            LIMIT 1
        ";

        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute([
            'teacher_id' => $teacherId,
            'student_id' => $studentId,
        ]);

        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    private function getAttendanceCountForSlotDate(int $slotId, string $date): int
    {
        $db = Database::connection();
        $stmt = $db->prepare('SELECT COUNT(*) as total FROM attendance WHERE timetable_slot_id = :slot_id AND date = :date');
        $stmt->execute([
            'slot_id' => $slotId,
            'date' => $date,
        ]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int) ($row['total'] ?? 0);
    }

    private function isAttendanceCompleted(int $slotId, string $date): bool
    {
        return $this->getAttendanceCountForSlotDate($slotId, $date) > 0;
    }
}

