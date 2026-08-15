<?php

declare(strict_types=1);

use App\Controllers\HomeController;
use App\Controllers\AuthController;
use App\Controllers\ActivationController;
use App\Controllers\DashboardController;
use App\Controllers\PrincipalController;
use App\Controllers\ReportController;
use App\Controllers\UserController;
use App\Controllers\ProgramController;
use App\Controllers\SemesterController;
use App\Controllers\SubjectController;
use App\Controllers\StudentProfileController;
use App\Controllers\TeacherAssignmentController;
use App\Controllers\TimetableController;
use App\Controllers\AttendanceController;
use App\Controllers\StudentFeeController;
use App\Controllers\UploadController;
use App\Controllers\AdminController;
use App\Controllers\ManagerController;
use App\Controllers\AccountantController;
use App\Controllers\TeacherController;
use App\Controllers\StudentController;
use App\Middleware\AuthMiddleware;
use App\Middleware\GuestMiddleware;
use App\Middleware\RoleMiddleware;

// ============================================================================
// MIDDLEWARE ALIASES
// ============================================================================

$router->alias('auth', AuthMiddleware::class);
$router->alias('guest', GuestMiddleware::class);
$router->alias('role', RoleMiddleware::class);

// ============================================================================
// HOME / LANDING ROUTE
// ============================================================================

$router->get('/', HomeController::class . '@landing', ['guest']);

// ============================================================================
// AUTHENTICATION ROUTES
// ============================================================================

$router->get('/login', AuthController::class . '@showLogin', ['guest']);
$router->post('/login', AuthController::class . '@login', ['guest']);
$router->post('/logout', AuthController::class . '@logout', ['auth']);
$router->get('/change-password', AuthController::class . '@showChangePassword', ['auth']);
$router->post('/change-password', AuthController::class . '@changePassword', ['auth']);
$router->get('/forgot-password', AuthController::class . '@showForgotPassword', ['guest']);
$router->post('/forgot-password', AuthController::class . '@sendPasswordReset', ['guest']);
$router->get('/verify-password-reset-email', AuthController::class . '@verifyPasswordResetEmail', ['guest']);
$router->get('/reset-password', AuthController::class . '@showResetPassword', ['guest']);
$router->post('/reset-password', AuthController::class . '@resetPassword', ['guest']);
$router->get('/activate/{token}', ActivationController::class . '@show', ['guest']);
$router->post('/activate/{token}', ActivationController::class . '@activate', ['guest']);

// ============================================================================
// DASHBOARD ROUTE (Authenticated)
// ============================================================================

$router->get('/dashboard', DashboardController::class . '@index', ['auth']);

// ============================================================================
// REPORT ROUTES
// ============================================================================

$router->get('/reports', ReportController::class . '@index', ['auth', 'role:admin,principal,vp,manager,accountant']);
$router->get('/reports/academic', ReportController::class . '@academic', ['auth', 'role:admin,principal,vp,manager']);
$router->get('/reports/attendance', ReportController::class . '@attendance', ['auth', 'role:admin,principal,vp,manager']);
$router->get('/reports/finance', ReportController::class . '@finance', ['auth', 'role:admin,principal,accountant']);
$router->get('/reports/export', ReportController::class . '@export', ['auth', 'role:admin,principal,vp,manager']);

// ============================================================================
// USER MANAGEMENT ROUTES (Admin Only)
// ============================================================================

$router->get('/users', UserController::class . '@index', ['auth', 'role:admin']);
$router->get('/users/create', UserController::class . '@create', ['auth', 'role:admin']);
$router->get('/users/{id}', UserController::class . '@show', ['auth', 'role:admin']);
$router->get('/users/{id}/edit', UserController::class . '@edit', ['auth', 'role:admin']);
$router->post('/users', UserController::class . '@store', ['auth', 'role:admin']);
$router->post('/users/{id}', UserController::class . '@update', ['auth', 'role:admin']);
$router->delete('/users/{id}', UserController::class . '@destroy', ['auth', 'role:admin']);
$router->post('/users/{id}/resend-activation', UserController::class . '@resendActivation', ['auth', 'role:admin']);

// ============================================================================
// PROGRAM ROUTES
// ============================================================================

