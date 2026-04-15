<?php

namespace App\Services;

use App\Core\Database;

class TimetableService
{
    private Database $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Check for semester clash: another class in same semester at same day/time
     * Per spec Part 3 Table 7 clash detection
     * 
     * @param int $semesterId Semester ID
     * @param string $dayOfWeek Day (MON, TUE, WED, THU, FRI, SAT)
     * @param string $startTime Time in HH:MM format
     * @param string $endTime Time in HH:MM format
     * @param string $room Location/room
     * @param int|null $excludeId Exclude timetable ID (for updates)
     * @return array Clash details if found, empty array if no clash
     */
    public function checkSemesterClash(
        int $semesterId,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        string $room,
        ?int $excludeId = null
    ): array {
        try {
            // Find existing entries in same semester, day, and overlapping time in same room
            $query = '
                SELECT t.id, s.name as subject_name, ta.teacher_user_id, u.full_name as teacher_name
                FROM timetable t
                INNER JOIN subjects s ON t.subject_id = s.id
                INNER JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
                INNER JOIN users u ON ta.teacher_user_id = u.id
                WHERE t.semester_id = ?
                AND t.day_of_week = ?
                AND t.location = ?
                AND t.is_active = 1
            ';

            $params = [$semesterId, $dayOfWeek, $room];

            // Add exclusion for update operations
            if ($excludeId !== null) {
                $query .= ' AND t.id != ?';
                $params[] = $excludeId;
            }

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            // Check each existing entry for time overlap
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if ($this->timesOverlap($startTime, $endTime, $row['start_time'], $row['end_time'])) {
                    return [
                        'type' => 'semester',
                        'clash_with_id' => $row['id'],
                        'subject' => $row['subject_name'],
                        'teacher' => $row['teacher_name'],
                        'message' => "Room {$room} is already in use at {$row['start_time']}-{$row['end_time']} by {$row['subject_name']}"
                    ];
                }
            }

            return [];
        } catch (\Exception $e) {
            \App\Helpers\logger_helper('timetable_clash_error', 'TimetableService::checkSemesterClash error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Check for teacher clash: teacher assigned to another class at overlapping time
     * Per spec Part 3 Table 7 clash detection
     * 
     * @param int $teacherId User ID of teacher
     * @param string $dayOfWeek Day (MON, TUE, WED, THU, FRI, SAT)
     * @param string $startTime Time in HH:MM format
     * @param string $endTime Time in HH:MM format
     * @param int|null $excludeId Exclude timetable ID (for updates)
     * @return array Clash details if found, empty array if no clash
     */
    public function checkTeacherClash(
        int $teacherId,
        string $dayOfWeek,
        string $startTime,
        string $endTime,
        ?int $excludeId = null
    ): array {
        try {
            // Find other timetable entries for same teacher on same day
            $query = '
                SELECT t.id, s.name as subject_name, sem.academic_year
                FROM timetable t
                INNER JOIN subjects s ON t.subject_id = s.id
                INNER JOIN semesters sem ON t.semester_id = sem.id
                INNER JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
                WHERE ta.teacher_user_id = ?
                AND t.day_of_week = ?
                AND t.is_active = 1
            ';

            $params = [$teacherId, $dayOfWeek];

            // Add exclusion for update operations
            if ($excludeId !== null) {
                $query .= ' AND t.id != ?';
                $params[] = $excludeId;
            }

            $stmt = $this->db->getConnection()->prepare($query);
            $stmt->execute($params);

            // Check each for time overlap
            while ($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
                if ($this->timesOverlap($startTime, $endTime, $row['start_time'], $row['end_time'])) {
                    return [
                        'type' => 'teacher',
                        'clash_with_id' => $row['id'],
                        'subject' => $row['subject_name'],
                        'academic_year' => $row['academic_year'],
                        'message' => "Teacher already assigned to {$row['subject_name']} at {$row['start_time']}-{$row['end_time']}"
                    ];
                }
            }

            return [];
        } catch (\Exception $e) {
            \App\Helpers\logger_helper('timetable_clash_error', 'TimetableService::checkTeacherClash error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Helper function: Check if two time ranges overlap
     * Times in HH:MM format
     * 
     * @param string $start1 Start time 1
     * @param string $end1 End time 1
     * @param string $start2 Start time 2
     * @param string $end2 End time 2
     * @return bool True if times overlap
     */
    private function timesOverlap(string $start1, string $end1, string $start2, string $end2): bool
    {
        $start1Min = $this->timeToMinutes($start1);
        $end1Min = $this->timeToMinutes($end1);
        $start2Min = $this->timeToMinutes($start2);
        $end2Min = $this->timeToMinutes($end2);

        // Two ranges overlap if: start1 < end2 AND start2 < end1
        return $start1Min < $end2Min && $start2Min < $end1Min;
    }

    /**
     * Helper function: Convert HH:MM to minutes since midnight
     * 
     * @param string $time Time in HH:MM format
     * @return int Minutes since midnight
     */
    private function timeToMinutes(string $time): int
    {
        [$hours, $minutes] = explode(':', $time);
        return (int)$hours * 60 + (int)$minutes;
    }

    /**
     * Get timetable for specific semester with teacher/subject info
     * Formatted per spec Part 3 Table 7
     * 
     * @param int $semesterId Semester ID
     * @return array Array of timetable entries
     */
    public function getTimetableBySemester(int $semesterId): array
    {
        try {
            $stmt = $this->db->getConnection()->prepare('
                SELECT 
                    t.id,
                    t.semester_id,
                    t.subject_id,
                    s.name as subject_name,
                    t.teacher_assignment_id,
                    u.id as teacher_id,
                    u.full_name as teacher_name,
                    t.day_of_week,
                    t.start_time,
                    t.end_time,
                    t.location,
                    t.is_active,
                    t.created_at
                FROM timetable t
                INNER JOIN subjects s ON t.subject_id = s.id
                INNER JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
                INNER JOIN users u ON ta.teacher_user_id = u.id
                WHERE t.semester_id = ? AND t.is_active = 1
                ORDER BY 
                    FIELD(t.day_of_week, \"MON\", \"TUE\", \"WED\", \"THU\", \"FRI\", \"SAT\"),
                    t.start_time
            ');

            $stmt->execute([$semesterId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            \App\Helpers\logger_helper('timetable_error', 'TimetableService::getTimetableBySemester error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get timetable for specific teacher
     * 
     * @param int $teacherId User ID of teacher
     * @return array Array of timetable entries
     */
    public function getTimetableByTeacher(int $teacherId): array
    {
        try {
            $stmt = $this->db->getConnection()->prepare('
                SELECT 
                    t.id,
                    t.semester_id,
                    sem.academic_year,
                    t.subject_id,
                    s.name as subject_name,
                    t.day_of_week,
                    t.start_time,
                    t.end_time,
                    t.location,
                    t.is_active,
                    t.created_at
                FROM timetable t
                INNER JOIN subjects s ON t.subject_id = s.id
                INNER JOIN semesters sem ON t.semester_id = sem.id
                INNER JOIN teacher_assignments ta ON t.teacher_assignment_id = ta.id
                WHERE ta.teacher_user_id = ? AND t.is_active = 1
                ORDER BY 
                    sem.academic_year DESC,
                    FIELD(t.day_of_week, \"MON\", \"TUE\", \"WED\", \"THU\", \"FRI\", \"SAT\"),
                    t.start_time
            ');

            $stmt->execute([$teacherId]);
            return $stmt->fetchAll(\PDO::FETCH_ASSOC);
        } catch (\Exception $e) {
            \App\Helpers\logger_helper('timetable_error', 'TimetableService::getTimetableByTeacher error: ' . $e->getMessage());
            return [];
        }
    }
}
