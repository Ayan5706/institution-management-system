# IMS Test Credentials Guide

**Last Updated:** April 12, 2026  
**Status:** ✅ All Test Credentials Working & Verified

---

## 🎯 Quick Reference - Working Test Credentials

Use these credentials to test different role-based dashboards:

| Role | Login ID | Password | Email | Status |
|------|----------|----------|-------|--------|
| **Principal** | `principal` | `principal123` | principal@imsschool.local | ✅ Working |
| **Vice Principal** | `vp` | `vp123` | vp@imsschool.local | ✅ Working |
| **Manager** | `manager` | `manager123` | manager@imsschool.local | ✅ Working |
| **Accountant** | `accountant` | `accountant123` | accountant@imsschool.local | ✅ Working |
| **Teacher** | `teacher` | `teacher123` | teacher@imsschool.local | ✅ Working |
| **Student** | `student` | `student123` | student@imsschool.local | ✅ Working |

---

## ✅ Issue Fixed

### Problem
Initial test credentials showed "Invalid credentials" when attempting to log in. This was because the password hashes in the database did not match the provided passwords.

### Root Cause
The password hashes were placeholder values that didn't correspond to the actual passwords. The authentication system uses bcrypt password hashing, which requires cryptographically secure random hashes.

### Solution Applied
Generated correct bcrypt hashes for each credential:
- **Principal**: `principal123` → `$2y$10$LmsuMyfKKYMbLg7fyW8HW.zb9pfIll8tLYnJP9IR1wCIQnHFPWioG`
- **VP**: `vp123` → `$2y$10$z70aX/20F3fgbZf6eSVfEe1PRdLNVQKzxwHEMw0hLbBdbBqRzI3jq`
- **Manager**: `manager123` → `$2y$10$2qswEgplmUIH4erkzfEwKeqbf6u.jLwnrr40k.wXsyXsPZUFIvX1i`
- **Accountant**: `accountant123` → `$2y$10$Tq6nLXsdPv6LIc/ZfNcRZeSr5ufmSqQVNi2swyrbmIGiuE/xfzJEa`
- **Teacher**: `teacher123` → `$2y$10$nrvioOIX6djwROOG5K.4ku1mpTHGRi0vkjlgmdadIZwdIEuFEI/EC`
- **Student**: `student123` → `$2y$10$SD08NE4/JaaTKMKo5oSw4e1OJBWLnM6FlkcSM9f.r4hnKIkFkRuHy`

All hashes verified with `password_verify()` to ensure 100% authentication compatibility.

---

## Getting Started

### Step 1: Test Login Immediately
No additional setup needed! The credentials are already in the database.

1. Open: `http://localhost/IMS_FINAL/public/login`
2. Enter any Login ID from the table above
3. Enter the corresponding password
4. Click Login
5. ✅ You should be redirected to the dashboard

### Step 2: Test Each Role
Test each credential to verify role-based access works:

```
Principal:  principal / principal123 → Full system access
VP:         vp / vp123 → Operations and reporting
Manager:    manager / manager123 → Program and schedule management
Accountant: accountant / accountant123 → Financial management
Teacher:    teacher / teacher123 → Class and attendance
Student:    student / student123 → Personal information
```

### Step 3: Browser Cache
If you experience any issues:
1. Clear browser cache: `Ctrl+Shift+Delete` (or `Cmd+Shift+Delete` on Mac)
2. Close and reopen browser
3. Try logging in again
Navigate to: `http://localhost/IMS_FINAL/public/login`

Use any of the credentials above to test different role dashboards.

---

## Role Capabilities Overview

### 1. Principal (`principal` / `principal123`)
- **System Access:** Full system access
- **Capabilities:**
  - Manage all users and roles
  - Configure system settings
  - View all reports
  - Manage programs and subjects
  - Handle student enrollments
  - Approve all transactions
- **Dashboard:** Principal Dashboard (access to all modules)