$router->get('/programs', ProgramController::class . '@index', ['auth']);
$router->get('/programs/create', ProgramController::class . '@create', ['auth', 'role:admin']);
$router->get('/programs/{id}', ProgramController::class . '@show', ['auth']);
$router->get('/programs/{id}/edit', ProgramController::class . '@edit', ['auth', 'role:admin']);
$router->post('/programs', ProgramController::class . '@store', ['auth', 'role:admin']);
$router->post('/programs/{id}', ProgramController::class . '@update', ['auth', 'role:admin']);
$router->delete('/programs/{id}', ProgramController::class . '@destroy', ['auth', 'role:admin']);

// ============================================================================
// SEMESTER ROUTES
// ============================================================================

$router->get('/semesters', SemesterController::class . '@index', ['auth']);
$router->get('/semesters/create', SemesterController::class . '@create', ['auth', 'role:admin']);
$router->get('/semesters/{id}', SemesterController::class . '@show', ['auth']);
$router->get('/semesters/{id}/edit', SemesterController::class . '@edit', ['auth', 'role:admin']);
$router->post('/semesters', SemesterController::class . '@store', ['auth', 'role:admin']);
$router->post('/semesters/{id}', SemesterController::class . '@update', ['auth', 'role:admin']);
$router->delete('/semesters/{id}', SemesterController::class . '@destroy', ['auth', 'role:admin']);

// ============================================================================
// SUBJECT ROUTES
// ============================================================================

$router->get('/subjects', SubjectController::class . '@index', ['auth']);
$router->get('/subjects/create', SubjectController::class . '@create', ['auth', 'role:admin']);
$router->get('/subjects/{id}', SubjectController::class . '@show', ['auth']);
$router->get('/subjects/{id}/edit', SubjectController::class . '@edit', ['auth', 'role:admin']);
$router->post('/subjects', SubjectController::class . '@store', ['auth', 'role:admin']);
$router->post('/subjects/{id}', SubjectController::class . '@update', ['auth', 'role:admin']);
$router->delete('/subjects/{id}', SubjectController::class . '@destroy', ['auth', 'role:admin']);

// ============================================================================
// STUDENT PROFILE ROUTES
// ============================================================================

$router->get('/students', StudentProfileController::class . '@index', ['auth']);
$router->get('/students/create', StudentProfileController::class . '@create', ['auth', 'role:admin']);
$router->get('/students/{id}', StudentProfileController::class . '@show', ['auth']);
$router->get('/students/{id}/edit', StudentProfileController::class . '@edit', ['auth', 'role:admin']);
$router->post('/students', StudentProfileController::class . '@store', ['auth', 'role:admin']);
$router->post('/students/{id}', StudentProfileController::class . '@update', ['auth', 'role:admin']);
$router->delete('/students/{id}', StudentProfileController::class . '@destroy', ['auth', 'role:admin']);

// ============================================================================
// TEACHER ASSIGNMENT ROUTES
// ============================================================================

$router->get('/teacher-assignments', TeacherAssignmentController::class . '@index', ['auth']);
$router->get('/teacher-assignments/create', TeacherAssignmentController::class . '@create', ['auth', 'role:admin']);
$router->get('/teacher-assignments/{id}', TeacherAssignmentController::class . '@show', ['auth']);
$router->get('/teacher-assignments/{id}/edit', TeacherAssignmentController::class . '@edit', ['auth', 'role:admin']);
$router->post('/teacher-assignments', TeacherAssignmentController::class . '@store', ['auth', 'role:admin']);
$router->post('/teacher-assignments/{id}', TeacherAssignmentController::class . '@update', ['auth', 'role:admin']);
$router->delete('/teacher-assignments/{id}', TeacherAssignmentController::class . '@destroy', ['auth', 'role:admin']);

// ============================================================================
// TIMETABLE ROUTES
// ============================================================================

