# Important Viva Questions for IMS Project

## 1. Project Overview
1. What is the purpose of this IMS project?
   - Answer: It is an Institution Management System used to manage users, students, teachers, attendance, timetables, fees, and academic records.
2. Explain the architecture of the project.
   - Answer: The project follows a MVC-style structure with routes, controllers, models, views, services, and configuration files.
3. What is the role of the controller in this project?
   - Answer: Controllers handle user requests, process business logic, interact with models/services, and return views or responses.
4. How are models used in this project?
   - Answer: Models interact with the database for CRUD operations like inserting, reading, updating, and deleting records.
5. What is the purpose of the views folder?
   - Answer: Views contain the UI templates shown to users, such as login pages, dashboards, profile pages, and forms.
6. How does routing work in this project?
   - Answer: Requests are mapped to specific controller methods through route definitions in the routes/web.php file.
7. What is the difference between a controller and a service in this project?
   - Answer: Controllers manage request flow and presentation, while services contain reusable business logic such as semester operations, fees, or mail handling.

## 2. Authentication and Authorization
8. How does the login system work?
   - Answer: The user submits credentials, the system checks them against the database, and if valid, creates a session for that user.
9. How is a user authenticated in this project?
   - Answer: Authentication is done by verifying the entered email/password against stored user data and starting a logged-in session.
10. What is the purpose of middleware in this project?
   - Answer: Middleware protects routes by checking authentication, user role, or security requirements before the request reaches the controller.
11. How are roles such as admin, principal, VP, manager, accountant, teacher, and student handled?
   - Answer: Each user has a role in the database, and middleware checks the role before allowing access to role-specific routes.
12. What happens when a user tries to access a page without login?
   - Answer: The request is blocked and the user is redirected or denied access based on the authentication middleware.
13. How is session management implemented?
   - Answer: Sessions are created and stored using PHP sessions, with user data such as ID, name, role, and login state stored in the session.
14. What is the purpose of CSRF protection here?
   - Answer: CSRF protection prevents unauthorized form submissions by verifying a hidden token sent with the request.

## 3. Password Change Flow
15. Explain the password change process step by step.
   - Answer: The user enters the current password and new password, the system validates the input, checks the current password, and updates the password if valid.
16. Which controller handles the password change request?
   - Answer: The relevant role controller such as PrincipalController, VPController, TeacherController, StudentController, etc. handles the request, depending on the logged-in user.
17. How is the old password verified before changing the password?
   - Answer: The system compares the entered current password with the stored hashed password using password verification functions.
18. How is the new password stored securely?
   - Answer: The new password is hashed using PHP password hashing functions before being stored.
19. What function or method is used to hash the password?
   - Answer: password_hash() is used for hashing, and password_verify() is used for verification.
20. What validation checks are performed before updating the password?
   - Answer: The system checks that the current password is correct, that the new password is provided, and that the confirmation matches.
21. What happens if the old password is incorrect?
   - Answer: The system returns an error message and does not change the password.
22. What happens if the new password and confirmation do not match?
   - Answer: The system rejects the request and asks the user to re-enter matching values.

## 4. Email Change and OTP Flow
23. Explain how the email change process works.
   - Answer: The user enters a new email, the system creates an OTP record, sends an OTP to the new email, and only updates the account email after OTP verification.
24. What is the purpose of the email change request?
   - Answer: It adds a secure verification step before changing the user’s main email address.
25. How is a new email request initiated by the user?
   - Answer: The user submits the new email and confirmation through the profile form, which triggers the requestEmailChange logic.
26. What is an OTP and why is it used?
   - Answer: OTP stands for One-Time Password; it is used to verify ownership of the new email address.
27. How is the OTP generated?
   - Answer: A 6-digit random number is generated and then hashed before being stored.
28. Which function/class is used to send the OTP email?
   - Answer: The MailService class is used to send the OTP email through PHPMailer.
