-- Add program start and end year fields to semesters table
-- This allows Vice Principal to specify the complete program duration

ALTER TABLE semesters
  ADD COLUMN start_academic_year INT NULL AFTER academic_year,
  ADD COLUMN end_academic_year INT NULL AFTER start_academic_year;
