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
     * Creates StudentFee rows for all currently active semesters (is_current=1) 
     * in the student's program
     * 
     * Must be called within a PDO transaction
     * Per Spec: "After PHP INSERTs a student_profile row..."
     */
    public static function createFeesForNewStudent(int $student_id, int $program_id): void
    {
        $db = Database::connection();
        
        // Get all active semesters for this program
        $stmt = $db->prepare('
            SELECT id, fee_amount
            FROM semesters
            WHERE program_id = ? AND is_current = 1
        ');
        $stmt->execute([$program_id]);
        $semesters = $stmt->fetchAll();
        
        // Insert a fee record for each active semester
        $insert_stmt = $db->prepare('
            INSERT INTO student_fees (student_id, semester_id, amount_paid)
            VALUES (?, ?, \'0.00\')
            ON DUPLICATE KEY UPDATE id=LAST_INSERT_ID(id)
        ');
        
        foreach ($semesters as $semester) {
            $insert_stmt->execute([$student_id, $semester['id']]);
        }
    }

    /**
     * Create fee records for all active students when a semester is activated
     * Creates StudentFee rows for all active students (is_active=1) 
     * in the semester's program, IF no fee record already exists for that pair
     * 
     * Must be called within a PDO transaction
     * Per Spec: "When semester is activated (is_current set to 1)..."
     */
    public static function createFeesOnSemesterActivation(int $semester_id): void
    {
        $db = Database::connection();
        
        // Get program_id from semester
        $sem_stmt = $db->prepare('SELECT program_id FROM semesters WHERE id = ?');
        $sem_stmt->execute([$semester_id]);
        $semester = $sem_stmt->fetch();
        
        if (!$semester) {
            return;
        }
        
        $program_id = $semester['program_id'];
        
        // Get all active students in this program
        $student_stmt = $db->prepare('
            SELECT sp.user_id
            FROM student_profiles sp
            JOIN users u ON sp.user_id = u.id
            WHERE sp.program_id = ? AND u.is_active = 1
        ');
        $student_stmt->execute([$program_id]);
        $students = $student_stmt->fetchAll();
        
        // Insert fee record for each student IF it doesn't exist
        $insert_stmt = $db->prepare('
            INSERT IGNORE INTO student_fees (student_id, semester_id, amount_paid)
            VALUES (?, ?, \'0.00\')
        ');
        
        foreach ($students as $student) {
            $insert_stmt->execute([$student['user_id'], $semester_id]);
        }
    }
}
