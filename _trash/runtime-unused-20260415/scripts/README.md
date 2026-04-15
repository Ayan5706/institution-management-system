# IMS CLI Scripts Documentation

Comprehensive command-line tools for managing the Institution Management System.

## Quick Start

### Windows
```bash
# Run CLI launcher
php scripts/cli.php

# First-time setup
php scripts/cli.php install

# Run migrations
php scripts/cli.php migrate

# Seed database
php scripts/cli.php seed --force
```

### Linux/macOS
```bash
# Same commands work on all platforms
php scripts/cli.php install
php scripts/cli.php migrate
```

---

## Available Scripts

### 1. **cli.php** - Main CLI Launcher
Central entry point for all CLI commands.

```bash
php scripts/cli.php <command> [options]
php scripts/cli.php list          # Show all commands
php scripts/cli.php health        # Quick health check
```

**Commands:**
- `install` - First-time setup wizard
- `migrate` - Run database migrations
- `seed` - Populate database with seed data
- `user` - Manage users
- `backup` - Backup database and files
- `permissions` - Fix file permissions
- `cache` - Clear cache and logs
- `reset` - Reset database (dev only)
- `health` - System health check
- `export` - Export data to CSV
- `optimize` - Optimize database
- `uploads` - Manage uploads

---

### 2. **install.php** - Installation Wizard
First-time setup wizard for IMS.

```bash
php scripts/install.php
```

**What it does:**
- ✓ Checks PHP version and extensions
- ✓ Creates necessary directories
- ✓ Sets proper file permissions
- ✓ Prompts for database configuration
- ✓ Tests database connection
- ✓ Creates database if needed

**Output:**
- Creates all required directories
- Sets correct permissions (755)
- Verifies all requirements met
- Guides to next steps

---

### 3. **migrate.php** - Database Migrations
Applies pending database migrations.

```bash
php scripts/migrate.php
```

**Features:**
- Tracks applied migrations
- Batch processing
- Skips already-applied migrations
- Creates migrations table automatically

**Output:**
- Lists all applied migrations
- Shows execution time
- Reports success/failure

---

### 4. **seed.php** - Database Seeding
Populates database with initial/sample data.

```bash
php scripts/seed.php
php scripts/seed.php --force      # Skip confirmation
```

**Seeders:**
- UsersTableSeeder (creates admin, teacher, student accounts)
- ProgramsTableSeeder (engineering, business, arts, etc.)
- SemestersTableSeeder (Spring, Summer, Fall, Winter)
- SubjectsTableSeeder (Math, Science, Literature, etc.)
- TeacherAssignmentsTableSeeder (assigns teachers to subjects)
- StudentProfilesTableSeeder (enrolls students)

**Demo Credentials:**
- Login ID: `admin`
- Password: `password123`

---

### 5. **user-manage.php** - User Management
Create, list, and manage users.

```bash
php scripts/user-manage.php create              # Create new user
php scripts/user-manage.php list                # List all users
php scripts/user-manage.php reset-password 1   # Reset password
php scripts/user-manage.php deactivate 2       # Deactivate user
```

**Interactive prompts:**
- Full Name
- Login ID
- Email
- Phone
- Role (admin/teacher/student)
- Password (hidden input)

**Output:**
- User table listing
- Success confirmations
- User ID assignment

---

### 6. **backup.php** - Database & Files Backup
Create backups of database and uploaded files.

```bash
php scripts/backup.php              # Full backup (DB + files)
php scripts/backup.php --db-only    # Database only
php scripts/backup.php --files-only # Files only
php scripts/backup.php --list       # List existing backups
php scripts/backup.php --clean      # Remove backups >30 days old
```

**Backup Formats:**
- Database: `db_ims_final_YYYY-MM-DD_HH-MM-SS.sql`
- Files: `files_YYYY-MM-DD_HH-MM-SS.zip`

**Storage:**
- Location: `storage/backups/`
- Auto cleanup: Deletes backups older than 30 days

