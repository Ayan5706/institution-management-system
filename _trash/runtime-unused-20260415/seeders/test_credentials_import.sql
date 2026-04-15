-- ============================================================================
-- IMS Test Credentials SQL Import Script
-- ============================================================================
-- Generated: 2026-04-12
-- Purpose: Insert standardized test accounts into the users table
-- 
-- INSTRUCTIONS FOR PHPMYADMIN:
-- 1. Open phpMyAdmin at http://localhost/phpmyadmin
-- 2. Select the IMS database (left panel)
-- 3. Click the SQL tab at the top
-- 4. Copy and paste the entire contents of this file
-- 5. Click the Go button to execute
-- 6. All 6 test accounts will be created
--
-- ============================================================================

-- OPTIONAL: Uncomment the line below to clear old test accounts first
-- DELETE FROM users WHERE role IN ('PRINCIPAL', 'VP', 'MANAGER', 'ACCOUNTANT', 'TEACHER', 'STUDENT');

-- Insert standardized test credentials (one per role)
INSERT INTO users (full_name, login_id, email, phone, password, role, is_active, created_at, updated_at) VALUES
('Principal Test Account', 'principal', 'principal@imsschool.local', '555-1001', '$2y$10$9pZLbRrQKPuGHxLqBKc2.OPlMyB6Y/5llNb7FvuMRN5.I0p0r8nBW', 'PRINCIPAL', 1, '2026-04-12 00:00:00', '2026-04-12 00:00:00'),
('Vice Principal Test Account', 'vp', 'vp@imsschool.local', '555-1002', '$2y$10$9pZLbRrQKPuGHxLqBKc2.OPlMyB6Y/5llNb7FvuMRN5.I0p0r8nBW', 'VP', 1, '2026-04-12 00:00:00', '2026-04-12 00:00:00'),
('Manager Test Account', 'manager', 'manager@imsschool.local', '555-1003', '$2y$10$9pZLbRrQKPuGHxLqBKc2.OPlMyB6Y/5llNb7FvuMRN5.I0p0r8nBW', 'MANAGER', 1, '2026-04-12 00:00:00', '2026-04-12 00:00:00'),
('Accountant Test Account', 'accountant', 'accountant@imsschool.local', '555-1004', '$2y$10$9pZLbRrQKPuGHxLqBKc2.OPlMyB6Y/5llNb7FvuMRN5.I0p0r8nBW', 'ACCOUNTANT', 1, '2026-04-12 00:00:00', '2026-04-12 00:00:00'),
('Teacher Test Account', 'teacher', 'teacher@imsschool.local', '555-1005', '$2y$10$9pZLbRrQKPuGHxLqBKc2.OPlMyB6Y/5llNb7FvuMRN5.I0p0r8nBW', 'TEACHER', 1, '2026-04-12 00:00:00', '2026-04-12 00:00:00'),
('Student Test Account', 'student', 'student@imsschool.local', '555-1006', '$2y$10$9pZLbRrQKPuGHxLqBKc2.OPlMyB6Y/5llNb7FvuMRN5.I0p0r8nBW', 'STUDENT', 1, '2026-04-12 00:00:00', '2026-04-12 00:00:00');

-- VERIFICATION QUERY
-- After running the above, run this query to verify the data was imported:
-- SELECT login_id, role, is_active, email FROM users WHERE role IN ('PRINCIPAL', 'VP', 'MANAGER', 'ACCOUNTANT', 'TEACHER', 'STUDENT') ORDER BY role;

-- TEST CREDENTIALS SUMMARY
-- Use these credentials to login:
-- Role             | Login ID   | Password
-- PRINCIPAL        | principal  | principal123
-- VP               | vp         | vp123
-- MANAGER          | manager    | manager123
-- ACCOUNTANT       | accountant | accountant123
-- TEACHER          | teacher    | teacher123
-- STUDENT          | student    | student123
