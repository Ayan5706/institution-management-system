<?php

/**
 * Comprehensive Validation Rules
 * 
 * Defines validation rules for all major entities in the IMS application.
 * These rules can be used by the Validator class to ensure data integrity.
 */

return [
    // ===== USER VALIDATION =====
    'user.create' => [
        'name' => 'required|min:3|max:100|string',
        'email' => 'required|email|unique:users,email',
        'password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/',
        'password_confirm' => 'required|same:password',
        'role' => 'required|in:admin,principal,teacher,student,staff',
        'status' => 'in:active,inactive,suspended',
        'phone' => 'nullable|regex:/^[0-9]{10,15}$/',
    ],

    'user.update' => [
        'name' => 'required|min:3|max:100|string',
        'email' => 'required|email|unique:users,email,{id}',
        'role' => 'required|in:admin,principal,teacher,student,staff',
        'status' => 'in:active,inactive,suspended',
        'phone' => 'nullable|regex:/^[0-9]{10,15}$/',
    ],

    'user.password_change' => [
        'current_password' => 'required|password',
        'new_password' => 'required|min:8|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/',
        'password_confirm' => 'required|same:new_password',
    ],

    'user.login' => [
        'email' => 'required|email',
        'password' => 'required|min:6',
        'remember' => 'boolean',
    ],

    // ===== STUDENT PROFILE VALIDATION =====
    'student.create' => [
        'user_id' => 'required|exists:users,id',
        'roll_number' => 'required|unique:student_profiles,roll_number|alpha_numeric|max:20',
        'batch' => 'required|integer|min:2020|max:' . (date('Y') + 10),
        'program_id' => 'required|exists:programs,id',
        'semester_id' => 'required|exists:semesters,id',
        'status' => 'in:active,graduated,suspended,inactive',
    ],

    'student.update' => [
        'roll_number' => 'required|alpha_numeric|max:20|unique:student_profiles,roll_number,{id}',
        'batch' => 'required|integer|min:2020',
        'program_id' => 'required|exists:programs,id',
        'semester_id' => 'required|exists:semesters,id',
        'status' => 'in:active,graduated,suspended,inactive',
    ],

    // ===== PROGRAM VALIDATION =====
    'program.create' => [
        'name' => 'required|string|max:100|unique:programs,name',
        'code' => 'required|alpha|max:10|unique:programs,code',
        'duration_years' => 'required|integer|min:1|max:6',
        'description' => 'nullable|string|max:500',
        'status' => 'in:active,inactive,archived',
    ],

    'program.update' => [
        'name' => 'required|string|max:100|unique:programs,name,{id}',
        'code' => 'required|alpha|max:10|unique:programs,code,{id}',
        'duration_years' => 'required|integer|min:1|max:6',
        'description' => 'nullable|string|max:500',
        'status' => 'in:active,inactive,archived',
    ],

    // ===== SEMESTER VALIDATION =====
    'semester.create' => [
        'program_id' => 'required|exists:programs,id',
        'name' => 'required|string|max:100',
        'number' => 'required|integer|min:1|max:8',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'status' => 'in:planning,active,completed,cancelled',
    ],

    'semester.update' => [
        'name' => 'required|string|max:100',
        'number' => 'required|integer|min:1|max:8',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after:start_date',
        'status' => 'in:planning,active,completed,cancelled',
    ],

    // ===== SUBJECT VALIDATION =====
    'subject.create' => [
        'code' => 'required|alpha_numeric|max:20|unique:subjects,code',
        'name' => 'required|string|max:100',
        'credits' => 'required|integer|min:1|max:6',
        'program_id' => 'required|exists:programs,id',
        'semester_id' => 'required|exists:semesters,id',
        'description' => 'nullable|string|max:500',
        'status' => 'in:active,inactive,archived',
    ],

    'subject.update' => [
        'code' => 'required|alpha_numeric|max:20|unique:subjects,code,{id}',
        'name' => 'required|string|max:100',
        'credits' => 'required|integer|min:1|max:6',
        'program_id' => 'required|exists:programs,id',
        'semester_id' => 'required|exists:semesters,id',
        'description' => 'nullable|string|max:500',
        'status' => 'in:active,inactive,archived',
    ],

    // ===== TEACHER ASSIGNMENT VALIDATION =====
    'teacher_assignment.create' => [
        'teacher_id' => 'required|exists:users,id',
        'subject_id' => 'required|exists:subjects,id',
        'program_id' => 'required|exists:programs,id',
        'semester_id' => 'required|exists:semesters,id',
        'status' => 'in:active,inactive',
    ],

    'teacher_assignment.update' => [
        'teacher_id' => 'required|exists:users,id',
        'subject_id' => 'required|exists:subjects,id',
        'status' => 'in:active,inactive',
    ],

    // ===== TIMETABLE VALIDATION =====
    'timetable.create' => [
        'subject_id' => 'required|exists:subjects,id',
        'teacher_id' => 'required|exists:users,id',
        'day' => 'required',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'room_number' => 'required|string|max:20',
        'status' => 'in:active,cancelled',
    ],

    'timetable.update' => [
        'day' => 'required',
        'start_time' => 'required|date_format:H:i',
        'end_time' => 'required|date_format:H:i|after:start_time',
        'room_number' => 'required|string|max:20',
        'status' => 'in:active,cancelled',
    ],

    // ===== ATTENDANCE VALIDATION =====
    'attendance.create' => [
        'student_id' => 'required|exists:student_profiles,id',
        'subject_id' => 'required|exists:subjects,id',
        'date' => 'required|date',
        'status' => 'required|in:present,absent,late,excused',
        'remarks' => 'nullable|string|max:200',
    ],

    'attendance.update' => [
        'status' => 'required|in:present,absent,late,excused',
        'remarks' => 'nullable|string|max:200',
    ],

    // ===== FEE VALIDATION =====
    'fee.create' => [
        'student_id' => 'required|exists:student_profiles,id',
        'semester_id' => 'required|exists:semesters,id',
        'amount' => 'required|numeric|min:0|decimal:2',
        'due_date' => 'required|date|after:today',
        'description' => 'nullable|string|max:200',
    ],

    'fee.update' => [
        'amount' => 'required|numeric|min:0|decimal:2',
        'due_date' => 'required|date',
        'description' => 'nullable|string|max:200',
    ],

    'fee.payment' => [
        'amount' => 'required|numeric|min:0|decimal:2|max_fee_remaining',
        'payment_date' => 'required|date|before_or_equal:today',
        'payment_method' => 'required|in:cash,cheque,transfer,card',
        'reference_number' => 'required|string|max:50',
    ],

    // ===== FILE UPLOAD VALIDATION =====
    'file.upload' => [
        'file' => 'required|file|max:5120|mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png',
        'description' => 'nullable|string|max:200',
        'category' => 'required|in:document,image,other',
    ],

    'avatar.upload' => [
        'avatar' => 'required|image|max:2048|dimensions:min_width=100,min_height=100',
    ],

    // ===== REPORT VALIDATION =====
    'report.academic' => [
        'program_id' => 'nullable|exists:programs,id',
        'semester_id' => 'nullable|exists:semesters,id',
        'start_date' => 'nullable|date',
        'end_date' => 'nullable|date|after_or_equal:start_date',
        'format' => 'in:html,pdf,csv',
    ],

    'report.attendance' => [
        'subject_id' => 'required|exists:subjects,id',
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'format' => 'in:html,pdf,csv',
    ],

    'report.financial' => [
        'semester_id' => 'required|exists:semesters,id',
        'status' => 'in:pending,paid,partially_paid',
        'format' => 'in:html,pdf,csv',
    ],

    // ===== SEARCH VALIDATION =====
    'search.user' => [
        'query' => 'required|string|min:2|max:100',
        'role' => 'nullable|in:admin,principal,teacher,student,staff',
        'status' => 'nullable|in:active,inactive,suspended',
    ],

    'search.student' => [
        'query' => 'required|string|min:2|max:100',
        'program_id' => 'nullable|exists:programs,id',
        'batch_year' => 'nullable|integer',
        'status' => 'nullable|in:active,graduated,suspended',
    ],

    'search.attendance' => [
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'subject_id' => 'nullable|exists:subjects,id',
        'status' => 'nullable|in:present,absent,late,excused',
    ],

    // ===== BATCH OPERATIONS =====
    'batch.attendance_mark' => [
        'attendance_data' => 'required|array',
        'attendance_data.*.student_id' => 'required|exists:student_profiles,id',
        'attendance_data.*.status' => 'required|in:present,absent,late,excused',
    ],

    'batch.fee_generate' => [
        'semester_id' => 'required|exists:semesters,id',
        'amount' => 'required|numeric|min:0|decimal:2',
        'due_date' => 'required|date',
    ],
];