$router->get('/timetables', TimetableController::class . '@index', ['auth']);
$router->get('/timetables/create', TimetableController::class . '@create', ['auth', 'role:admin']);
$router->get('/timetables/{id}', TimetableController::class . '@show', ['auth']);
$router->get('/timetables/{id}/edit', TimetableController::class . '@edit', ['auth', 'role:admin']);
$router->post('/timetables', TimetableController::class . '@store', ['auth', 'role:admin']);
$router->post('/timetables/{id}', TimetableController::class . '@update', ['auth', 'role:admin']);
$router->delete('/timetables/{id}', TimetableController::class . '@destroy', ['auth', 'role:admin']);

// ============================================================================
// ATTENDANCE ROUTES
// ============================================================================

$router->get('/attendance', AttendanceController::class . '@index', ['auth']);
$router->get('/attendance/create', AttendanceController::class . '@create', ['auth', 'role:teacher,admin']);
$router->get('/attendance/{id}', AttendanceController::class . '@show', ['auth']);
$router->get('/attendance/{id}/edit', AttendanceController::class . '@edit', ['auth', 'role:teacher,admin']);
$router->post('/attendance', AttendanceController::class . '@store', ['auth', 'role:teacher,admin']);
$router->post('/attendance/{id}', AttendanceController::class . '@update', ['auth', 'role:teacher,admin']);
$router->delete('/attendance/{id}', AttendanceController::class . '@destroy', ['auth', 'role:teacher,admin']);

// ============================================================================
// STUDENT FEE ROUTES
// ============================================================================

$router->get('/fees', StudentFeeController::class . '@index', ['auth']);
$router->get('/fees/create', StudentFeeController::class . '@create', ['auth', 'role:admin']);
$router->get('/fees/{id}', StudentFeeController::class . '@show', ['auth']);
$router->get('/fees/{id}/edit', StudentFeeController::class . '@edit', ['auth', 'role:admin']);
$router->post('/fees', StudentFeeController::class . '@store', ['auth', 'role:admin']);
$router->post('/fees/{id}', StudentFeeController::class . '@update', ['auth', 'role:admin']);
$router->delete('/fees/{id}', StudentFeeController::class . '@destroy', ['auth', 'role:admin']);

// ============================================================================
// FILE UPLOAD & DOWNLOAD ROUTES (API)
// ============================================================================

$router->post('/api/upload/avatar', UploadController::class . '@uploadAvatar', ['auth']);
$router->post('/api/upload/document', UploadController::class . '@uploadDocument', ['auth', 'role:teacher,admin']);
$router->post('/api/upload/product-image', UploadController::class . '@uploadProductImage', ['auth', 'role:admin']);
$router->post('/api/upload/product-document', UploadController::class . '@uploadProductDocument', ['auth', 'role:admin']);
$router->delete('/api/uploads/{type}/{id}', UploadController::class . '@deleteFile', ['auth']);
$router->get('/api/downloads/{type}/{filename}', UploadController::class . '@download', ['auth']);

// ============================================================================
// ADMIN PANEL ROUTES
// ============================================================================

$router->get('/admin', AdminController::class . '@dashboard', ['auth', 'role:admin']);
$router->get('/admin/config', AdminController::class . '@showConfig', ['auth', 'role:admin']);
$router->post('/admin/config', AdminController::class . '@updateConfig', ['auth', 'role:admin']);
$router->get('/admin/system-health', AdminController::class . '@systemHealth', ['auth', 'role:admin']);
$router->post('/admin/cleanup', AdminController::class . '@cleanupFiles', ['auth', 'role:admin']);

// ============================================================================
// PRINCIPAL ROUTES
// ============================================================================

// Principal Dashboard & Overview
$router->get('/principal/dashboard', PrincipalController::class . '@showDashboard', ['auth', 'role:principal']);
$router->get('/principal', PrincipalController::class . '@showDashboard', ['auth', 'role:principal']); // Backward compatibility alias

// Principal Account Management
$router->get('/principal/accounts', PrincipalController::class . '@showAccounts', ['auth', 'role:principal']);
$router->get('/principal/accounts/create', PrincipalController::class . '@createAccountForm', ['auth', 'role:principal']);
$router->get('/principal/accounts/next-login-id', PrincipalController::class . '@getNextLoginId', ['auth', 'role:principal']);
$router->post('/principal/accounts', PrincipalController::class . '@storeAccount', ['auth', 'role:principal']);
$router->patch('/principal/accounts/{id}/toggle', PrincipalController::class . '@toggleAccountStatus', ['auth', 'role:principal']);

