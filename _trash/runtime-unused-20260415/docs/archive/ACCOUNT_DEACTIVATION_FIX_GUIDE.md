# Account Deactivation Fix - Testing and Verification Guide

## Quick Start: Test the Fix

### Step 1: Verify Files Were Modified
The following files have been updated with critical fixes:

1. **app/Core/Router.php** (lines 155-200)
   - Added parameter type casting using PHP Reflection
   - Ensures URL parameters like {id} are properly converted from string to int

2. **app/Controllers/PrincipalController.php** (lines 219-280)
   - Already has comprehensive error handling with try-catch
   - Only updates `is_active` field (DB trigger handles timestamp)

3. **app/Views/principal/accounts.php** (line 674)
   - JavaScript uses `data.data.status === 1` for state determination

### Step 2: Test the Deactivate Button

1. Log in as Principal user
2. Navigate to: Manage Accounts section
3. Find any admin account (VP, Manager, or Accountant role)
4. Click the "Deactivate" button
5. Observe the result

### Expected Behavior
- Button shows "Processing..." temporarily
- Button updates to show "Activate" (status flipped)
- Account badge changes from "Active" to "Inactive"
- NO 500 error appears
- Response should be HTTP 200

### If You Still Get 500 Error

#### Step A: Check Browser Console
1. Press F12 to open Developer Tools
2. Go to "Console" tab
3. Try to deactivate an account again
4. Look for any JavaScript errors
5. Share the exact error message

#### Step B: Check Network Tab
1. Press F12 to open Developer Tools
2. Go to "Network" tab
3. Try to deactivate an account again
4. Find the PATCH request to `/principal/accounts/X/toggle`
5. Click on it and check:
   - Request URL: Should be exact format `/principal/accounts/{id}/toggle`
   - Request Method: Should be `PATCH`
   - Response Status: Should be 200 (or may be 500)
   - Response Body: Should contain JSON with `success` field

#### Step C: Check Server Logs
1. Open file: `C:\xampp\htdocs\IMS_FINAL\storage\logs\app.log`
2. Check the most recent entries for any error messages
3. Look for "Toggle account status error" messages

#### Step D: Check PHP Error Logs
1. Open file: `C:\xampp\apache\logs\error.log`
2. Scroll to bottom for most recent errors
3. Look for any PHP errors related to Principal controller

### Detailed Troubleshooting

#### Issue: "Route not found" (404 error)
**Cause**: PATCH request is not matching the route
**Solution**: 
- Verify route exists in `routes/web.php` line 213
- Check that HTTP method is PATCH (not POST or PUT)
- Clear any browser cache
- Restart Apache server

#### Issue: "500 error" still appearing
**Cause**: Type coercion or other backend error
**Solution**:
1. Check `app.log` in `storage/logs/` for detailed error
2. Verify database connection is working
3. Test with ID that definitely exists in database
4. Check if user has Principal role properly assigned

#### Issue: Button shows "Processing..." but nothing happens
**Cause**: Network request failed silently
**Solution**:
1. Check browser console (F12) for errors
2. Check Network tab to see if PATCH request was sent
3. Check if JavaScript toggleAccountStatus function exists in accounts.php
4. Verify authentication cookie is valid

### Verification Checklist

Use this checklist to verify each component is working:

- [ ] File modifications are in place (check file timestamps)
- [ ] PHP syntax is valid (no parse errors in files)
- [ ] Route is registered correctly
- [ ] Controller method exists and is callable
- [ ] JavaScript function sends correct PATCH request
- [ ] Account deactivation works without 500 error
- [ ] Account status changes in UI immediately
- [ ] Database `users` table is actually updated
- [ ] Audit log entry is created

### SQL Query to Verify Database Update

After attempting to deactivate an account, run this SQL query to verify the database was updated:

```sql
SELECT id, login_id, full_name, role, is_active, updated_at 
FROM users 
WHERE role IN ('VP', 'MANAGER', 'ACCOUNTANT')
ORDER BY updated_at DESC 
LIMIT 5;
```

The account you deactivated should appear in the results with `is_active = 0` (or 1 if you reactivated it) and a recent `updated_at` timestamp.

### If All Else Fails

If the issue persists after these checks:

1. **Capture detailed error information**:
   - Take screenshot of console error
   - Take screenshot of Network tab showing full request/response
   - Copy full text from app.log
   - Copy full text from error.log

2. **Provide the following information**:
   - Exact URL being accessed (e.g., `http://localhost/IMS_FINAL/public/principal/accounts`)
   - AWS database details (if applicable)
   - PHP version being used
   - MySQL version being used
   - Exact error message from browser and server logs

3. **Test alternative approach**:
   - Try deactivating a different admin account (to rule out user-specific issue)
   - Try refreshing the page and trying again
   - Try in a different browser

### Files Modified Summary

```
app/Core/Router.php
  - Added parameter type casting in runHandler() method
  - Converts URL string parameters to declared types
  - Added exception handling for Reflection failures

app/Controllers/PrincipalController.php
  - No changes needed (proper error handling already in place)
  - Verified toggleAccountStatus method is correct

app/Views/principal/accounts.php
  - No changes needed (JavaScript already uses correct response data)
```

### Technical Details

**Route**: `PATCH /principal/accounts/{id}/toggle`
**Handler**: `PrincipalController@toggleAccountStatus`
**Parameters**: `int $id` (account ID to toggle)
**Response**: JSON with structure:
```json
{
  "success": true,
  "message": "Account deactivated.",
  "data": {
    "status": 0
  }
}
```

**Middleware**: 
- `auth` - User must be authenticated
- `role:principal` - User must have Principal role

**Database Changes**:
- Table: `users`
- Field: `is_active` (toggled between 0 and 1)
- Timestamp: `updated_at` (automatically updated by DB trigger)

---

## Next Steps

1. **Test the fix** using the steps above
2. **Report any errors** with screenshot and log file contents
3. **Verify database** was actually updated using the SQL query
4. **Confirm success** when account deactivation works without errors

If you encounter any issues during testing, please provide the detailed error information from the troubleshooting section above.
