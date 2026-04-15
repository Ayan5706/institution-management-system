# ✅ WORKING TEST CREDENTIALS - APRIL 12, 2026

All test credentials are now verified and working. Use these to test the IMS Final system.

---

## 🔐 Login Credentials (All Verified)

Copy and paste directly into login form:

### 1. PRINCIPAL
```
Login ID:  principal
Password:  principal123
Email:     principal@imsschool.local
Role:      PRINCIPAL (Full system access)
```

### 2. VICE PRINCIPAL  
```
Login ID:  vp
Password:  vp123
Email:     vp@imsschool.local
Role:      VP (Operations and reporting)
```

### 3. MANAGER
```
Login ID:  manager
Password:  manager123
Email:     manager@imsschool.local
Role:      MANAGER (Program and schedule management)
```

### 4. ACCOUNTANT
```
Login ID:  accountant
Password:  accountant123
Email:     accountant@imsschool.local
Role:      ACCOUNTANT (Financial management)
```

### 5. TEACHER
```
Login ID:  teacher
Password:  teacher123
Email:     teacher@imsschool.local
Role:      TEACHER (Class and attendance)
```

### 6. STUDENT
```
Login ID:  student
Password:  student123
Email:     student@imsschool.local
Role:      STUDENT (Personal information only)
```

---

## 🚀 Quick Start

1. **Open Login Page**
   ```
   http://localhost/IMS_FINAL/public/login
   ```

2. **Clear Cache** (First time only)
   - Press: `Ctrl + Shift + Delete` (Windows) or `Cmd + Shift + Delete` (Mac)
   - Delete: Cookies and Passwords, Cached Images
   - Reload page

3. **Test Credentials**
   - Copy Login ID from above
   - Paste into Login ID field
   - Copy Password from above
   - Paste into Password field
   - Click **Login**

4. **Verify Dashboard**
   - Each role should show different dashboard
   - Check top menu for role-specific options
   - Click **Logout** to test another role

---

## ✅ Verification Checklist

- [x] All 6 credentials exist in database
- [x] All credentials are active (is_active = true)
- [x] All passwords use secure bcrypt hashing
- [x] All hashes verified with password_verify()
- [x] All roles can authenticate successfully
- [x] All dashboards load correctly
- [x] Session management working
- [x] Logout functionality working

---

## 📋 Expected Behavior

### Successful Login
```
Input: principal / principal123
↓
System: Finds user, verifies password
↓
Output: Redirects to /dashboard
        Shows: PRINCIPAL dashboard with full menu
```

### Failed Login
```
Input: principal / wrongpassword
↓
System: Finds user, password doesn't match
↓
Output: Error message: "Invalid credentials."
        User stays on login page
```

### Invalid User
```
Input: invaliduser / password123
↓
System: User not found in database
↓
Output: Error message: "Invalid credentials."
        User stays on login page
```

---

## 🔍 Troubleshooting

**Q: "Invalid credentials" error?**
- A: Clear browser cache (Ctrl+Shift+Delete)
- A: Check Login ID is lowercase: `principal` not `Principal`
- A: Check password is exact: `principal123` not `principal`

**Q: Page won't load after login?**
- A: You may not have access. Try different role
- A: Check browser console for JavaScript errors
- A: Try accessing: http://localhost/IMS_FINAL/public/dashboard directly

**Q: Stuck on login page?**
- A: Logout: http://localhost/IMS_FINAL/public/logout
- A: Clear browser cache again
- A: Close and reopen browser

**Q: Can't find login page?**
- A: URL: http://localhost/IMS_FINAL/public/login
- A: Check XAMPP is running (Apache + MySQL)
- A: Check no other app using port 80/443

---

## 📱 Multi-Device Testing

| Device | Test These Credentials |
|--------|------------------------|
| Desktop Browser | All credentials |
| Mobile Browser | All credentials |
| Tablet Browser | All credentials |
| Different Browsers | principal, teacher, student |

---

## 🎯 Testing Scenarios

### Scenario 1: Full Access Test
```
1. Login: principal / principal123
2. Verify: See full admin dashboard
3. Verify: All menu options visible
4. Verify: Can access all sections
5. Logout and repeat with other roles
```

### Scenario 2: Role-Based Access Test
```
1. Login: teacher / teacher123
2. Verify: See teacher-specific dashboard
3. Verify: Only teacher menu items show
4. Verify: Cannot access admin sections
5. Verify: Can only manage class/attendance
```

### Scenario 3: Invalid Credentials Test
```
1. Try: principal / wrong123
2. Verify: Get "Invalid credentials" error
3. Try: fakeuserid / password123
4. Verify: Get "Invalid credentials" error
5. Try: principal / (blank)
6. Verify: Get validation error
```

### Scenario 4: Session Test
```
1. Login: principal / principal123
2. Navigate: Around dashboard
3. Close: Browser tab
4. Open new tab: http://localhost/IMS_FINAL/public/dashboard
5. Verify: Still logged in (session persists)
6. Logout and verify redirects to login
```

---

## 🛠️ Database Verification

To verify credentials in database:

1. Open: http://localhost/phpmyadmin
2. Database: `ims_final`
3. Table: `users`
4. Query:
   ```sql
   SELECT login_id, email, role, is_active 
   FROM users 
   WHERE login_id IN ('principal', 'vp', 'manager', 'accountant', 'teacher', 'student')
   ORDER BY role;
   ```
5. Should show 6 rows with all users active

---

## 🔐 What's Fixed

**Previous Issue**: Placeholder password hashes didn't match passwords
- Hash: `$2y$10$9pZLbRrQKPuGHxLqBKc2...` (wrong)
- Result: "Invalid credentials" when trying any password

**Current Status**: Correct bcrypt hashes for each password
- Principal Hash: `$2y$10$LmsuMyfKKYMbLg7fyW8HW...` ✅
- VP Hash: `$2y$10$z70aX/20F3fgbZf6eSVfEe...` ✅
- Manager Hash: `$2y$10$2qswEgplmUIH4erkzfEwKe...` ✅
- Accountant Hash: `$2y$10$Tq6nLXsdPv6LIc/ZfNcRZe...` ✅
- Teacher Hash: `$2y$10$nrvioOIX6djwROOG5K.4ku...` ✅
- Student Hash: `$2y$10$SD08NE4/JaaTKMKo5oSw4e...` ✅

All hashes are cryptographically verified and working.

---

## 📞 Support

If credentials still don't work:

1. **Check System Status**
   - XAMPP: Running? Apache ✅ MySQL ✅
   - Database: Exists? Tables exist?
   - Login page: Loads correctly?

2. **Check Credentials**
   - Copy exact text including case
   - Paste directly (no typos)
   - Check for extra spaces

3. **Clear Everything**
   - Clear browser cache completely
   - Delete cookies for localhost
   - Close all browser windows
   - Clear PHP session files: `/storage/sessions/*`

4. **Last Resort**
   - Restart XAMPP completely
   - Reload database from backup
   - Reimport test credentials
   - Restart browser and try again

---

## ✅ You're Ready!

All test credentials are working. Pick any credential above and start testing the IMS Final system right now! 🚀

**Next Steps:**
1. Open: `http://localhost/IMS_FINAL/public/login`
2. Select a credential from above
3. Enter login ID and password
4. Click Login
5. Enjoy testing! 🎉

---

**Status**: ✅ All credentials verified and working  
**Last Updated**: April 12, 2026  
**System**: IMS Final (Institutional Management System)
