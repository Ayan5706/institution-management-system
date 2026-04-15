# How to Run the IMS Application

## Quick Start Guide

### Option 1: Using XAMPP Control Panel (Easiest)

1. **Start XAMPP Services**
   - Open XAMPP Control Panel
   - Click "Start" next to **Apache**
   - Click "Start" next to **MySQL** (for database)
   
2. **Access the Application**
   - Open your browser
   - Go to: `http://localhost/IMS_FINAL/public/`
   - You should see the login page

3. **Default Login Credentials**
   ```
   Email: admin@ims.local
   Password: admin123456
   ```

### Option 2: Using Command Line (Terminal/PowerShell)

#### Step 1: Start Apache and MySQL
```powershell
# Open PowerShell as Administrator, then run:
cd C:\xampp

# Start Apache
.\apache_start.bat

# In a new PowerShell window, start MySQL
.\mysql_start.bat
```

#### Step 2: Access Application
```
http://localhost/IMS_FINAL/public/
```

#### Step 3: Stop Services (when done)
```powershell
# In PowerShell (Admin):
.\apache_stop.bat
.\mysql_stop.bat
```

---

## Detailed Setup Instructions

### Initial Configuration

**1. Create `.env` File**
```bash
# In project root (C:\xampp\htdocs\IMS_FINAL\):
copy .env.example .env
```

**2. Edit `.env` File** (if needed)
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ims_final
DB_USERNAME=root
DB_PASSWORD=
APP_DEBUG=true
```

**3. Verify Database Exists**
- Open phpMyAdmin: `http://localhost/phpmyadmin`
- Check if `ims_final` database exists
- If not, run migrations:
  ```bash
  cd C:\xampp\htdocs\IMS_FINAL
  php scripts/migrate.php
  ```

---

## Running the Application

### Method 1: Browser (Recommended)

1. **Ensure XAMPP is running**
   - Apache: Status = Running
   - MySQL: Status = Running

2. **Open in Browser**
   ```
   http://localhost/IMS_FINAL/public/
   ```

3. **Login**
   - Email: `admin@ims.local`
   - Password: `admin123456`

---

### Method 2: Using PHP Built-in Server

```bash
cd C:\xampp\htdocs\IMS_FINAL

# Start built-in server on port 8000
php -S localhost:8000 -t public/
```

Then open: `http://localhost:8000/`

---

### Method 3: Using CLI Commands

```bash
cd C:\xampp\htdocs\IMS_FINAL
php scripts/cli.php
```

This shows available CLI commands:
- `migrate.php` - Run database migrations
- `seed.php` - Seed database with sample data
- `health-check.php` - Check system health
- `user-manage.php` - Manage users
- And more...

---

## Verification Tests

### Run Application Verification
```bash
cd C:\xampp\htdocs\IMS_FINAL

# Test 1: Verify application structure
php verify-application.php

# Test 2: Verify routing
php verify-routing.php

# Test 3: Verify database connection
php verify-database.php
```

Expected results:
- ✅ 18/20 tests passed
- ✅ Application ready
- ✅ Database connected

---

## Troubleshooting

### Issue: "Connection refused" or "Cannot connect to database"

**Solution:**
1. Make sure MySQL is running in XAMPP
2. Check `.env` file has correct database credentials
3. Verify `ims_final` database exists

```bash
# Run database setup
cd C:\xampp\htdocs\IMS_FINAL
php scripts/migrate.php    # Create tables
php scripts/seed.php       # Add sample data
```

### Issue: "404 Not Found"

**Solution:**
1. Ensure URL is: `http://localhost/IMS_FINAL/public/`
2. Check Apache is running
3. Verify `public/index.php` exists

### Issue: "Class not found" or errors

**Solution:**
```bash
cd C:\xampp\htdocs\IMS_FINAL

# Check PHP syntax
php -l verify-application.php

# If there are syntax errors, fix them
```

---

## Application Structure

```
http://localhost/IMS_FINAL/public/
├── login             → Authentication
├── /                 → Dashboard (requires login)
├── /users            → User management
├── /programs         → Programs
├── /students         → Student profiles
├── /attendance       → Attendance tracking
├── /fees             → Fees management
├── /reports          → Reports
└── /admin            → Admin panel
```

---

## Key URLs

| Feature | URL |
|---------|-----|
| Login | `http://localhost/IMS_FINAL/public/login` |
| Dashboard | `http://localhost/IMS_FINAL/public/` |
| Users | `http://localhost/IMS_FINAL/public/users` |
| Programs | `http://localhost/IMS_FINAL/public/programs` |
| Students | `http://localhost/IMS_FINAL/public/students` |
| Attendance | `http://localhost/IMS_FINAL/public/attendance` |
| Fees | `http://localhost/IMS_FINAL/public/fees` |
| Reports | `http://localhost/IMS_FINAL/public/reports` |

---

## Sample Test Data

### Default Users (after seeding)
```
Admin Account:
  Email: admin@ims.local
  Password: admin123456
  Role: Admin

Teacher Account:
  Email: teacher@ims.local
  Password: teacher123456
  Role: Teacher

Student Account:
  Email: student@ims.local
  Password: student123456
  Role: Student
```

---

## Running Tests

### PHPUnit Tests (if Composer is installed)
```bash
cd C:\xampp\htdocs\IMS_FINAL
composer install
php vendor/bin/phpunit
```

---

## Performance Optimization

### Enable Caching
```php
// Already configured in app/Config/config.php
CACHE_ENABLED = true
CACHE_TTL = 3600
```

### Database Optimization
```bash
cd C:\xampp\htdocs\IMS_FINAL
php scripts/optimize-database.php
```

---

## Common Commands

```bash
cd C:\xampp\htdocs\IMS_FINAL

# View application health
php scripts/health-check.php

# Backup database
php scripts/backup.php

# Reset database
php scripts/reset-database.php

# Export data
php scripts/data-export.php

# Fix permissions
php scripts/permissions.php
```

---

## Summary

**Quickest Way to Run:**

1. Start XAMPP (Apache + MySQL)
2. Go to: `http://localhost/IMS_FINAL/public/`
3. Login with: `admin@ims.local` / `admin123456`

That's it! The application is ready to use.

**For Development:**
```bash
php -S localhost:8000 -t public/
# Then go to: http://localhost:8000
```

**For Testing:**
```bash
php verify-application.php
php verify-routing.php
php verify-database.php
```

---

## Next Steps

After running the application:

1. **Explore Dashboard** - Understand the UI
2. **Test Features** - Create users, programs, students
3. **Review Reports** - Check reporting capabilities
4. **Try CLI Tools** - Run maintenance commands
5. **Check Logs** - Review application logs in `storage/logs/`

All files are well-documented. Check the project documentation:
- [PROJECT_REPORT.md](PROJECT_REPORT.md) - Full project overview
- [TEST_AND_VERIFICATION_REPORT.md](TEST_AND_VERIFICATION_REPORT.md) - Testing results
- [TESTING.md](TESTING.md) - Testing guide