// Principal Student Management (Read-Only)
$router->get('/principal/students', PrincipalController::class . '@showStudents', ['auth', 'role:principal']);
$router->get('/principal/students/{id}', PrincipalController::class . '@showStudentDetail', ['auth', 'role:principal']);

// Principal Teacher Management (Read-Only)
$router->get('/principal/teachers', PrincipalController::class . '@showTeachers', ['auth', 'role:principal']);
$router->get('/principal/teachers/{id}', PrincipalController::class . '@showTeacherDetail', ['auth', 'role:principal']);

// Principal Profile
$router->get('/principal/profile', PrincipalController::class . '@showProfile', ['auth', 'role:principal']);
$router->post('/principal/profile', PrincipalController::class . '@updateProfile', ['auth', 'role:principal']);
$router->post('/principal/profile/email', PrincipalController::class . '@updateEmail', ['auth', 'role:principal']);
$router->post('/principal/profile/email/verify-otp', PrincipalController::class . '@verifyEmailChangeOtp', ['auth', 'role:principal']);

// Principal System Configuration
$router->get('/principal/config', PrincipalController::class . '@showConfig', ['auth', 'role:principal']);
$router->patch('/principal/config/{key}', PrincipalController::class . '@updateConfig', ['auth', 'role:principal']);

// Principal Password Reset Approvals
$router->get('/principal/password-resets', PrincipalController::class . '@showPasswordResets', ['auth', 'role:principal']);
$router->post('/principal/password-resets/{id}/approve', PrincipalController::class . '@approvePasswordReset', ['auth', 'role:principal']);
$router->post('/principal/password-resets/{id}/reject', PrincipalController::class . '@rejectPasswordReset', ['auth', 'role:principal']);

// Principal Email Change Approvals
$router->get('/principal/email-requests', PrincipalController::class . '@showEmailChangeRequests', ['auth', 'role:principal']);
$router->post('/principal/email-requests/{id}/approve', PrincipalController::class . '@approveEmailChange', ['auth', 'role:principal']);
$router->post('/principal/email-requests/{id}/reject', PrincipalController::class . '@rejectEmailChange', ['auth', 'role:principal']);


// Principal API Endpoints
$router->get('/api/principal/dashboard', PrincipalController::class . '@apiDashboard', ['auth', 'role:principal']);
$router->get('/api/principal/users', PrincipalController::class . '@apiGetAdminUsers', ['auth', 'role:principal']);
$router->get('/api/principal/students', PrincipalController::class . '@apiGetStudents', ['auth', 'role:principal']);
$router->get('/api/principal/teachers', PrincipalController::class . '@apiGetTeachers', ['auth', 'role:principal']);
$router->get('/api/principal/password-resets', PrincipalController::class . '@apiGetPasswordResets', ['auth', 'role:principal']);
$router->get('/api/principal/email-requests', PrincipalController::class . '@apiGetEmailChangeRequests', ['auth', 'role:principal']);

// Vice Principal API Endpoints
$router->get('/api/vp/dashboard', VPController::class . '@apiDashboard', ['auth', 'role:vp']);
$router->get('/api/vp/password-resets', VPController::class . '@apiGetPasswordResets', ['auth', 'role:vp']);
$router->get('/api/vp/email-requests', VPController::class . '@apiGetEmailChangeRequests', ['auth', 'role:vp']);

// ============================================================================
// VICE PRINCIPAL ROUTES
// ============================================================================

use App\Controllers\VPController;

// Vice Principal Dashboard & Overview
$router->get('/vp/dashboard', VPController::class . '@showDashboard', ['auth', 'role:vp']);
$router->get('/vp', VPController::class . '@showDashboard', ['auth', 'role:vp']); // Backward compatibility alias

// Vice Principal Profile
$router->get('/vp/profile', VPController::class . '@showProfile', ['auth', 'role:vp']);
$router->post('/vp/profile', VPController::class . '@updateProfile', ['auth', 'role:vp']);
$router->post('/vp/profile/email', VPController::class . '@requestEmailChange', ['auth', 'role:vp']);
$router->post('/vp/profile/email/verify-otp', VPController::class . '@verifyEmailChangeOtp', ['auth', 'role:vp']);

