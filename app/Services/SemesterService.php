<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use Throwable;

/**
 * Semester Service
 * Per Spec Part 3 Table 4: Semester management with is_current enforcement
 */
final class SemesterService
{
    /**
     * Activate a semester (set is_current=1)
     * This triggers automatic fee creation for all active students
     * 
     * Must be wrapped in PDO::beginTransaction() by caller
     * Per Spec: MySQL trigger + PHP transaction for double-enforcement
     */
    public static function activateSemester(int $semester_id): void
    {
        $db = Database::connection();
        
        // Get program_id first
        $sem_stmt = $db->prepare('SELECT program_id FROM semesters WHERE id = ?');
        $sem_stmt->execute([$semester_id]);
        $semester = $sem_stmt->fetch();
        
        if (!$semester) {
            throw new \RuntimeException('Semester not found');
        }
        
        $program_id = $semester['program_id'];
        
        // Step 1: Clear is_current for other semesters (double-enforce before UPDATE)
        $clear_stmt = $db->prepare('
            UPDATE semesters
            SET is_current = 0
            WHERE program_id = ? AND id != ?
        ');
        $clear_stmt->execute([$program_id, $semester_id]);
        
        // Step 2: Set this semester as current
        $update_stmt = $db->prepare('
            UPDATE semesters
            SET is_current = 1
            WHERE id = ?
        ');
        $update_stmt->execute([$semester_id]);
        
        // Step 3: Auto-create student fees
        StudentFeeService::createFeesOnSemesterActivation($semester_id);
    }

    /**
     * Get semester with computed 'term' field included
     * Returns formatted semester row or null
     */
    public static function getSemesterWithTerm(int $semester_id): ?array
    {
        $db = Database::connection();
        $stmt = $db->prepare('
            SELECT id, program_id, semester_number, academic_year, is_current, fee_amount, created_by, created_at
            FROM semesters
            WHERE id = ?
        ');
        $stmt->execute([$semester_id]);
        $row = $stmt->fetch();
        
        if (!$row) {
            return null;
        }
        
        // Add computed 'term' field
        $row['term'] = (((int)$row['semester_number'] % 2) === 1) ? 'Odd' : 'Even';
        return $row;
    }
}
