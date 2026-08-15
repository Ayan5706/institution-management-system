<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Throwable;

/**
 * Student Fee Service
 * Per Spec Part 3 Table 9 FLOW — Auto-creation Triggers
 * Handles creation of student fees during enrollment and semester activation
 */
final class StudentFeeService
{
    /**
     * Create fee records for a newly enrolled student
     * Creates StudentFee row for the LOWEST active semester (student's entry/current semester)
     * This ensures students are in their appropriate semester level, not all active semesters
     * 
     * Must be called within a PDO transaction
     * Per Spec: "After PHP INSERTs a student_profile row..."
     */
    public static function createFeesForNewStudent(int $student_id, int $program_id): void
    {
        $db = Database::connection();
        
        // Get the lowest active semester for this program (student's entry semester)
        $stmt = $db->prepare('
            SELECT id, fee_amount
            FROM semesters
            WHERE program_id = ? AND is_current = 1
            ORDER BY semester_number ASC
            LIMIT 1
        ');
        $stmt->execute([$program_id]);
        $semester = $stmt->fetch();
        
        if (!$semester) {
            return;
        }
        
        // Insert fee record for the entry semester only
        $insert_stmt = $db->prepare('
            INSERT INTO student_fees (student_id, semester_id, amount_paid)
            VALUES (?, ?, \'0.00\')
            ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)
        ');
        
        $insert_stmt->execute([$student_id, $semester['id']]);
    }

    /**
     * Create fee records for all active students when a semester is activated
     * Creates StudentFee rows for students who don't yet have fees in any active semester
     * of this program. This ensures students show up in only their current active semester.
     * 
     * Must be called within a PDO transaction
     * Per Spec: "When semester is activated (is_current set to 1)..."
     */
    public static function createFeesOnSemesterActivation(int $semester_id): void
    {
        $db = Database::connection();
        
        // Get program_id from semester
        $sem_stmt = $db->prepare('SELECT program_id, semester_number FROM semesters WHERE id = ?');
        $sem_stmt->execute([$semester_id]);
        $semester = $sem_stmt->fetch();
        
        if (!$semester) {
            return;
        }
        
        $program_id = $semester['program_id'];
        
        // Get active students in this program who don't already have fees in any active semester
        $student_stmt = $db->prepare('
            SELECT DISTINCT sp.user_id
            FROM student_profiles sp
            JOIN users u ON sp.user_id = u.id
            WHERE sp.program_id = ?
              AND u.is_active = 1
              AND NOT EXISTS (
                SELECT 1 FROM student_fees sf
                JOIN semesters s ON sf.semester_id = s.id
                WHERE sf.student_id = sp.user_id
                  AND s.program_id = sp.program_id
                  AND s.is_current = 1
              )
        ');
        $student_stmt->execute([$program_id]);
        $students = $student_stmt->fetchAll();
        
        // Insert fee record for each student in this semester
        $insert_stmt = $db->prepare('
            INSERT IGNORE INTO student_fees (student_id, semester_id, amount_paid)
            VALUES (?, ?, \'0.00\')
        ');
        
        foreach ($students as $student) {
            $insert_stmt->execute([$student['user_id'], $semester_id]);
        }
    }
}
