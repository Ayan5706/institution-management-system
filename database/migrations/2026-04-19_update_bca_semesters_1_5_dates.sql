-- Update BCA semester dates (1-5) with 1-month gaps and set program academic years (2026-2029)

-- Semester 1: 2026-04-01 to 2026-09-30
UPDATE semesters s
JOIN programs p ON p.id = s.program_id
SET s.start_date = '2026-04-01',
    s.end_date = '2026-09-30',
    s.academic_year = '2026-2027',
    s.start_academic_year = 2026,
    s.end_academic_year = 2029
WHERE p.program_code = 'BCA' AND s.semester_number = 1;

-- Semester 2: 2026-11-01 to 2027-03-31 (1-month gap after Sem 1)
UPDATE semesters s
JOIN programs p ON p.id = s.program_id
SET s.start_date = '2026-11-01',
    s.end_date = '2027-03-31',
    s.academic_year = '2026-2027',
    s.start_academic_year = 2026,
    s.end_academic_year = 2029
WHERE p.program_code = 'BCA' AND s.semester_number = 2;

-- Semester 3: 2027-05-01 to 2027-09-30 (1-month gap after Sem 2)
UPDATE semesters s
JOIN programs p ON p.id = s.program_id
SET s.start_date = '2027-05-01',
    s.end_date = '2027-09-30',
    s.academic_year = '2027-2028',
    s.start_academic_year = 2026,
    s.end_academic_year = 2029
WHERE p.program_code = 'BCA' AND s.semester_number = 3;

-- Semester 4: 2027-11-01 to 2028-03-31 (1-month gap after Sem 3)
UPDATE semesters s
JOIN programs p ON p.id = s.program_id
SET s.start_date = '2027-11-01',
    s.end_date = '2028-03-31',
    s.academic_year = '2027-2028',
    s.start_academic_year = 2026,
    s.end_academic_year = 2029
WHERE p.program_code = 'BCA' AND s.semester_number = 4;

-- Semester 5: 2028-05-01 to 2028-09-30 (1-month gap after Sem 4)
UPDATE semesters s
JOIN programs p ON p.id = s.program_id
SET s.start_date = '2028-05-01',
    s.end_date = '2028-09-30',
    s.academic_year = '2028-2029',
    s.start_academic_year = 2026,
    s.end_academic_year = 2029
WHERE p.program_code = 'BCA' AND s.semester_number = 5;
