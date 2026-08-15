ALTER TABLE student_profiles
  ADD COLUMN enrollment_semester_id bigint(20) unsigned DEFAULT NULL COMMENT 'Entry semester for the student (usually semester 1).',
  ADD KEY idx_enrollment_semester_id (enrollment_semester_id),
  ADD CONSTRAINT fk_student_profiles_enrollment_semester
    FOREIGN KEY (enrollment_semester_id) REFERENCES semesters(id)
    ON UPDATE CASCADE ON DELETE SET NULL;