**Features:**
- Creates SQL dump with INSERT statements
- ZIP archive of upload directories
- Timestamp tracking
- Automatic cleanup policy

---

### 7. **permissions.php** - File Permissions
Fix directory permissions.

```bash
php scripts/permissions.php         # Check current permissions
php scripts/permissions.php --fix   # Fix all permissions
php scripts/permissions.php --check # Detailed permission check
```

**Affected Directories:**
- `storage/` (logs, cache, backups)
- `public/uploads/` (all subdirectories)
- `bootstrap/`

**Permissions Applied:**
- Directories: `0755` (rwxr-xr-x)
- Writable by web server but protected from public execution

---

### 8. **cache-cleanup.php** - Cache & Logs Cleanup
Clear application cache and old log files.

```bash
php scripts/cache-cleanup.php       # Show help
php scripts/cache-cleanup.php --cache   # Clear cache only
php scripts/cache-cleanup.php --logs    # Clean logs >30 days
php scripts/cache-cleanup.php --all     # Clear cache + clean logs
```

**Cache Directories:**
- `storage/cache/`

**Logs Cleanup:**
- Removes `.log` files older than 30 days
- Keeps recent logs for debugging

---

### 9. **reset-database.php** - Database Reset
⚠️ **WARNING: Development only! Deletes all data!**

```bash
php scripts/reset-database.php
```

**Confirmation:**
- Requires typing 'yes' to confirm
- Shows database name before deletion
- Creates fresh database

**Use Cases:**
- Development/testing
- Resetting sample data
- Starting fresh

**After Reset:**
1. Run `php scripts/migrate.php`
2. Run `php scripts/seed.php --force`

---

### 10. **health-check.php** - System Health Check
Diagnose system configuration and health.

```bash
php scripts/health-check.php
```

**Checks:**
- ✓ PHP version (≥7.4)
- ✓ Required extensions (PDO MySQL, GD, JSON, mbstring)
- ✓ Directory structure
- ✓ File permissions (rwx checks)
- ✓ Database connection
- ✓ Disk space usage

**Output:**
- Formatted report with status indicators
- Issues highlighted with ✗
- Disk usage percentage
- Total database tables and migrations applied

---

### 11. **data-export.php** - Data Export
Export database tables to CSV format.

```bash
php scripts/data-export.php --list      # List tables and row counts
php scripts/data-export.php users       # Export specific table
php scripts/data-export.php --all       # Export all tables
```

**Export Format:**
- CSV files in `storage/exports/`
- Naming: `tablename_YYYY-MM-DD_HH-MM-SS.csv`
- Headers row included

**Use Cases:**
- Data analysis
- Reporting
- Data migration
- Backups

---

### 12. **optimize-database.php** - Database Optimization
Optimize and maintain database tables.

```bash
php scripts/optimize-database.php
```

**Operations:**
- ✓ OPTIMIZE TABLE on all tables
- ✓ CHECK TABLE integrity
- ✓ ANALYZE TABLE statistics
- ✓ Display table sizes and row counts

**Output:**
- Optimization status per table
- Integrity check results
- Size statistics (MB per table)
- Total database size

**Performance Benefits:**
- Recovers space from deleted rows
- Updates index statistics
- Improves query performance

---

### 13. **upload-maintenance.php** - Upload Management
Manage uploaded files cleanup and verification.

```bash
php scripts/upload-maintenance.php clean-temp     # Remove temp files
php scripts/upload-maintenance.php verify         # Check structure
php scripts/upload-maintenance.php disk-usage     # Show disk usage
php scripts/upload-maintenance.php report         # Full report
```

**Features:**
- Clean temporary uploads
- Verify directory structure
- Check disk usage by category
- Generate maintenance reports

---

## Usage Patterns

### Development Workflow
```bash
# 1. First-time setup
php scripts/cli.php install

# 2. Run migrations
php scripts/cli.php migrate

# 3. Seed data
php scripts/cli.php seed --force

# 4. Check health
php scripts/cli.php health

# 5. Start developing!
```

