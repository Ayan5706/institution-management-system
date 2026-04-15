# Principal Module - Quick Testing Guide

## Quick Verification Steps

### 1. **Verify Routes Are Configured**
```bash
# Check that routes are defined
grep -n "principal" routes/web.php | head -20
# Expected: 20 routes starting with /principal
```

### 2. **Verify Controller Methods Exist**
```bash
# Check that all methods are implemented
grep -n "public function" app/Controllers/PrincipalController.php
# Expected: 20 public methods
```

### 3. **Verify View Files Exist**
```bash
# Check all view files are created
ls -la app/Views/principal/
# Expected: 6 .php files
```

### 4. **Verify Sidebar Navigation**
- Open app/Views/layouts/app.php
- Search for "if ($userRole === 'PRINCIPAL')"
- Verify 6 navigation links exist for Principal role

---

## Manual Testing Workflow

### Setup
1. **Create Principal User in Database**
   ```sql
   INSERT INTO users (
       role, login_id, email, full_name, password_hash, is_active, created_at
   ) VALUES (
       'PRINCIPAL', 'principal_001', 'principal@school.edu', 'Principal Name',
       '$2y$10$...', 1, NOW()
   );
   ```

2. **Create Admin Users (for Principal to manage)**
   ```sql
   -- Create VP
   INSERT INTO users (role, login_id, email, full_name, password_hash, is_active)
   VALUES ('VP', 'vp_001', 'vp@school.edu', 'VP Name', '$2y$10$...', 1);
   
   -- Create Manager
   INSERT INTO users (role, login_id, email, full_name, password_hash, is_active)
   VALUES ('MANAGER', 'mgr_001', 'manager@school.edu', 'Manager Name', '$2y$10$...', 1);
   
   -- Create Accountant
   INSERT INTO users (role, login_id, email, full_name, password_hash, is_active)
   VALUES ('ACCOUNTANT', 'acc_001', 'accountant@school.edu', 'Accountant', '$2y$10$...', 1);
   ```

---

## Test Cases

### Test 1: Dashboard Access
**Action:** Login as Principal and visit `/principal`
**Expected:** Dashboard with stats card showing:
- Total Students count
- Total Teachers count
- Active Programs count
- Pending password resets count

**Verification Checklist:**
- [ ] Page loads without errors
- [ ] All 4 statistics display
- [ ] Quick action buttons visible (6 buttons)
- [ ] Pending actions section shows
- [ ] "View Audit Log" button works

---

### Test 2: Account Management
**Action:** Navigate to `/principal/accounts`
**Expected:** Table listing VP, Manager, and Accountant accounts

**Test Cases:**
1. **View Accounts**
   - [ ] All admin accounts display in table
   - [ ] Columns show: Name, Email, Role, Status, Last Login, Actions
   - [ ] Status badges display (Active/Inactive)
   - [ ] Role badges display (VP, Manager, Accountant)

2. **Create Account**
   - [ ] Navigate to create form
   - [ ] Fill in: Full Name, Login ID, Email, Phone, Role
   - [ ] Submit form
   - [ ] Temporary password displayed
   - [ ] New account appears in list

3. **Toggle Status**
   - [ ] Click "Deactivate" on active account
   - [ ] Confirm deactivation
   - [ ] Status changes to Inactive
   - [ ] Revert by activating again

---

### Test 3: Student Records
**Action:** Navigate to `/principal/students`
**Expected:** List of all enrolled students

**Test Cases:**
1. **View Students**
   - [ ] Table displays all students
   - [ ] Columns: Student ID, Name, Email, Program, Enrollment Date, Status
   - [ ] Data populates correctly

2. **Search**
   - [ ] Type student name in search box
   - [ ] Table filters in real-time
   - [ ] Only matching students display

3. **Filter**
   - [ ] Select program from dropdown
   - [ ] Table updates to show only selected program
   - [ ] Can combine search + filter

4. **View Details**
   - [ ] Click on student name
   - [ ] Detailed view loads (if implemented)
   - [ ] All student information displays

---

### Test 4: Faculty Records
**Action:** Navigate to `/principal/teachers`
**Expected:** List of all teaching staff with statistics

**Test Cases:**
1. **Statistics**
   - [ ] Total Faculty count displays
   - [ ] Active Teachers count displays
   - [ ] On Leave count displays
   - [ ] Numbers are accurate

2. **View Teachers**
   - [ ] Table displays all teachers
   - [ ] Columns: Name, Email, Department, Qualification, Hire Date, Status

3. **Filter**
   - [ ] Filter by department works
   - [ ] Filter by status works
   - [ ] Can apply both filters together

4. **Search**
   - [ ] Search by name works
   - [ ] Search by email works
   - [ ] Real-time filtering

---

### Test 5: System Configuration
**Action:** Navigate to `/principal/config`
**Expected:** Configuration interface with multiple sections

**Test Cases:**
1. **View Configuration**
   - [ ] Academic Settings section displays
   - [ ] Attendance Policies section displays
   - [ ] Financial Settings section displays
   - [ ] System Settings section displays

2. **Update Settings**
   - [ ] Edit academic year (if editable)
   - [ ] Toggle "Allow Late Arrivals"
   - [ ] Toggle "Enable Online Payments"
   - [ ] Toggle "System Notifications"
   - [ ] Settings persist on refresh