// Vice Principal Program Management
$router->get('/vp/programs', VPController::class . '@showPrograms', ['auth', 'role:vp']);
$router->post('/vp/programs', VPController::class . '@createProgram', ['auth', 'role:vp']);

// Vice Principal Semester Management
$router->get('/vp/semesters', VPController::class . '@showSemesters', ['auth', 'role:vp']);
$router->post('/vp/semesters', VPController::class . '@createSemester', ['auth', 'role:vp']);
$router->post('/vp/semesters/{id}/activate', VPController::class . '@activateSemester', ['auth', 'role:vp']);
$router->post('/vp/semesters/toggle-term', VPController::class . '@toggleSemesterTerm', ['auth', 'role:vp']);

// Vice Principal Subject Management
$router->get('/vp/subjects', VPController::class . '@showSubjects', ['auth', 'role:vp']);
$router->post('/vp/subjects', VPController::class . '@createSubject', ['auth', 'role:vp']);

// Vice Principal Teacher Management
$router->get('/vp/teachers', VPController::class . '@showTeachers', ['auth', 'role:vp']);
$router->get('/vp/teachers/next-login-id', VPController::class . '@getNextTeacherLoginId', ['auth', 'role:vp']);
$router->get('/vp/teachers/{id}', VPController::class . '@showTeacherDetail', ['auth', 'role:vp']);
$router->post('/vp/teachers', VPController::class . '@createTeacher', ['auth', 'role:vp']);

// Vice Principal Assignment Management
$router->get('/vp/assignments', VPController::class . '@showAssignments', ['auth', 'role:vp']);
$router->post('/vp/assignments', VPController::class . '@createAssignment', ['auth', 'role:vp']);
$router->delete('/vp/assignments/{id}', VPController::class . '@deleteAssignment', ['auth', 'role:vp']);

// Vice Principal Timetable Management
$router->get('/vp/timetable', VPController::class . '@showTimetable', ['auth', 'role:vp']);
$router->post('/vp/timetable', VPController::class . '@createTimetable', ['auth', 'role:vp']);
$router->delete('/vp/timetable/{id}', VPController::class . '@deleteTimetable', ['auth', 'role:vp']);

// Vice Principal Password Reset Approvals
$router->get('/vp/password-requests', VPController::class . '@showPasswordRequests', ['auth', 'role:vp']);
$router->post('/vp/password-requests/{id}/approve', VPController::class . '@approvePasswordReset', ['auth', 'role:vp']);
$router->post('/vp/password-requests/{id}/reject', VPController::class . '@rejectPasswordReset', ['auth', 'role:vp']);

// Vice Principal Email Change Requests
$router->get('/vp/email-requests', VPController::class . '@showEmailChangeRequests', ['auth', 'role:vp']);
$router->post('/vp/email-requests/{id}/approve', VPController::class . '@approveEmailChange', ['auth', 'role:vp']);
$router->post('/vp/email-requests/{id}/reject', VPController::class . '@rejectEmailChange', ['auth', 'role:vp']);

// ============================================================================
// MANAGER ROUTES
// ============================================================================

// Manager Dashboard & Overview
$router->get('/manager/dashboard', ManagerController::class . '@showDashboard', ['auth', 'role:manager']);
$router->get('/manager', ManagerController::class . '@showDashboard', ['auth', 'role:manager']); // Backward compatibility alias

// Manager Student Management
$router->get('/manager/students', ManagerController::class . '@showStudents', ['auth', 'role:manager']);

// Manager Promotions
$router->get('/manager/promotions', ManagerController::class . '@showPromotions', ['auth', 'role:manager']);

// Manager CSV Upload (MUST come before {id} route to avoid being matched as ID)
$router->get('/manager/students/csv-upload', ManagerController::class . '@showCsvUpload', ['auth', 'role:manager']);
$router->post('/api/manager/csv-upload', ManagerController::class . '@processCsvUpload', ['auth', 'role:manager']);
$router->post('/api/manager/csv-confirm', ManagerController::class . '@confirmCsvImport', ['auth', 'role:manager']);