### Testing Workflow
```bash
# Reset for clean test environment
php scripts/cli.php reset

# 4. Seed fresh data
php scripts/cli.php seed --force

# Run tests
```

### Maintenance Workflow
```bash
# Regular maintenance
php scripts/cli.php health         # Check system
php scripts/cli.php optimize       # Optimize DB
php scripts/cli.php cache --all    # Clean cache/logs
php scripts/cli.php permissions    # Verify permissions

# Backup before updates
php scripts/cli.php backup         # Full backup
```

### Deployment Workflow
```bash
# 1. Pre-deployment check
php scripts/cli.php health

# 2. Migrate new schema
php scripts/cli.php migrate

# 3. Fix permissions
php scripts/cli.php permissions --fix

# 4. Post-deployment optimization
php scripts/cli.php optimize
```

---

## Environment Variables

Useful environment variables for automation:

```bash
# Database config (if different from defaults)
export DB_HOST=192.168.1.100
export DB_USER=app_user
export DB_PASS=secure_password

# Then run scripts normally
php scripts/cli.php migrate
```

---

## Error Handling

### Common Issues

**"No migration files found"**
- Ensure `database/migrations/` directory exists
- Migration files must be in `database/migrations/*.sql`

**"Permission denied" errors**
- Run: `php scripts/cli.php permissions --fix`
- Or manually: `chmod 755 storage/ public/uploads/`

**"Connection refused" errors**
- Check MySQL is running
- Verify database credentials in `app/Config/database.php`
- Test with: `php scripts/cli.php health`

**"Table already exists"**
- Run: `php scripts/cli.php reset` (dev only)
- Or manually drop tables and re-run migrations

---

## Automation / Cron Jobs

### Linux Cron
```bash
# Daily backup at 2 AM
0 2 * * * /usr/bin/php /var/www/ims/scripts/backup.php >/dev/null 2>&1

# Weekly optimization on Sundays at 3 AM
0 3 * * 0 /usr/bin/php /var/www/ims/scripts/optimize-database.php >/dev/null 2>&1

# Daily cache cleanup at 4 AM
0 4 * * * /usr/bin/php /var/www/ims/scripts/cache-cleanup.php --all >/dev/null 2>&1

# Old backup cleanup (runs with backup)
0 2 * * * /usr/bin/php /var/www/ims/scripts/backup.php --clean >/dev/null 2>&1
```

### Windows Task Scheduler
```powershell
# Daily backup
schtasks /create /tn "IMS Backup" /tr "php C:\xampp\htdocs\IMS_FINAL\scripts\backup.php" /sc daily /st 02:00

# Weekly optimization
schtasks /create /tn "IMS Optimize" /tr "php C:\xampp\htdocs\IMS_FINAL\scripts\optimize-database.php" /sc weekly /d SUN /st 03:00
```

---

## Testing Commands

Quick test suite to verify everything works:

```bash
#!/bin/bash
echo "Running IMS Script Tests..."

php scripts/health-check.php
php scripts/backup.php --list
php scripts/permissions.php --check
php scripts/cache-cleanup.php --all
php scripts/optimize-database.php

echo "All tests completed!"
```

---

## Directory Structure

```
scripts/
├── cli.php                    # Main CLI launcher
├── install.php                # Installation wizard
├── migrate.php                # Database migrations
├── seed.php                   # Database seeding
├── user-manage.php            # User management
├── backup.php                 # Backup utilities
├── permissions.php            # Permission manager
├── cache-cleanup.php          # Cache/logs cleanup
├── reset-database.php         # Database reset (dev)
├── health-check.php           # Health check
├── data-export.php            # Data export/reporting
├── optimize-database.php      # DB optimization
├── upload-maintenance.php     # Upload management
└── README.md                  # This file
```

---

## Support & Documentation

For more information:
- Database: See `database/README.md`
- Models: See `app/Models/`
- Configuration: See `app/Config/`

---

**Last Updated:** April 12, 2026
**Version:** 1.0