3. **Numeric Validation**
   - [ ] Enter negative number in attendance percentage
   - [ ] Should show validation error or reject
   - [ ] Enter valid value (75) - should accept

---

### Test 6: Password Reset Approvals
**Action:** Navigate to `/principal/password-resets`
**Expected:** List of pending password reset requests

**Test Cases:**
1. **View Pending Requests** (if any exist)
   - [ ] Pending requests display
   - [ ] Request details show (name, email, role, reason)
   - [ ] Priority badges display (High, Medium, Low)
   - [ ] Approve and Reject buttons present

2. **Approve Request**
   - [ ] Click "Approve & Send Reset Link"
   - [ ] Confirm action
   - [ ] Success message with temporary password
   - [ ] Request moves from pending

3. **Reject Request**
   - [ ] Create another pending request (if available)
   - [ ] Click "Reject"
   - [ ] Confirm action
   - [ ] Success message displayed
   - [ ] Request moves from pending

4. **Empty State**
   - [ ] If no pending requests
   - [ ] Show "No Pending Requests" message
   - [ ] Display checkmark icon

---

### Test 7: Audit Log
**Action:** Navigate to `/principal/audit-log`
**Expected:** System activity log with filtering capabilities

**Test Cases:**
1. **View Audit Log**
   - [ ] Audit log entries display in table
   - [ ] Columns: Timestamp, User, Action, Entity Type, Entity ID, Details, IP Address
   - [ ] Entries sorted by date (newest first)

2. **Statistics**
   - [ ] Total Entries count displays
   - [ ] This Week count displays
   - [ ] Active Users count displays
   - [ ] Deletions count displays

3. **Search**
   - [ ] Search by user name works
   - [ ] Search by action works
   - [ ] Search by entity works
   - [ ] Real-time filtering

4. **Filter**
   - [ ] Filter by action type works
   - [ ] Filter by date range works
   - [ ] Multiple filters work together

5. **Pagination**
   - [ ] If many records, pagination shows
   - [ ] Can navigate between pages
   - [ ] Items per page works

---

### Test 8: API Endpoints

**1. Dashboard API**
```bash
curl -H "Authorization: Bearer TOKEN" http://localhost/api/principal/dashboard
```
Expected Response:
```json
{
    "success": true,
    "data": {
        "total_students": 150,
        "total_teachers": 25,
        "total_programs": 3,
        "pending_resets": 0
    }
}
```

**2. Users API**
```bash
curl "http://localhost/api/principal/users?role=VP"
```
Expected: List of VP accounts in JSON

**3. Students API**
```bash
curl "http://localhost/api/principal/students?program=BS%20Computer"
```
Expected: Filtered list of students

**4. Teachers API**
```bash
curl "http://localhost/api/principal/teachers?status=active"
```
Expected: Filtered list of active teachers

**5. Audit Log API**
```bash
curl "http://localhost/api/principal/audit-log?action=CREATE&limit=50"
```
Expected: Paginated audit log entries

---

### Test 9: Navigation & Sidebar
**Action:** View sidebar when logged in as Principal
**Expected:** Principal-specific navigation

**Verification:**
- [ ] Dashboard link (top)
- [ ] Manage Accounts link
- [ ] Students link
- [ ] Teachers link
- [ ] Settings link
- [ ] Password Requests link
- [ ] Audit Log link
- [ ] Logout button

**Test:**
- [ ] Click each link - pages load correctly
- [ ] Active nav item highlights
- [ ] Can navigate between sections smoothly

---

### Test 10: Security & Permissions
**Action:** Test access control

**Test Cases:**
1. **Logged Out User**
   - [ ] Cannot access `/principal` - redirects to login

2. **Non-Principal User**
   - [ ] Login as VP/Manager/Student
   - [ ] Try to access `/principal`
   - [ ] Should get 403 Forbidden or redirect

3. **Principal User**
   - [ ] Login as Principal
   - [ ] Can access all `/principal/*` routes
   - [ ] Cannot access admin-only routes (if different)

---

## Performance Testing

### Load Testing
1. **Dashboard Load Time**
   - Should load in < 1 second
   - Check browser DevTools Network tab

2. **Student List (large dataset)**
   - Upload 10,000+ student records
   - List should still load reasonably fast (< 3 seconds)

3. **Audit Log (large log)**
   - Ensure pagination works with 100,000+ log entries
   - Single page should load quickly

---

## Browser Compatibility

Test in:
- [ ] Chrome (latest)
- [ ] Firefox (latest)
- [ ] Safari (if on Mac)
- [ ] Edge (latest)
- [ ] Mobile Safari (iOS)
- [ ] Chrome Mobile (Android)

Expected: All features work across all browsers without visual issues

---

## Final Verification Checklist

- [ ] All 20 routes configured and working
- [ ] All 6 views display correctly
- [ ] Sidebar navigation complete
- [ ] All 20 controller methods implemented
- [ ] Role-based access control enforced
- [ ] UI responsive on all screen sizes
- [ ] Forms validate input properly
- [ ] Error messages display clearly
- [ ] API endpoints return proper JSON
- [ ] Audit logging working for all actions
- [ ] Password reset workflow complete
- [ ] Database integration functional
- [ ] No JavaScript console errors
- [ ] No 404 or 500 errors on valid routes

---

## Sign-off

**Tested By:** ___________________  
**Date:** ___________________  
**Status:** ✅ Ready for Production  

**Notes:**
_______________________________________________________________________
