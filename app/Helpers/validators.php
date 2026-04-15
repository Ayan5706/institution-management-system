<?php

declare(strict_types=1);

namespace App\Helpers;

/**
 * Validation Helper Functions
 * Per Spec Part 4.4 & 7: Data validation rules
 */

/**
 * Validate academic year format: YYYY-YYYY
 * Returns error string or null on success
 * Per Spec Rule 13: year2 must equal year1 + 1
 */
function validate_academic_year(string $year_range): ?string
{
    if (!preg_match('/^(\d{4})-(\d{4})$/', $year_range, $matches)) {
        return 'Academic year format must be YYYY-YYYY (e.g., 2024-2025)';
    }
    
    $year1 = (int)$matches[1];
    $year2 = (int)$matches[2];
    
    if ($year2 !== $year1 + 1) {
        return 'Academic year second year must equal first year + 1';
    }
    
    return null;
}

/**
 * Validate program code format
 * Per Spec Rule 8: Uppercase, 2-10 chars, letters only
 * PHP enforces strtoupper() before INSERT
 */
function validate_program_code(string $code): ?string
{
    $code = strtoupper($code);
    
    if (!preg_match('/^[A-Z]{2,10}$/', $code)) {
        return 'Program code must be 2-10 uppercase letters (A-Z)';
    }
    
    return null;
}

/**
 * Validate time format HH:MM
 * Returns error string or null on success
 */
function validate_time_format(string $time): ?string
{
    if (!preg_match('/^([0-1][0-9]|2[0-3]):([0-5][0-9])$/', $time)) {
        return 'Time must be in HH:MM format (00:00 - 23:59)';
    }
    
    return null;
}

/**
 * Validate date format YYYY-MM-DD
 * Returns error string or null on success
 */
function validate_date_format(string $date): ?string
{
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        return 'Date must be in YYYY-MM-DD format';
    }
    
    // Also validate it's actually a valid date
    if (!strtotime($date)) {
        return 'Invalid date';
    }
    
    return null;
}

/**
 * Validate password strength
 * Min 8 chars, at least 1 number
 */
function validate_password_strength(string $password): ?string
{
    if (strlen($password) < 8) {
        return 'Password must be at least 8 characters';
    }
    
    if (!preg_match('/[0-9]/', $password)) {
        return 'Password must contain at least one number';
    }
    
    return null;
}

/**
 * Validate login_id format
 * Alphanumeric + underscore/hyphen, 3-50 chars
 */
function validate_login_id(string $login_id): ?string
{
    if (!preg_match('/^[a-zA-Z0-9_-]{3,50}$/', $login_id)) {
        return 'Login ID must be 3-50 chars, alphanumeric plus underscore/hyphen';
    }
    
    return null;
}

/**
 * Validate email format
 */
function validate_email(string $email): ?string
{
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'Invalid email format';
    }
    
    return null;
}

/**
 * Validate semester number is within program duration
 * Per Spec Rule 12: semester_number >= 1 AND <= program.duration_semesters
 */
function validate_semester_number(int $semester_num, int $program_duration_semesters): ?string
{
    if ($semester_num < 1) {
        return 'Semester number must be at least 1';
    }
    
    if ($semester_num > $program_duration_semesters) {
        return "Semester number cannot exceed program duration of $program_duration_semesters semesters";
    }
    
    return null;
}

/**
 * Validate phone number (basic: 7-20 digits, may include +, -, space)
 */
function validate_phone(string $phone): ?string
{
    if (!preg_match('/^[\d+\-\s()]{7,20}$/', $phone)) {
        return 'Invalid phone number format';
    }
    
    return null;
}

/**
 * Validate CSV mime type
 * Per Spec Part 4.9: Accept csv, plain text, ms-excel
 */
function validate_csv_mime_type(string $mime_type): ?string
{
    $allowed = ['text/csv', 'text/plain', 'application/vnd.ms-excel', 'text/x-csv'];
    
    if (!in_array($mime_type, $allowed)) {
        return 'CSV file must have MIME type: text/csv, text/plain, or application/vnd.ms-excel';
    }
    
    return null;
}