### 2. Vice Principal (`vp` / `vp123`)
- **System Access:** Operations and reporting
- **Capabilities:**
  - Manage staff and teachers
  - Oversee attendance
  - Generate reports
  - Manage academic calendar
  - View student records
  - Approve leave requests
- **Dashboard:** VP Dashboard (operations-focused)

### 3. Manager (`manager` / `manager123`)
- **System Access:** Program and schedule management
- **Capabilities:**
  - Create and manage programs
  - Setup subjects and classes
  - Manage timetables
  - Assign teachers to subjects
  - View class-level reports
  - Manage class schedules
- **Dashboard:** Manager Dashboard (operations)

### 4. Accountant (`accountant` / `accountant123`)
- **System Access:** Financial management
- **Capabilities:**
  - Manage student fees
  - Configure fee structures
  - Process fee payments
  - Generate financial reports
  - View transaction history
  - Manage fee schemes
- **Dashboard:** Accountant Dashboard (financial)

### 5. Teacher (`teacher` / `teacher123`)
- **System Access:** Class and attendance management
- **Capabilities:**
  - Mark attendance
  - View assigned classes
  - Record grades (if configured)
  - View student information
  - Access timetables
  - Generate class reports
- **Dashboard:** Teacher Dashboard (class-focused)

### 6. Student (`student` / `student123`)
- **System Access:** Personal information
- **Capabilities:**
  - View own profile
  - View attendance record
  - Check fee status
  - View class schedule
  - View academic records
  - Track payments
- **Dashboard:** Student Dashboard (limited access)

---

## Testing Workflow

### Test All Role Dashboards

Use this workflow to verify each role's dashboard functions:

```
1. Login with: principal / principal123
   ✓ Verify principal dashboard loads
   ✓ Check all menu items visible
   ✓ Logout
   
2. Login with: vp / vp123
   ✓ Verify VP dashboard loads
   ✓ Check VP-specific features
   ✓ Logout
   
3. Login with: manager / manager123
   ✓ Verify manager dashboard loads
   ✓ Check program management
   ✓ Logout
   
4. Login with: accountant / accountant123
   ✓ Verify accountant dashboard loads
   ✓ Check fee management
   ✓ Logout
   
5. Login with: teacher / teacher123
   ✓ Verify teacher dashboard loads
   ✓ Check attendance features
   ✓ Logout
   
6. Login with: student / student123
   ✓ Verify student dashboard loads
   ✓ Check limited menu
   ✓ Logout
```

---

## Database Information

### Users Table
After seeding, the `users` table contains:

```sql
SELECT login_id, role, is_active, email FROM users ORDER BY role;
```

**Expected Result:**
```
| login_id | role       | is_active | email                    |
|----------|-----------|-----------|--------------------------|
| principal| PRINCIPAL | 1         | principal@imsschool.local|
| vp       | VP        | 1         | vp@imsschool.local      |
| manager  | MANAGER   | 1         | manager@imsschool.local |
| accountant| ACCOUNTANT| 1         | accountant@imsschool.local|
| teacher  | TEACHER   | 1         | teacher@imsschool.local |
| student  | STUDENT   | 1         | student@imsschool.local |
```

---

## Verification Checklist

After seeding, verify:

- [ ] Database connection working
- [ ] All 6 test accounts created
- [ ] Each account has active status (is_active = 1)
- [ ] Passwords are hashed (starts with $2y$)
- [ ] All roles are correctly assigned
- [ ] Login page accessible
- [ ] Can login with principal account
- [ ] Dashboard loads after login
- [ ] Each role sees correct dashboard
- [ ] Logout works correctly

---

## Re-seeding the Database

### If test data gets corrupted or you want fresh data:

```bash
# Option 1: Full reset (clears everything, resurfaces all tables)
php scripts/cli.php migrate:fresh

# Option 2: Just reseed (keeps table structure, refreshes data)
php scripts/seed.php
```

### To re-seed with confirmation:
```bash
php scripts/seed.php
# System will ask for confirmation before proceeding
```

---

## Common Testing Scenarios

