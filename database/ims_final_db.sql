-- =====================================================
-- IMS_FINAL Complete Unified Database Schema
-- =====================================================
-- Single source of truth combining all tables, migrations tracker, and trigger
--
-- Usage (phpMyAdmin): Import this file into a NEW database
-- Charset: utf8mb4, Collation: utf8mb4_unicode_ci
-- =====================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- 1. Migration tracking table
CREATE TABLE IF NOT EXISTS `migrations` (
  `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
  `migration` VARCHAR(255) NOT NULL,
  `batch` INT NOT NULL DEFAULT 1,
  `executed_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_migration_name` (`migration`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Migration tracker table.';

-- 2. Users table (base identity)
CREATE TABLE IF NOT EXISTS `users` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `role` enum('PRINCIPAL','VP','MANAGER','ACCOUNTANT','TEACHER','STUDENT') NOT NULL,
  `login_id` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL COMMENT 'PHP bcrypt hash. Never store plaintext.',
  `full_name` varchar(150) NOT NULL,
  `email` varchar(191) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=active, 0=deactivated. Checked on every protected request.',
  `must_change_password` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=force password change on next login.',
  `created_by` bigint(20) unsigned DEFAULT NULL COMMENT 'NULL for Principal (created by system owner).',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_login_id` (`login_id`),
  KEY `idx_role` (`role`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_users_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Single identity table for all roles.';

-- 3. User support tables
CREATE TABLE IF NOT EXISTS `activation_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `token_hash` char(64) NOT NULL COMMENT 'SHA-256 hash of the activation token.',
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_by` bigint(20) unsigned DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_activation_token_hash` (`token_hash`),
  KEY `idx_activation_user` (`user_id`),
  KEY `idx_activation_expires` (`expires_at`),
  CONSTRAINT `fk_activation_tokens_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_activation_tokens_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='One-time activation links for admin accounts.';

CREATE TABLE IF NOT EXISTS `password_reset_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `requested_by` bigint(20) unsigned NOT NULL COMMENT 'User who submitted the reset request.',
  `status` enum('PENDING','APPROVED','EXPIRED') NOT NULL DEFAULT 'PENDING',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` bigint(20) unsigned DEFAULT NULL COMMENT 'Authority who approved. NULL if expired by cron.',
  PRIMARY KEY (`id`),
  KEY `idx_requested_by` (`requested_by`),
  KEY `idx_status` (`status`),
  KEY `idx_created_at` (`created_at`),
  KEY `fk_reset_resolved_by` (`resolved_by`),
  CONSTRAINT `fk_reset_requested_by` FOREIGN KEY (`requested_by`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_reset_resolved_by` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Password reset requests. Cron expires PENDING requests older than 7 days.';

CREATE TABLE IF NOT EXISTS `password_reset_tokens` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'User requesting reset.',
  `token_hash` char(64) NOT NULL COMMENT 'SHA-256 hash of the reset token.',
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_password_reset_token_hash` (`token_hash`),
  KEY `idx_password_reset_user` (`user_id`),
  KEY `idx_password_reset_expires` (`expires_at`),
  CONSTRAINT `fk_password_reset_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Password reset tokens.';

CREATE TABLE IF NOT EXISTS `password_reset_verifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'User requesting password reset (VP, Manager, Accountant).',
  `token_hash` char(64) NOT NULL COMMENT 'SHA-256 hash of the email verification token.',
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_email_verification_token_hash` (`token_hash`),
  KEY `idx_email_verification_user` (`user_id`),
  KEY `idx_email_verification_expires` (`expires_at`),
  CONSTRAINT `fk_email_verification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Email verification tokens for password reset requests (VP, Manager, Accountant).';

CREATE TABLE IF NOT EXISTS `email_change_requests` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'User requesting the email change.',
  `new_email` varchar(191) NOT NULL,
  `status` enum('PENDING','APPROVED','EXPIRED') NOT NULL DEFAULT 'PENDING',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `resolved_at` datetime DEFAULT NULL,
  `resolved_by` bigint(20) unsigned DEFAULT NULL COMMENT 'Principal who approved or dismissed the request.',
  PRIMARY KEY (`id`),
  KEY `idx_email_change_user` (`user_id`),
  KEY `idx_email_change_status` (`status`),
  KEY `idx_email_change_created` (`created_at`),
  KEY `idx_email_change_resolved_by` (`resolved_by`),
  CONSTRAINT `fk_email_change_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_email_change_resolved_by` FOREIGN KEY (`resolved_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Email change requests awaiting principal approval.';

CREATE TABLE IF NOT EXISTS `email_change_verifications` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'User requesting the email change.',
  `new_email` varchar(191) NOT NULL COMMENT 'Email being verified.',
  `otp_hash` char(64) NOT NULL COMMENT 'SHA-256 hash of the OTP code.',
  `expires_at` datetime NOT NULL,
  `verified_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_email_verification_user` (`user_id`),
  KEY `idx_email_verification_expires` (`expires_at`),
  CONSTRAINT `fk_email_change_verification_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='OTP verification records for email change requests.';

CREATE TABLE IF NOT EXISTS `jwt_blacklist` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `jti` varchar(255) NOT NULL COMMENT 'JWT ID claim. Unique per token. bin2hex(random_bytes(16)) in PHP.',
  `user_id` bigint(20) unsigned NOT NULL COMMENT 'User whose token was blacklisted.',
  `expires_at` datetime NOT NULL COMMENT 'Original token expiry. Used by cron for cleanup.',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_jti` (`jti`),
  KEY `idx_user_id` (`user_id`),
  KEY `idx_expires_at` (`expires_at`),
  CONSTRAINT `fk_jwt_blacklist_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Blacklisted JWT tokens. Cron deletes rows WHERE expires_at < NOW().';

-- 4. Academic structure tables
CREATE TABLE IF NOT EXISTS `programs` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `program_name` varchar(100) NOT NULL COMMENT 'e.g., Bachelor of Computer Applications.',
  `program_code` varchar(10) NOT NULL COMMENT 'Uppercase short code e.g., BCA. PHP enforces strtoupper() and regex /^[A-Z]{2,10}$/.',
  `duration_semesters` tinyint(3) unsigned NOT NULL COMMENT 'Total semesters in program. min=1.',
  `is_active` tinyint(1) NOT NULL DEFAULT 1 COMMENT '1=active. Cannot deactivate if active students or current semesters exist.',
  `created_by` bigint(20) unsigned DEFAULT NULL COMMENT 'FK to users (VP who created this).',
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_program_name` (`program_name`),
  UNIQUE KEY `uq_program_code` (`program_code`),
  KEY `idx_is_active` (`is_active`),
  KEY `idx_created_by` (`created_by`),
  CONSTRAINT `fk_programs_created_by` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Academic degree programs. Managed by VP.';

CREATE TABLE IF NOT EXISTS `semesters` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `program_id` bigint(20) unsigned NOT NULL,
  `semester_number` tinyint(3) unsigned NOT NULL COMMENT 'Position within program. PHP validates: >= 1 AND <= program.duration_semesters.',
  `academic_year` varchar(9) NOT NULL COMMENT 'Format: YYYY-YYYY. e.g., 2024-2025. PHP validates year2 == year1 + 1.',
  `start_academic_year` int DEFAULT NULL COMMENT 'Program start year (e.g., 2025). VP specifies program duration.',
  `end_academic_year` int DEFAULT NULL COMMENT 'Program end year (e.g., 2028). VP specifies program duration.',
  `start_date` date DEFAULT NULL COMMENT 'Semester start date set by VP.',
  `end_date` date DEFAULT NULL COMMENT 'Semester end date set by VP.',
  `is_current` tinyint(1) NOT NULL DEFAULT 0 COMMENT '1=currently running for this program. Enforced by trigger + PHP transaction.',
  `fee_amount` decimal(10,2) DEFAULT NULL COMMENT 'Set by Accountant. NULL = fee not yet configured.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_semester` (`program_id`,`semester_number`,`academic_year`),
  KEY `idx_program_id` (`program_id`),
  KEY `idx_is_current` (`is_current`),
  CONSTRAINT `fk_semesters_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Semester instances. term is computed, not stored.';

CREATE TABLE IF NOT EXISTS `subjects` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `semester_id` bigint(20) unsigned NOT NULL,
  `subject_name` varchar(150) NOT NULL,
  `subject_code` varchar(20) NOT NULL COMMENT 'Unique within a semester only. Same code allowed across different semesters.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_subj_code` (`semester_id`,`subject_code`),
  KEY `idx_semester_id` (`semester_id`),
  CONSTRAINT `fk_subjects_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Academic subjects per semester.';

-- 5. Student management tables
CREATE TABLE IF NOT EXISTS `student_profiles` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `user_id` bigint(20) unsigned NOT NULL,
  `father_name` varchar(150) NOT NULL COMMENT 'Father name for student profile.',
  `registration_number` varchar(30) NOT NULL COMMENT 'Also stored in users.login_id. Must match.',
  `date_of_birth` date NOT NULL,
  `program_id` bigint(20) unsigned NOT NULL COMMENT 'Enrolled program. READ-ONLY after INSERT. No PHP endpoint may UPDATE this.',
  `enrollment_semester_id` bigint(20) unsigned DEFAULT NULL COMMENT 'Entry semester for the student (usually semester 1).',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_user_id` (`user_id`),
  UNIQUE KEY `uq_registration_number` (`registration_number`),
  KEY `idx_program_id` (`program_id`),
  KEY `idx_enrollment_semester_id` (`enrollment_semester_id`),
  CONSTRAINT `fk_student_profiles_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `fk_student_profiles_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_student_profiles_enrollment_semester` FOREIGN KEY (`enrollment_semester_id`) REFERENCES `semesters` (`id`) ON UPDATE CASCADE ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Student-specific fields. One-to-one with users.';

CREATE TABLE IF NOT EXISTS `student_fees` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL,
  `semester_id` bigint(20) unsigned NOT NULL,
  `amount_paid` decimal(10,2) NOT NULL DEFAULT 0.00 COMMENT 'PHP validates >= 0 AND <= semester.fee_amount using BCMath bccomp().',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_student_semester` (`student_id`,`semester_id`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_semester_id` (`semester_id`),
  CONSTRAINT `fk_student_fees_semester` FOREIGN KEY (`semester_id`) REFERENCES `semesters` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_student_fees_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Student fee payments. pending_amount and status computed in PHP, never stored.';

CREATE TABLE IF NOT EXISTS `promotions_log` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `semester_id` bigint(20) unsigned NOT NULL,
  `student_id` bigint(20) unsigned NOT NULL,
  `status` enum('PROMOTED','PENDING','REMINDED','REJECTED') NOT NULL,
  `performed_by` bigint(20) unsigned NOT NULL,
  `performed_at` datetime NOT NULL DEFAULT current_timestamp(),
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `idx_semester_id` (`semester_id`),
  KEY `idx_student_id` (`student_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Audit log for semester promotions and reminders.';

-- 6. Teaching and scheduling tables
CREATE TABLE IF NOT EXISTS `teacher_assignments` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_id` bigint(20) unsigned NOT NULL COMMENT 'Must have role=TEACHER. PHP validates before INSERT.',
  `subject_id` bigint(20) unsigned NOT NULL COMMENT 'UNIQUE: one teacher per subject. Each subject has exactly one teacher.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_subject_id` (`subject_id`),
  KEY `idx_teacher_id` (`teacher_id`),
  CONSTRAINT `fk_teacher_assignments_subject` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_teacher_assignments_teacher` FOREIGN KEY (`teacher_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Teacher to subject assignment. semester_id derived via JOIN.';

CREATE TABLE IF NOT EXISTS `timetables` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `teacher_assignment_id` bigint(20) unsigned NOT NULL,
  `day` enum('MON','TUE','WED','THU','FRI','SAT') NOT NULL COMMENT 'PHP validates against SystemConfig WORKING_DAYS.',
  `start_time` time NOT NULL COMMENT 'PHP validates >= SystemConfig DAY_START_TIME.',
  `end_time` time NOT NULL COMMENT 'PHP validates <= DAY_END_TIME and > start_time.',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_timetable_slot` (`teacher_assignment_id`,`day`,`start_time`),
  KEY `idx_day` (`day`),
  CONSTRAINT `fk_timetables_assignment` FOREIGN KEY (`teacher_assignment_id`) REFERENCES `teacher_assignments` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Recurring timetable slots. Clash checks done in PHP before INSERT.';

CREATE TABLE IF NOT EXISTS `attendance` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `student_id` bigint(20) unsigned NOT NULL COMMENT 'Must have role=STUDENT. PHP validates.',
  `timetable_slot_id` bigint(20) unsigned NOT NULL,
  `date` date NOT NULL COMMENT 'PHP validates: day-of-week of date must match timetable slot day.',
  `status` enum('PRESENT','ABSENT','LATE') NOT NULL,
  `marked_by` bigint(20) unsigned DEFAULT NULL COMMENT 'Teacher who marked. SET NULL if teacher account deleted.',
  `marked_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_attendance` (`student_id`,`timetable_slot_id`,`date`),
  KEY `idx_student_id` (`student_id`),
  KEY `idx_timetable_slot_id` (`timetable_slot_id`),
  KEY `idx_date` (`date`),
  KEY `idx_marked_by` (`marked_by`),
  CONSTRAINT `fk_attendance_marked_by` FOREIGN KEY (`marked_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_attendance_student` FOREIGN KEY (`student_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_attendance_timetable_slot` FOREIGN KEY (`timetable_slot_id`) REFERENCES `timetables` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Per-student per-slot per-date attendance. Window enforcement in PHP.';

-- 7. System configuration table
CREATE TABLE IF NOT EXISTS `system_config` (
  `id` bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  `config_key` varchar(60) NOT NULL,
  `config_value` text NOT NULL,
  `updated_by` bigint(20) unsigned DEFAULT NULL COMMENT 'Principal who last updated this setting.',
  `updated_at` datetime NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_config_key` (`config_key`),
  KEY `idx_updated_by` (`updated_by`),
  CONSTRAINT `fk_system_config_updated_by` FOREIGN KEY (`updated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Institution-wide configuration settings. Principal only.';

SET FOREIGN_KEY_CHECKS = 1;

-- Trigger: enforce single parity (odd/even) for current semesters per program
DELIMITER //

DROP TRIGGER IF EXISTS enforce_single_current_semester //

CREATE TRIGGER enforce_single_current_semester 
BEFORE UPDATE ON semesters
FOR EACH ROW
BEGIN
    DECLARE current_count INT;
  DECLARE new_parity INT;

    IF NEW.is_current = 1 THEN
    SET new_parity = MOD(NEW.semester_number, 2);
        SELECT COUNT(*) INTO current_count
        FROM semesters
        WHERE program_id = NEW.program_id 
        AND is_current = 1 
        AND id != NEW.id;

    IF current_count > 0 THEN
      SELECT COUNT(*) INTO current_count
      FROM semesters
      WHERE program_id = NEW.program_id
      AND is_current = 1
      AND id != NEW.id
      AND MOD(semester_number, 2) <> new_parity;
    END IF;

        IF current_count > 0 THEN
            SIGNAL SQLSTATE '45000'
      SET MESSAGE_TEXT = 'Cannot mix odd and even active semesters for same program';
        END IF;
    END IF;
END //

DELIMITER ;
