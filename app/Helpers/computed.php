<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Computed Value Functions
 * Per Spec Part 4.4 & 7: All computed values that MUST NOT be stored in DB
 * These functions ensure consistency across all API responses
 */

/**
 * Compute semester term (Odd/Even)
 * Semester 1, 3, 5... = Odd
 * Semester 2, 4, 6... = Even
 */
function compute_term(int $semester_number): string
{
    return ($semester_number % 2 === 1) ? 'Odd' : 'Even';
}

/**
 * Compute pending fee amount using BCMath
 * Returns null if fee_amount not set
 * Per Spec Rule 34: Use BCMath for precision
 * $fee_amount and $amount_paid are strings (as returned by PDO DECIMAL fields)
 */
function compute_pending(?string $fee_amount, string $amount_paid): ?string
{
    if ($fee_amount === null) {
        return null;
    }
    
    return bcsub($fee_amount, $amount_paid, 2);
}

/**
 * Compute fee status based on pending amount
 * Per Spec Rule 35: "Paid", "Pending", or "Fee not set"
 */
function compute_fee_status(?string $pending): string
{
    if ($pending === null) {
        return 'Fee not set';
    }
    
    return bccomp($pending, '0.00', 2) === 0 ? 'Paid' : 'Pending';
}

/**
 * Format semester response: add computed 'term' field
 * Appends 'term' key to the semester row array
 */
function format_semester_response(array $row): array
{
    if (isset($row['semester_number'])) {
        $row['term'] = compute_term((int)$row['semester_number']);
    }
    return $row;
}

/**
 * Format student fee response: add computed 'pending_amount' and 'fee_status' fields
 * Appends 'pending_amount' and 'fee_status' keys to the fee row array
 * $fee_amount_from_semester: fee amount from the associated semester, or null if not set
 */
function format_student_fee_response(array $row, ?string $fee_amount_from_semester = null): array
{
    $fee_amount = $fee_amount_from_semester ?? ($row['fee_amount'] ?? null);
    $amount_paid = $row['amount_paid'] ?? '0.00';
    
    $row['pending_amount'] = compute_pending($fee_amount, $amount_paid);
    $row['fee_status'] = compute_fee_status($row['pending_amount']);
    
    return $row;
}