### Scenario 1: Test Role-Based Access
1. Login as `principal / principal123`
2. Verify can see all modules
3. Logout
4. Login as `student / student123`
5. Verify can only see limited modules

### Scenario 2: Test Feature Access
1. Login as `accountant / accountant123`
2. Check fee management is visible
3. Verify teacher menu NOT visible
4. Logout

### Scenario 3: Test Dashboard Customization
1. Login as any role
2. Verify dashboard shows role-specific widgets
3. Check sidebar shows role-specific menu items

### Scenario 4: Test Permission Enforcement
1. For testing permission enforcement:
   - Each role should NOT see other role's exclusive menu items
   - Accessing restricted pages should redirect to dashboard
   - All operations should be role-appropriate

---

## Credentials Storage

The credentials are stored in:
- **File:** `database/seeders/UsersTableSeeder.php`
- **Storage:** Database `users` table
- **Passwords:** Hashed with bcrypt

### Password Hashing
```php
password_hash('principal123', PASSWORD_BCRYPT)
// Produces: $2y$10$... (hashed)
```

**Note:** Passwords are NOT stored in plain text. They are hashed using bcrypt for security.

---

## Modifying Test Credentials

If you want to change credentials:

1. Edit `database/seeders/UsersTableSeeder.php`
2. Modify the `login_id` or `password` fields
3. Reseed the database:
   ```bash
   php scripts/seed.php
   ```

### Example: Change principal password
```php
'password' => password_hash('newpassword123', PASSWORD_BCRYPT),
```

Then reseed:
```bash
php scripts/seed.php
```

---

## Troubleshooting

### Issue: "User not found" after seeding
**Solution:**
1. Run seeding again: `php scripts/seed.php`
2. Check if database connection is working
3. Verify table exists: `SHOW TABLES;`

### Issue: Login fails with correct credentials
**Solution:**
1. Check is_active = 1 in database
2. Verify password is hashed correctly
3. Clear browser cache
4. Try different browser

### Issue: Dashboard doesn't load after login
**Solution:**
1. Check middleware configuration
2. Verify session is being created
3. Check role field matches supported values
4. Check server error logs

---

## Supported Roles

The system supports exactly these 6 roles (database enum):

```sql
ENUM('PRINCIPAL', 'VP', 'MANAGER', 'ACCOUNTANT', 'TEACHER', 'STUDENT')
```

**Important:** Login IDs are case-sensitive, but stored in lowercase. Always use:
- `principal` (not `Principal`)
- `vp` (not `VP`)
- `manager` (not `Manager`)
- `accountant` (not `Accountant`)
- `teacher` (not `Teacher`)
- `student` (not `Student`)

---

## Security Notes

### For Development/Testing:
- ✓ Simple passwords used for testing convenience
- ✓ Credentials documented for team access
- ✓ All accounts active and verified
- ⚠️ Change credentials before production deployment

### For Production:
- ⚠️ Use strong passwords (20+ characters)
- ⚠️ Remove dev/test accounts
- ⚠️ Create unique real user accounts
- ⚠️ Never share credentials in code/docs
- ⚠️ Use environment variables for sensitive data

---

## Next Steps

After standardizing credentials:

1. ✅ Test each role dashboard
2. ✅ Verify feature access by role
3. ✅ Test logout functionality
4. ✅ Document observed behaviors
5. ✅ Plan permission adjustments if needed
6. ✅ Create production credentials

---

## Reference

**Related Files:**
- [database/seeders/UsersTableSeeder.php](database/seeders/UsersTableSeeder.php) - Test credentials definition
- [scripts/seed.php](scripts/seed.php) - Seeding runner
- [database/migrations/](database/migrations/) - Database schema

**System Documentation:**
- [HOW_TO_RUN.md](HOW_TO_RUN.md) - Setup guide
- [README.md](README.md) - Project overview

---

**Last Updated:** April 12, 2026  
**Version:** 1.0 - Standardized Test Credentials  
**Status:** Ready for Testing