29. How is the OTP stored securely?
   - Answer: The OTP is stored as a hash in the email_change_verifications table, not as plain text.
30. How is the OTP verified by the system?
   - Answer: The user enters the OTP, the system hashes it and compares it with the stored hash, and checks if it is still valid and not expired.
31. What happens after the OTP is verified successfully?
   - Answer: The email change is accepted and the user’s email is updated after the verification step.
32. What happens if the OTP expires?
   - Answer: The verification is rejected and the user must request a new OTP.
33. Why is the old email not changed immediately after request?
   - Answer: To prevent unauthorized changes and confirm that the user owns the new email address.
34. What is the purpose of the email verification table/model?
   - Answer: It stores the pending verification request, new email address, OTP hash, expiry time, and verification status.

## 5. Mail and Notification System
35. How are emails sent from the system?
   - Answer: Emails are sent through the MailService class using SMTP credentials configured in the application.
36. Which mail service is used in the project?
   - Answer: PHPMailer is used for sending emails.
37. What configuration is required for sending emails?
   - Answer: SMTP host, port, username, password, encryption, from address, and from name are required.
38. How do you test whether mail sending is working?
   - Answer: A test script or SMTP test page can be used to send a sample email and confirm the configuration.
39. What happens if mail sending fails?
   - Answer: The system throws an exception or returns an error message and logs the failure.
40. What is the difference between HTML body and text body in email sending?
   - Answer: HTML body is used for rich formatted email content, while text body is a plain-text fallback.

## 6. Student Module
41. How is student information managed in the project?
   - Answer: Student data is stored in the database and managed through the StudentController and StudentModel.
42. How are student records created, updated, or deleted?
   - Answer: The controller receives form data and calls model methods to create, update, or remove student records.
43. Explain the role of the student controller.
   - Answer: It handles student-related pages, profile operations, fee views, and other student-specific actions.
44. How is student profile data handled?
   - Answer: Profile information is retrieved from the database and shown in the student profile view, where it can be edited.
45. How are student fees managed?
   - Answer: Student fee records are stored and processed through the fee service/controller for payment and management.
46. What is the purpose of the student dashboard?
   - Answer: It provides a personalized landing page with relevant student information and links to tools.

## 7. Teacher Module
47. How does the teacher module work?
   - Answer: The teacher module provides role-specific features such as viewing assigned classes, marking attendance, and managing profile details.
48. What features are available for teachers in this project?
   - Answer: Teachers can manage attendance, view timetables, update their profile, and interact with assigned subjects.
49. How is attendance marked for students by teachers?
   - Answer: Teachers use the attendance form to mark present or absent for each student based on the class schedule.
50. How are teacher assignments managed?
   - Answer: Teacher assignments are stored in the database and linked to subjects, semesters, and classes.
51. How does the teacher role interact with the timetable module?
   - Answer: Teachers view or follow class schedules assigned to them from the timetable system.

## 8. Attendance and Timetable
52. How is attendance recorded in the system?
   - Answer: Attendance is recorded through attendance forms that store the presence status of students for a given date and class.
53. What is the purpose of the timetable module?
   - Answer: It organizes classes, subjects, teachers, and time slots into a structured schedule.
54. How are class schedules managed?
   - Answer: Schedules are stored in the database and displayed through timetable views for different roles.
55. How does the system prevent timetable conflicts?
   - Answer: The system checks existing assignments and schedules to avoid overlapping class slots for the same teacher or room.
56. How is attendance linked with students and courses?
   - Answer: Attendance records are tied to specific students and the course or class being taught.

## 9. Fees and Finance Module
57. How are student fees handled in the system?
   - Answer: Student fees are managed through fee records that are created, updated, and tracked by the accounting module.
58. What is the purpose of the finance/accounting module?
   - Answer: It manages fee collections, balances, and financial records for students.