// Manager Student Detail (generic {id} route comes AFTER specific routes)
$router->get('/manager/students/{id}', ManagerController::class . '@showStudentDetail', ['auth', 'role:manager']);
$router->post('/api/manager/students', ManagerController::class . '@createStudent', ['auth', 'role:manager']);
$router->patch('/api/manager/students/{id}/toggle', ManagerController::class . '@toggleStudentStatus', ['auth', 'role:manager']);

// Manager Student Password Reset Approvals
$router->get('/manager/password-resets', ManagerController::class . '@showPasswordResets', ['auth', 'role:manager']);
$router->post('/api/manager/reset-requests/{id}/approve', ManagerController::class . '@approvePasswordReset', ['auth', 'role:manager']);
$router->post('/api/manager/reset-requests/{id}/reject', ManagerController::class . '@rejectPasswordReset', ['auth', 'role:manager']);

// Manager Email Change Requests
$router->get('/manager/email-requests', ManagerController::class . '@showEmailChangeRequests', ['auth', 'role:manager']);
$router->post('/manager/email-requests/{id}/approve', ManagerController::class . '@approveEmailChange', ['auth', 'role:manager']);
$router->post('/manager/email-requests/{id}/reject', ManagerController::class . '@rejectEmailChange', ['auth', 'role:manager']);

// Manager Profile
$router->get('/manager/profile', ManagerController::class . '@showProfile', ['auth', 'role:manager']);
$router->post('/manager/profile', ManagerController::class . '@updateProfile', ['auth', 'role:manager']);
$router->post('/manager/profile/email', ManagerController::class . '@requestEmailChange', ['auth', 'role:manager']);
$router->post('/manager/profile/email/verify-otp', ManagerController::class . '@verifyEmailChangeOtp', ['auth', 'role:manager']);

// Manager API Endpoints
$router->get('/api/manager/dashboard', ManagerController::class . '@apiDashboard', ['auth', 'role:manager']);
$router->get('/api/manager/students', ManagerController::class . '@apiGetStudents', ['auth', 'role:manager']);
$router->get('/api/manager/reset-requests', ManagerController::class . '@apiGetStudentResetRequests', ['auth', 'role:manager']);
$router->get('/api/manager/email-requests', ManagerController::class . '@apiGetEmailChangeRequests', ['auth', 'role:manager']);
$router->get('/api/manager/promotions', ManagerController::class . '@apiGetPromotionLists', ['auth', 'role:manager']);
$router->post('/api/manager/promotions/promote', ManagerController::class . '@apiPromoteStudents', ['auth', 'role:manager']);
$router->post('/api/manager/promotions/remind', ManagerController::class . '@apiRemindStudents', ['auth', 'role:manager']);

// ============================================================================
// ACCOUNTANT ROUTES
// ============================================================================

// Accountant Dashboard
$router->get('/accountant/dashboard', AccountantController::class . '@showDashboard', ['auth', 'role:accountant']);
$router->get('/accountant', AccountantController::class . '@showDashboard', ['auth', 'role:accountant']); // Backward compatibility alias

// Accountant Fees Management
$router->get('/accountant/fees', AccountantController::class . '@showFees', ['auth', 'role:accountant']);
$router->get('/accountant/semester-fees', AccountantController::class . '@showSemesterFees', ['auth', 'role:accountant']);
$router->get('/accountant/student-fees', AccountantController::class . '@showStudentFees', ['auth', 'role:accountant']);

// Accountant Profile
$router->get('/accountant/profile', AccountantController::class . '@showProfile', ['auth', 'role:accountant']);
$router->post('/accountant/profile', AccountantController::class . '@updateProfile', ['auth', 'role:accountant']);
$router->post('/accountant/profile/email', AccountantController::class . '@requestEmailChange', ['auth', 'role:accountant']);
$router->post('/accountant/profile/email/verify-otp', AccountantController::class . '@verifyEmailChangeOtp', ['auth', 'role:accountant']);

