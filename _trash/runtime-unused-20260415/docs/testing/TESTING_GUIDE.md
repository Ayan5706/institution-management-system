# Landing Page Testing Guide

## Quick Start

Your landing page is now ready to test. Follow these steps to verify everything is working:

---

## Test 1: View Landing Page

**Steps:**
1. Clear your browser cache:
   - Chrome: `Ctrl+Shift+Delete` → Select "All time" → Clear data
   - Firefox: `Ctrl+Shift+Delete` → Select "Everything" → Clear Now
   - Edge: `Ctrl+Shift+Delete` → Clear Now

2. Visit the landing page:
   ```
   http://localhost/IMS_FINAL/public/
   ```

**Expected Results:**
- ✅ Clean, modern landing page displays
- ✅ Navigation header with logo/title
- ✅ Hero section with gradient text
- ✅ 6 feature cards visible (Student Management, Attendance, Fees, etc.)
- ✅ "Login" button prominently displayed
- ✅ Responsive layout (try resizing browser)
- ✅ Smooth animations when page loads

---

## Test 2: Login Button Navigation

**Steps:**
1. On the landing page, click the **"Login"** button (or login link in nav)

**Expected Results:**
- ✅ Redirects to login page: `http://localhost/IMS_FINAL/public/login`
- ✅ Login form displays
- ✅ No error messages

---

## Test 3: User Login Flow

**Steps:**
1. Use test credentials:
   - **Email:** admin@imsystem.local
   - **Password:** password (or as configured in your database)

2. Or use username:
   - **Username:** admin
   - **Password:** password

**Expected Results:**
- ✅ Login form accepts credentials
- ✅ After submit, redirects to dashboard: `http://localhost/IMS_FINAL/public/dashboard`
- ✅ Dashboard loads showing user name/role
- ✅ Navigation bar shows authenticated user menu

---

## Test 4: Dashboard Navigation

**Steps:**
1. From dashboard, click **"Dashboard"** in navigation

**Expected Results:**
- ✅ Stays on dashboard view
- ✅ URL: `http://localhost/IMS_FINAL/public/dashboard`

---

## Test 5: Logout and Return to Landing

**Steps:**
1. Click the **"Logout"** button in the navigation

**Expected Results:**
- ✅ User session ends
- ✅ Browser redirects to landing page
- ✅ Can view landing page again
- ✅ Cannot access dashboard without re-logging in

---

## Test 6: Authenticated User Redirect

**Steps:**
1. After logging in and being on dashboard...
2. Try to manually go to landing page: `http://localhost/IMS_FINAL/public/`

**Expected Results:**
- ✅ Automatically redirects to dashboard
- ✅ Authenticated users cannot see landing page
- ✅ URL changes to: `http://localhost/IMS_FINAL/public/dashboard`

---

## Test 7: Guest Access Only

**Steps:**
1. Make sure you're logged out
2. Try to access dashboard: `http://localhost/IMS_FINAL/public/dashboard`

**Expected Results:**
- ✅ Redirects to login page
- ✅ Shows error or redirects gracefully
- ✅ Cannot access protected areas without authentication

---

## Test 8: Responsive Design

**Steps:**
1. On landing page, open browser DevTools: `F12`
2. Click responsive design toggle (or `Ctrl+Shift+M`)
3. Test different screen sizes:
   - Mobile: 375px width
   - Tablet: 768px width
   - Desktop: 1920px width

**Expected Results:**
- ✅ Landing page adapts to all screen sizes
- ✅ Feature cards stack on mobile (1 per row)
- ✅ Tablet shows 2 cards per row
- ✅ Desktop shows 3 cards per row
- ✅ Navigation adjusts properly
- ✅ All text remains readable

---

## Test 9: Browser Compatibility

Test on different browsers:

**Chrome/Chromium:**
- [ ] Landing page displays
- [ ] Animations work smoothly
- [ ] Forms work

**Firefox:**
- [ ] Landing page displays
- [ ] Animations work smoothly
- [ ] Forms work

**Edge:**
- [ ] Landing page displays
- [ ] Animations work smoothly
- [ ] Forms work

**Safari (if on Mac):**
- [ ] Landing page displays
- [ ] Animations work smoothly
- [ ] Forms work

---

## Test 10: Direct URL Access

**Test various URLs:**

1. `http://localhost/IMS_FINAL/public/`
   - Expected: Landing page

