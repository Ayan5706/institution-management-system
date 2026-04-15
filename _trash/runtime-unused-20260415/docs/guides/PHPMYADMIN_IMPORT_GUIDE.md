# How to Import Test Credentials via phpMyAdmin

**File:** `test_credentials_import.sql`

## Quick Steps

1. **Open phpMyAdmin**
   - Navigate to: `http://localhost/phpmyadmin`
   - Login if prompted

2. **Select the IMS Database**
   - Look for your database in the left panel
   - Click on it to select it

3. **Access SQL Tab**
   - Click the **SQL** tab at the top of the page

4. **Import the SQL File**
   - Open `test_credentials_import.sql` from the project root
   - Copy the entire contents
   - Paste into the SQL editor in phpMyAdmin

5. **Execute**
   - Click the **Go** button to execute
   - Wait for confirmation message

## What Gets Created

The script creates exactly 6 test accounts:

| Login ID | Role | Password |
|----------|------|----------|
| principal | PRINCIPAL | principal123 |
| vp | VP | vp123 |
| manager | MANAGER | manager123 |
| accountant | ACCOUNTANT | accountant123 |
| teacher | TEACHER | teacher123 |
| student | STUDENT | student123 |

## Verification

After importing, run this query in phpMyAdmin to verify:

```sql
SELECT login_id, role, is_active, email FROM users 
WHERE role IN ('PRINCIPAL', 'VP', 'MANAGER', 'ACCOUNTANT', 'TEACHER', 'STUDENT') 
ORDER BY role;
```

You should see 6 rows with all test accounts.

## Testing

1. Clear browser cache: `Ctrl+Shift+Delete`
2. Visit: `http://localhost/IMS_FINAL/public/login`
3. Login with any credentials from the table above
4. Verify correct dashboard loads for the role

## Optional: Clear Old Accounts First

If you have old test accounts and want to remove them before importing, uncomment this line at the top of the SQL file:

```sql
DELETE FROM users WHERE role IN ('PRINCIPAL', 'VP', 'MANAGER', 'ACCOUNTANT', 'TEACHER', 'STUDENT');
```

Then run the entire script.

## Troubleshooting

**"Table 'users' doesn't exist"**
- Make sure you selected the correct database (left panel)
- Ensure database is created and migrations ran

**"Duplicate entry for key 'login_id'"**
- Those accounts already exist
- Uncomment the DELETE line to clear them first
- Or delete manually before importing

**Login fails after import**
- Clear browser cache completely
- Hard refresh the page (Ctrl+F5)
- Verify password is exactly as shown in the table
- Check is_active = 1 in database

**"Access denied" error**
- Ensure database user has INSERT permissions
- Check .env file has correct DB credentials

## File Location

The SQL file is located in the project root:
- **Path:** `/test_credentials_import.sql`
- **Size:** ~2.3 KB
- **Encoding:** UTF-8
- **Format:** SQL statements (INSERT)

## Related Files

- [TEST_CREDENTIALS.md](TEST_CREDENTIALS.md) - Complete credentials guide
- [database/seeders/UsersTableSeeder.php](database/seeders/UsersTableSeeder.php) - PHP seeder version
- [scripts/seed.php](scripts/seed.php) - Command-line seeding script

## Support

For more information on:
- **Database setup:** See [HOW_TO_RUN.md](HOW_TO_RUN.md)
- **All roles and passwords:** See [TEST_CREDENTIALS.md](TEST_CREDENTIALS.md)
- **Command-line seeding:** Run `php scripts/seed.php`