59. How are fee records created and tracked?
   - Answer: Records are inserted into the database and later retrieved or updated for reporting and payment tracking.
60. How does the accountant role interact with fees?
   - Answer: The accountant can manage and process fee-related operations for students.

## 10. Programs, Semesters, and Subjects
61. Explain how programs are managed.
   - Answer: Programs are stored in the database and managed through program-related controllers and models.
62. What is the purpose of semesters in the system?
   - Answer: Semesters organize academic periods and help track subjects, timetable entries, and student records.
63. How are subjects assigned to students or teachers?
   - Answer: Subject assignments are stored through assignment tables and linked to the relevant teacher or student records.
64. How does the system store academic data?
   - Answer: Academic data such as programs, semesters, subjects, and assignments are stored in relational database tables.
65. What is the role of the semester service?
   - Answer: The semester service handles semester-related logic such as current semester management and related operations.

## 11. Database and Models
66. Which database is used in this project?
   - Answer: The project uses MySQL as the database.
67. How are database connections established?
   - Answer: Database configuration is loaded from config files and used by the database layer to connect to MySQL.
68. What is the purpose of the base model?
   - Answer: The base model provides common database operations and shared functionality for all models.
69. How do models interact with the database?
   - Answer: Models prepare SQL queries and execute them through the database connection layer.
70. What is the difference between a model and a controller?
   - Answer: A controller handles requests and logic flow, while a model handles database data access.
71. How are insert, update, and delete operations implemented?
   - Answer: They are performed through model methods that execute SQL queries for create, read, update, and delete operations.

## 12. Security and Validation
72. How does the application validate user input?
   - Answer: It uses input validation rules and checks before processing user data.
73. Why is validation important in this system?
   - Answer: Validation prevents invalid data, reduces errors, and improves security.
74. How are invalid emails handled?
   - Answer: The system shows an error message and prevents the request from proceeding until a valid email is entered.
75. What precautions are taken against unauthorized access?
   - Answer: Authentication, role-based middleware, sessions, and CSRF protection are used.
76. How is sensitive data protected in the system?
   - Answer: Passwords are hashed, sessions are used securely, and access is restricted by role and middleware.

## 13. Error Handling and Logging
77. How does the project handle errors?
   - Answer: Errors are caught, logged, and usually shown as friendly error messages to the user.
78. What is the purpose of logging in this application?
   - Answer: Logging helps track system errors, mail failures, and other important events for debugging.
79. What should be done when an email fails to send?
   - Answer: The system should log the error, notify the user, and allow retry or fallback handling.
80. How is debugging done in this project?
   - Answer: Debugging is done by checking logs, reviewing controller logic, and testing the relevant module manually.

## 14. Practical / Scenario Based
81. If a user forgets the password, how would you implement the reset flow?
   - Answer: The user requests a password reset, a reset token is generated and email is sent, and the user sets a new password after verification.
82. If OTP sending fails, how would you troubleshoot it?
   - Answer: I would check the SMTP configuration, mail credentials, server connection, logs, and the mail service code.
83. If a user requests an email change, what steps occur in the backend?
   - Answer: The system validates the input, creates a verification record with an OTP hash, sends the OTP, and updates the email only after successful verification.
84. If a teacher tries to access a VP-only page, what happens?
   - Answer: The role middleware blocks the request and denies access.
85. How would you add a new role to the project?
   - Answer: I would add the role to the user data, update role-based middleware, and create role-specific routes and views.
86. How would you add a new module to the project?
   - Answer: I would create a controller, model, routes, views, and optionally a service for the new feature.
87. How would you test the email verification process manually?
   - Answer: I would enter a new email, check whether the OTP is sent, enter the OTP, and confirm that the email is updated only after successful verification.
88. How would you explain the difference between pending email request and verified email change?
   - Answer: A pending request means the system has created the verification record and sent the OTP, while a verified change means the OTP was successfully validated and the new email is now active.
