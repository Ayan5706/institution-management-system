-- Add semester period fields
ALTER TABLE semesters
  ADD COLUMN start_date DATE NULL AFTER academic_year,
  ADD COLUMN end_date DATE NULL AFTER start_date;

-- Audit log for promotions and reminders
CREATE TABLE IF NOT EXISTS promotions_log (
  id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
  semester_id bigint(20) unsigned NOT NULL,
  student_id bigint(20) unsigned NOT NULL,
  status enum('PROMOTED','PENDING','REMINDED','REJECTED') NOT NULL,
  performed_by bigint(20) unsigned NOT NULL,
  performed_at datetime NOT NULL DEFAULT current_timestamp(),
  notes text DEFAULT NULL,
  PRIMARY KEY (id),
  KEY idx_semester_id (semester_id),
  KEY idx_student_id (student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