2. `http://localhost/IMS_FINAL/public/dashboard`
   - Expected: Redirects to login if not authenticated, shows dashboard if logged in

3. `http://localhost/IMS_FINAL/public/login`
   - Expected: Login page

4. `http://localhost/IMS_FINAL/public/logout`
   - Expected: POST only, logs out if authenticated

5. `/nonexistent-page`
   - Expected: 404 error page

---

## Troubleshooting

### Landing page shows "Route not found"

**Solution:**
1. Check that `.htaccess` exists in `public/` folder
2. Verify Apache `mod_rewrite` is enabled
3. Clear browser cache completely
4. Try: `http://localhost/IMS_FINAL/public/?page=/`

### Landing page displays but styling is broken

**Solution:**
1. Hard refresh browser: `Ctrl+F5` (or `Cmd+Shift+R` on Mac)
2. Clear browser cache
3. Check developer console for CSS errors (`F12` → Console)

### Login form not working

**Solution:**
1. Verify user account exists in database
2. Check password is correct
3. Ensure user account is marked as "active"
4. Check database connection: `php check-users.php`

### Logout throws error

**Solution:**
1. Make sure logout form has CSRF token
2. Verify POST request is being sent (not GET)
3. Check browser console for errors

### Dashboard shows but styling is off

**Solution:**
1. Hard refresh: `Ctrl+F5`
2. Clear browser cache
3. Check for JavaScript errors in console

### Authenticated user not redirecting to dashboard

**Solution:**
1. Check session is being created during login
2. Verify middleware is configured
3. Check `/app/Middleware/Auth.php` exists

---

## Performance Testing

### Landing Page Load Time

1. Open DevTools: `F12`
2. Go to Network tab
3. Visit landing page
4. Check load times:
   - Target: < 2 seconds full load
   - Acceptable: < 3 seconds

### Database Performance

1. Run: `php verify-database.php`
2. Check connection time: should be < 100ms

---

## Security Checks

- [ ] Landing page doesn't expose sensitive info
- [ ] Login form has CSRF protection
- [ ] Password fields use `type="password"`
- [ ] No console errors about mixed content
- [ ] All forms submit over HTTPS (if deployed)
- [ ] Session cookies are secure

---

## Feature Verification Checklist

- [ ] Landing page displays 6 feature cards
- [ ] Each card shows: Icon, Title, Description
- [ ] Feature cards are clickable/hoverable
- [ ] Navigation header is sticky (stays at top while scrolling)
- [ ] Login button in header works
- [ ] Primary CTA button in hero section works
- [ ] Secondary CTA section below features
- [ ] Footer displays with copyright
- [ ] All links use proper routing (no `#` links)

---

## Success Criteria

**Landing Page is working correctly if:**
- ✅ Displays on opening in browser
- ✅ All styling/animations render
- ✅ Login button redirects to login
- ✅ Login works with valid credentials
- ✅ Redirects to dashboard after login
- ✅ Logout returns to landing/login
- ✅ Responsive design works on mobile
- ✅ Works on Chrome, Firefox, Edge
- ✅ Authenticated users auto-redirect away

---

## Next Steps

If all tests pass:
1. ✅ Landing page is production-ready
2. ✅ Deploy to staging environment
3. ✅ Have team test UX/UI
4. ✅ Gather feedback on design
5. ✅ Deploy to production

If issues found:
1. Review the troubleshooting section
2. Check server error logs: `logs/` folder
3. Run: `php verify-landing-page.php`
4. Check browser console for JavaScript errors

---

## Database Check

To verify your test users are set up, run:

```bash
php check-users.php
```

This will show all users in the database including their roles.

---

## Performance Baseline

After testing, your metrics should be approximately:
- Landing page load: < 2-3 seconds
- Login processing: < 1-2 seconds
- Dashboard load: < 2-3 seconds
- Database connection: < 100ms

---

## Support

If testing reveals issues:
1. Check `logs/` folder for error details
2. Enable debug mode in `config/app.php` (set `DEBUG = true`)
3. Run individual verification scripts in project root
4. Check browser console (`F12` → Console tab)

---

**Testing completed by:** _______________

**Date:** _______________

**All tests passed:** [ ] Yes [ ] No

**Issues found:** (if any)

```



```

---

**Sign-off:**

Date: _______________

Tester Name: _______________

Approval: _______________
