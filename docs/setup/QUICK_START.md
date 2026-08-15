# Quick Start Guide - Running Test Data Seeders

## Step 1: Run the Seeder Script

From your project root directory, run:

```bash
php scripts/seed.php
```

You'll see:
```
╔════════════════════════════════════════════════════╗
║  Database Seeder - IMS Test Data Populate         ║
╚════════════════════════════════════════════════════╝

Database Connection: ims_final @ 127.0.0.1:3306

Data to be seeded:
  ✓ Programs (BCA, MSC, and 5 others)
  ✓ Semesters (34 total across all programs)
  ✓ Subjects (70+ comprehensive curriculum)
  ✓ Teachers (10 faculty members)
  ✓ Students (11 total, enrolled in programs)
  ✓ Teacher Assignments (20 subject assignments)
  ✓ Timetables (50+ class slots)

This will seed your database with complete test data.
Are you sure you want to continue? (yes/no): 
```

Type `yes` and press Enter.

### Skip Confirmation
To skip the confirmation prompt:
```bash
php scripts/seed.php --force
```

## Step 2: Verify Seeding Completed

You should see:
```
Seeding database...
──────────────────────────────────────────────────
✓ Database seeding completed successfully!

✅ Test data ready. Login credentials:
   Principal: principal.wilson
   Teacher:   dr.johnson
   Student:   janderson (BCA)
   Password:  password123 (for all accounts)

📄 For detailed information, see:
   - SEED_DATA.md (complete curriculum details)
   - TEST_DATA_SUMMARY.md (quick reference)
```

## Step 3: Start Your Application

Run your application as normal:

```bash
# If using a local development server
php -S localhost:8000 -t public/

# Or through XAMPP
# Navigate to http://localhost/IMS_FINAL/public/
```

## Step 4: Test the System

### Option A: Login as Principal
1. Go to login page
2. Enter: `principal.wilson` / `password123`
3. See all programs, semesters, students, teachers

### Option B: Login as Teacher
1. Go to login page
2. Enter: `dr.johnson` / `password123`
3. View assigned subjects (CS101, CS201)
4. See student lists and timetables

### Option C: Login as BCA Student
1. Go to login page
2. Enter: `janderson` / `password123`
3. View BCA program (6 semesters)
4. See enrolled subjects and timetable

### Option D: Login as MSC Student
1. Go to login page
2. Enter: `dmiller` / `password123`
3. View MSC program (4 semesters)
4. See research-focused subjects

## What's Available Now

✅ **7 Programs** - BCA, MSC, BSCS, BAEL, BSBI, BSMA, ASBA

✅ **34 Semesters** - Complete academic structure

✅ **70+ Subjects** - Full curriculum for all programs

✅ **10 Teachers** - All with subject assignments

✅ **11 Students** - Enrolled in various programs

✅ **50+ Timetable Slots** - Complete class schedules

✅ **20 Teacher Assignments** - Every subject has a teacher

## Test Scenarios You Can Now Try

### BCA Program Features
- Login as BCA student: `janderson` / `password123`
- View 6 semester curriculum
- See 24 subjects across all semesters
- Check timetable with multiple classes per day
- View class times: 9:00-10:30, 10:30-12:00, 12:00-13:30, 13:30-15:00

### Teacher Dashboard
- Login as teacher: `dr.johnson` / `password123`
- See assigned subjects: CS101 & CS201
- View student lists for each subject
- Check timetable slots:
  - CS101: MON/WED/FRI 09:00-10:30
  - CS201: MON/WED/FRI 12:00-13:30

### MSC Program Features
- Login as MSC student: `dmiller` / `password123`
- View 4 semester advanced curriculum
- See research methodology and thesis work
- Check advanced courses: Algorithms, Distributed Systems, ML, NLP

### Timetable System
- All classes have proper time slots
- No scheduling conflicts
- Multiple classes per day
- Working days: MON-SAT
- Working hours: 09:00-15:30

## If You Need to Re-seed

To clear and re-seed the database:

```bash
# Option 1: Just run the seeder again (it will insert fresh data)
php scripts/seed.php --force

# Option 2: Clear database manually first
# 1. Drop and recreate the ims_final database
# 2. Import database/ims_final_db.sql
# 3. Then run: php scripts/seed.php --force
```

## Troubleshooting

**Error: "Database connection failed"**
- Check XAMPP MySQL is running
- Verify database.php config has correct credentials
- Ensure ims_final database exists

**Error: "Table doesn't exist"**
- Import database/ims_final_db.sql first
- Then run: php scripts/seed.php

**Error: "Foreign key constraint fails"**
- This shouldn't happen - seeders run in correct order
- Try clearing database and re-importing schema first

## Documentation

For complete details, see:

📖 **SEED_DATA.md**
- Complete curriculum listings
- All subject codes and names
- Teacher assignments
- Timetable schedules
- Student enrollment details

📋 **TEST_DATA_SUMMARY.md**
- Quick reference guide
- Statistics on all seeded data
- Recommended test logins
- Key features checklist

## All Accounts at a Glance

| Role | Login ID | Program/Role | Password |
|------|----------|-------------|----------|
| Principal | principal.wilson | System Admin | password123 |
| Teacher | dr.johnson | CS101, CS201 | password123 |
| Teacher | prof.kumar | Physics, Chemistry | password123 |
| Teacher | prof.nair | MSC Advanced | password123 |
| Student | janderson | BCA Sem 1 | password123 |
| Student | dmiller | MSC Sem 1 | password123 |
| Student | nkumar | BSCS Sem 1 | password123 |

(See SEED_DATA.md for complete list of all 20+ accounts)

## Next Steps

1. ✅ Run seeder: `php scripts/seed.php`
2. ✅ Start your application
3. ✅ Login with test credentials
4. ✅ Explore BCA/MSC programs
5. ✅ Test timetable features
6. ✅ Try student/teacher/principal dashboards
7. ✅ Verify all features work with test data

**Your IMS system is now fully populated with comprehensive test data!**
