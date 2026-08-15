# IMS Test Data - Small Seed Data Documentation

## Overview
This document provides a small, realistic dataset for running and testing the IMS (Institution Management System). It mirrors real data structure but keeps the volume low for fast testing.

## Database Seeders Updated

The following seeder files have been updated with small test data:

1. **ProgramsTableSeeder** - BCA and MSC programs
2. **SemestersTableSeeder** - Two semesters per program
3. **SubjectsTableSeeder** - 10 core subjects
4. **UsersTableSeeder** - 3 teachers, 8 students, 2 principals
5. **TeacherAssignmentsTableSeeder** - 10 subject assignments
6. **StudentProfilesTableSeeder** - BCA and MSC student enrollments
7. **TimetablesTableSeeder** - 12 timetable slots
8. **DatabaseSeeder** - Includes all seeders above

## Master Login Credentials

All test users use password: **password123**

### Admin / Principal Accounts (PRINCIPAL role)
| Login ID | Full Name | Email |
|----------|-----------|-------|
| admin | Admin User | admin@imsschool.local |
| principal.wilson | Principal Wilson | wilson@imsschool.local |

### Teacher Accounts (TEACHER role)
| User ID | Login ID | Full Name | Email | Phone |
|---------|----------|-----------|-------|-------|
| 3 | dr.johnson | Dr. Sarah Johnson | sjohnson@imsschool.local | 555-0003 |
| 4 | mr.smith | Mr. James Smith | jsmith@imsschool.local | 555-0004 |
| 5 | ms.davis | Ms. Emily Davis | edavis@imsschool.local | 555-0005 |

### Student Accounts (STUDENT role)
#### BCA Students
| User ID | Login ID | Full Name | Email | Reg. Number | DoB |
|---------|----------|-----------|-------|-------------|-----|
| 6 | janderson | John Michael Anderson | janderson@imsschool.local | BCA2025001 | 2004-05-15 |
| 7 | sbrown | Sarah Michelle Brown | sbrown@imsschool.local | BCA2025002 | 2004-08-22 |
| 8 | mharris | Michael Robert Harris | mharris@imsschool.local | BCA2025003 | 2004-03-10 |
| 9 | ltaylor | Lisa Marie Taylor | ltaylor@imsschool.local | BCA2025004 | 2004-11-30 |

#### MSC Students
| User ID | Login ID | Full Name | Email | Reg. Number | DoB |
|---------|----------|-----------|-------|-------------|-----|
| 10 | dmiller | David James Miller | dmiller@imsschool.local | MSC2025001 | 2004-07-14 |
| 11 | asingh | Aditya Singh | asingh@imsschool.local | MSC2025002 | 2003-02-20 |
| 12 | rdesai | Ravi Desai | rdesai@imsschool.local | MSC2025003 | 2003-06-18 |
| 13 | nkumar | Neha Kumar | nkumar@imsschool.local | MSC2025004 | 2003-09-12 |

## Programs and Semesters

### BCA - Bachelor of Computer Applications
**Program Code:** BCA

**Semesters:**
- Semester 1 (2025-2026) - Current
- Semester 2 (2025-2026) - Current

### MSC - Master of Science in Computer Science
**Program Code:** MSC

**Semesters:**
- Semester 1 (2025-2026) - Current
- Semester 2 (2025-2026) - Current

## BCA Curriculum

### Semester 1
| Subject Code | Subject Name | Teacher |
|--------------|--------------|---------|
| CS101 | Introduction to Programming | Dr. Sarah Johnson |
| CS102 | Data Structures | Mr. James Smith |
| MATH101 | Calculus I | Ms. Emily Davis |

**Timetable (Sem 1):**
- CS101: MON/WED 09:00-10:30
- CS102: TUE 09:00-10:30
- MATH101: THU 10:30-12:00

### Semester 2
| Subject Code | Subject Name | Teacher |
|--------------|--------------|---------|
| CS201 | Object-Oriented Programming | Dr. Sarah Johnson |
| CS202 | Web Development Basics | Mr. James Smith |
| MATH102 | Calculus II | Ms. Emily Davis |

**Timetable (Sem 2):**
- CS201: MON/WED 12:00-13:30
- CS202: TUE 12:00-13:30
- MATH102: THU 13:30-15:00

## MSC Curriculum

### Semester 1
| Subject Code | Subject Name | Teacher |
|--------------|--------------|---------|
| MACS101 | Advanced Algorithms | Dr. Sarah Johnson |
| MACS102 | Distributed Systems | Mr. James Smith |

**Timetable (Sem 1):**
- MACS101: FRI 09:00-10:30
- MACS102: FRI 10:30-12:00

### Semester 2
| Subject Code | Subject Name | Teacher |
|--------------|--------------|---------|
| MACS201 | Machine Learning Advanced | Dr. Sarah Johnson |
| MACS202 | Natural Language Processing | Mr. James Smith |

**Timetable (Sem 2):**
- MACS201: SAT 09:00-10:30
- MACS202: SAT 10:30-12:00

## Running the Seeders

To populate the database with this test data:

1. Navigate to your project directory
2. Run: `php scripts/seed.php`
3. Data inserts in this order:
   - Users (Principals, Teachers, Students)
   - Programs (BCA, MSC)
   - Semesters (per program)
   - Subjects (assigned to semesters)
   - Teacher Assignments (teachers linked to subjects)
   - Student Profiles (students enrolled)
   - Timetables (class schedules)

## Key Test Scenarios

### 1. **Principal Dashboard**
- Login as: `principal.wilson` / `password123`
- Can see: Programs, semesters, students, teachers

### 2. **Teacher Dashboard**
- Login as: `dr.johnson` / `password123`
- Can see: Assigned subjects (CS101, CS201, MACS101, MACS201) and timetables

### 3. **Student Dashboard**
- Login as: `janderson` / `password123` (BCA Student)
- Can see: Enrolled program, current semester, subject list, timetable

## Important Notes

- All passwords: `password123`
- Academic year: 2025-2026
- Current semesters: `is_current = 1`
- Working days: MON, TUE, WED, THU, FRI, SAT
- Registration numbers follow pattern: `PROGRAM_CODE+YEAR+SEQUENCE`
- Example: BCA2025001, MSC2025001

All data is interconnected and ready for quick system testing.
