<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Database;
use App\Models\SystemConfigModel;
use App\Models\UserModel;
use App\Models\TeacherAssignmentModel;
use App\Models\TimetableModel;
use App\Models\SubjectModel;
use App\Models\AttendanceModel;
use App\Models\StudentProfileModel;
use PDO;

class TeacherController extends BaseController
{
    private UserModel $user;
    private TeacherAssignmentModel $teacherAssignment;
    private TimetableModel $timetable;
    private SubjectModel $subject;
    private AttendanceModel $attendance;
    private StudentProfileModel $studentProfile;

    public function __construct()
    {
        $this->user = new UserModel();
        $this->teacherAssignment = new TeacherAssignmentModel();
        $this->timetable = new TimetableModel();
        $this->subject = new SubjectModel();
        $this->attendance = new AttendanceModel();
        $this->studentProfile = new StudentProfileModel();
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

        // Calculate dashboard stats
        $stats = [
            'total_assignments' => count($allSubjects),
            'today_sessions' => count($assignments),
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
        
        // Get students for this subject/class
        $students = $this->getStudentsForSlot($slot['subject_id'] ?? 0);

        $this->view('teacher/mark-attendance', [
            'title' => 'Mark Attendance',
            'pageSubtitle' => 'Record student attendance for this session.',
            'activeNav' => 'teacher/attendance/history',
            'slot' => $slot,
            'students' => $students,
            'isWithinWindow' => $isWithinWindow,
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

        $this->view('teacher/profile', [
            'title' => 'My Profile',
            'pageSubtitle' => 'Manage your profile details',
            'user' => $user,
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
            $this->json(['success' => false, 'message' => 'Attendance window is closed'], 400);
            return;
        }

        // Get attendance data from request
        $attendanceData = $this->input('attendance', []);
        
        if (empty($attendanceData) || !is_array($attendanceData)) {
            $this->json(['success' => false, 'message' => 'No attendance data provided'], 422);
            return;
        }

        // Get today's date
        $todayDate = date('Y-m-d');
        
        // Process each student's attendance
        $saved_count = 0;
        $errors = [];
        
        foreach ($attendanceData as $studentId => $status) {
            $studentId = (int) $studentId;
            $status = strtoupper((string) $status);
            
            if (!in_array($status, ['PRESENT', 'ABSENT'], true)) {
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

        $query = "
            SELECT 
                t.id,
                t.start_time,
                t.end_time,
                s.subject_name,
                s.subject_code,
                sem.id as semester_id,
                sem.academic_year,
                sem.semester_number,
                sem.is_current,
                ta.id as assignment_id
            FROM timetables t
            JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
            JOIN subjects s ON ta.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            WHERE ta.teacher_id = :teacher_id
            AND t.day = :day
            ORDER BY t.start_time ASC
        ";

        $slots = [];
        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':teacher_id' => $teacherId,
            ':day' => $dayOfWeek,
        ]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $row['is_enabled'] = $this->isWithinAttendanceWindow($row);
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
                sem.academic_year,
                sem.semester_number,
                sem.is_current
            FROM teacher_assignments ta
            JOIN subjects s ON ta.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            WHERE ta.teacher_id = :teacher_id
            ORDER BY sem.is_current DESC, sem.academic_year DESC, s.subject_code ASC
        ";

        $subjects = [];
        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute([':teacher_id' => $teacherId]);

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
                ta.id as assignment_id
            FROM timetables t
            JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
            JOIN subjects s ON ta.subject_id = s.id
            JOIN semesters sem ON s.semester_id = sem.id
            WHERE t.id = :slot_id
            AND ta.teacher_id = :teacher_id
        ";

        $db = Database::connection();
        $stmt = $db->prepare($query);
        $stmt->execute([
            ':slot_id' => $slotId,
            ':teacher_id' => $teacherId,
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
            ORDER BY u.full_name ASC
        ";

        $students = [];
        $db       = Database::connection();
        $stmt     = $db->prepare($query);
        $stmt->execute([':subject_id' => $subjectId]);

        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $students[] = $row;
        }

        return $students;
    }

    /**
     * Spec: Attendance window = class start_time <= now <= class end_time + GRACE_MINUTES
    * GRACE_MINUTES is read from system_config.
     * NOTE: The window opens AT start_time (not before), and closes GRACE_MINUTES after end_time.
     */
    private function isWithinAttendanceWindow(array $slot): bool
    {
        $now   = new \DateTime('now', new \DateTimeZone('UTC'));
        $start = \DateTime::createFromFormat('H:i:s', $slot['start_time'] ?? '', new \DateTimeZone('UTC'));
        $end   = \DateTime::createFromFormat('H:i:s', $slot['end_time']   ?? '', new \DateTimeZone('UTC'));

        if (!$start || !$end) {
            return false;
        }

        // Read GRACE_MINUTES from system_config
        $graceMinutes = null;
        try {
            $configModel = new SystemConfigModel();
            $value = $configModel->getValue('GRACE_MINUTES');
            if ($value !== null && is_numeric($value)) {
                $graceMinutes = (int) $value;
            }
        } catch (\Throwable $e) {
            error_log('[CONFIG_DEBUG] TeacherController GRACE_MINUTES load failed: ' . $e->getMessage());
        }

        if ($graceMinutes === null) {
            error_log('[CONFIG_DEBUG] TeacherController missing GRACE_MINUTES config');
            return false;
        }

        // Window: class start_time <= now <= class end_time + GRACE_MINUTES
        $windowEnd = clone $end;
        $windowEnd->modify("+{$graceMinutes} minutes");

        return $now >= $start && $now <= $windowEnd;
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
            ':student_id' => $studentId,
            ':slot_id' => $slotId,
            ':date' => $date,
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
        $params = [':teacher_id' => $teacherId];

        if (!empty($filters['subject_id'])) {
            $where_clauses[] = "s.id = :subject_id";
            $params[':subject_id'] = $filters['subject_id'];
        }

        if (!empty($filters['from_date'])) {
            $where_clauses[] = "att.date >= :from_date";
            $params[':from_date'] = $filters['from_date'];
        }

        if (!empty($filters['to_date'])) {
            $where_clauses[] = "att.date <= :to_date";
            $params[':to_date'] = $filters['to_date'];
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
                SUM(CASE WHEN att.status = 'ABSENT' THEN 1 ELSE 0 END) as absent_count
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
}