// Accountant API Endpoints
$router->get('/api/accountant/programs', AccountantController::class . '@apiGetPrograms', ['auth', 'role:accountant']);
$router->get('/api/accountant/program/{id}/semesters', AccountantController::class . '@apiGetProgramSemesters', ['auth', 'role:accountant']);
$router->get('/api/accountant/student-fees', AccountantController::class . '@apiGetFilteredStudentFees', ['auth', 'role:accountant']);
$router->get('/api/accountant/semesters', AccountantController::class . '@apiGetSemesters', ['auth', 'role:accountant']);
$router->get('/api/accountant/semester/{id}/fees', AccountantController::class . '@apiGetStudentFees', ['auth', 'role:accountant']);
$router->patch('/api/accountant/semester/{id}/fee-amount', AccountantController::class . '@updateSemesterFeeAmount', ['auth', 'role:accountant']);
$router->patch('/api/accountant/fee/{id}/payment', AccountantController::class . '@updateStudentPayment', ['auth', 'role:accountant']);

// ============================================================================
// TEACHER ROUTES
// ============================================================================

// Teacher Dashboard
$router->get('/teacher/dashboard', TeacherController::class . '@showDashboard', ['auth', 'role:teacher']);
$router->get('/teacher', TeacherController::class . '@showDashboard', ['auth', 'role:teacher']); // Backward compatibility alias

// Teacher Attendance Marking
$router->get('/teacher/attendance', TeacherController::class . '@showAttendanceSessions', ['auth', 'role:teacher']);
$router->get('/teacher/attendance/mark/{slotId}', TeacherController::class . '@showMarkAttendance', ['auth', 'role:teacher']);

// Teacher Attendance History
$router->get('/teacher/attendance/history', TeacherController::class . '@showAttendanceHistory', ['auth', 'role:teacher']);

// Teacher Timetable + Students
$router->get('/teacher/timetable', TeacherController::class . '@showTimetable', ['auth', 'role:teacher']);
$router->get('/teacher/students', TeacherController::class . '@showStudents', ['auth', 'role:teacher']);
$router->get('/teacher/students/{id}', TeacherController::class . '@showStudentDetail', ['auth', 'role:teacher']);

// Teacher Profile
$router->get('/teacher/profile', TeacherController::class . '@showProfile', ['auth', 'role:teacher']);
$router->post('/teacher/profile', TeacherController::class . '@updateProfile', ['auth', 'role:teacher']);
$router->post('/teacher/profile/email', TeacherController::class . '@requestEmailChange', ['auth', 'role:teacher']);
$router->post('/teacher/profile/email/verify-otp', TeacherController::class . '@verifyEmailChangeOtp', ['auth', 'role:teacher']);

// Teacher API Endpoints
$router->post('/api/teacher/attendance/{slotId}/submit', TeacherController::class . '@submitAttendance', ['auth', 'role:teacher']);

// ============================================================================
// STUDENT ROUTES
// ============================================================================

// Student Dashboard
$router->get('/student/dashboard', StudentController::class . '@showDashboard', ['auth', 'role:student']);
$router->get('/student', StudentController::class . '@showDashboard', ['auth', 'role:student']); // Backward compatibility alias

// Student Timetable
$router->get('/student/timetable', StudentController::class . '@showTimetable', ['auth', 'role:student']);

// Student Subjects
$router->get('/student/subjects', StudentController::class . '@showSubjects', ['auth', 'role:student']);

// Student Attendance
$router->get('/student/attendance', StudentController::class . '@showAttendance', ['auth', 'role:student']);

// Student Fees
$router->get('/student/fees', StudentController::class . '@showFees', ['auth', 'role:student']);

// Student Profile
$router->get('/student/profile', StudentController::class . '@showProfile', ['auth', 'role:student']);
$router->post('/student/profile', StudentController::class . '@updateProfile', ['auth', 'role:student']);
$router->post('/student/profile/email', StudentController::class . '@requestEmailChange', ['auth', 'role:student']);
$router->post('/student/profile/email/verify-otp', StudentController::class . '@verifyEmailChangeOtp', ['auth', 'role:student']);

// Student API Endpoints
$router->post('/api/student/profile', StudentController::class . '@updateProfile', ['auth', 'role:student']);
$router->post('/api/student/password-reset-request', StudentController::class . '@requestPasswordReset', ['auth', 'role:student']);

