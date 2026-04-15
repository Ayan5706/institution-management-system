# Storage Directory

This directory contains all runtime data and temporary files for the IMS application.

## Subdirectories

### `/logs/`
Application log files organized by date.

- **Files**: `YYYY-MM-DD.log`
- **Size Limit**: 10MB per file (auto-rotates)
- **Retention**: 30 days (configurable)
- **Access**: Internal only

**Log Levels:**
- `ERROR` - Application errors
- `WARNING` - Warning messages
- `INFO` - General information
- `DEBUG` - Debug messages (development)

**Usage:**
```php
use App\Core\Logger;

$logger = new Logger();
$logger->info('User logged in', ['user_id' => 1]);
$logger->error('Database connection failed', ['errno' => 1045]);
$logger->exception($throwable);
```

### `/cache/`
Application cache files with automatic expiration.

- **Files**: Serialized PHP objects (MD5 hash names)
- **TTL**: 1 hour default (configurable per item)
- **Auto-cleanup**: Expired entries cleaned on access
- **Size**: Not limited

**Cache Manager:**
```php
use App\Core\Cache;

$cache = new Cache();
$cache->put('user.1.profile', $userData, 3600); // 1 hour
$user = $cache->get('user.1.profile');
$cache->remember('expensive_query', 3600, function() {
    return Database::query('...');
});
```

### `/sessions/`
PHP session storage files.

- **Files**: Session data (serialized)
- **Lifetime**: 24 hours default
- **Cleanup**: PHP garbage collection
- **Security**: HTTPOnly, Secure flags enabled

**Session Features:**
```php
use App\Core\Session;

Session::init();
Session::put('user_id', 1);
Session::flash('success', 'User created!');
Session::generateCsrfToken();
```

### `/temp/`
Temporary uploaded files, processing buffers, and working files.

- **Files**: Upload staging area
- **Cleanup**: Auto-cleanup after 24 hours
- **Size Limit**: No hard limit (configurable)
- **Access**: Internal staging only

**Temp File Handler:**
```bash
php scripts/cache-cleanup.php --all        # Clean all temp files
php scripts/upload-maintenance.php verify  # Check structure
```

### `/backups/`
Database and file backups (created by backup script).

- **Database Backups**: `db_ims_final_YYYY-MM-DD_HH-MM-SS.sql`
- **File Backups**: `files_YYYY-MM-DD_HH-MM-SS.zip`
- **Retention**: 30 days default
- **Size**: As needed

**Backup Script:**
```bash
php scripts/backup.php              # Full backup
php scripts/backup.php --list       # List backups
php scripts/backup.php --clean      # Remove old backups
```

### `/exports/`
Exported data files (CSV, reports).

- **Files**: `tablename_YYYY-MM-DD_HH-MM-SS.csv`
- **Format**: CSV (Excel-compatible)
- **Retention**: No auto-cleanup
- **Access**: Download via API

**Data Export:**
```bash
php scripts/data-export.php users        # Export single table
php scripts/data-export.php --all        # Export all tables
php scripts/data-export.php --list       # List tables
```

## File Permissions

```
storage/               755 (rwxr-xr-x)
├── logs/             755
├── cache/            755
├── sessions/         755
├── temp/             755
├── backups/          755
└── exports/          755
```

**Fix Permissions:**
```bash
php scripts/permissions.php --fix
```

## Cleanup Tasks

### Automated Cleanup Scripts

**Cache Cleanup:**
```bash
# Clear cache and rotate logs
php scripts/cache-cleanup.php --all
```

**Old Log Cleanup:** 
```bash
# Remove logs older than 30 days
php scripts/cache-cleanup.php --logs
```

**Backup Cleanup:**
```bash
# Remove backups older than 30 days
php scripts/backup.php --clean
```

**Upload Cleanup:**
```bash
# Clean temporary upload files
php scripts/upload-maintenance.php clean-temp
```

### Cron Job Suggestions

**Daily at 2 AM - Full Backup:**
```bash
0 2 * * * /usr/bin/php /var/www/ims/scripts/backup.php >/dev/null 2>&1
```

**Daily at 4 AM - Cleanup Old Files:**
```bash
0 4 * * * /usr/bin/php /var/www/ims/scripts/cache-cleanup.php --all >/dev/null 2>&1
```

**Weekly on Sunday - Database Optimization:**
```bash
0 3 * * 0 /usr/bin/php /var/www/ims/scripts/optimize-database.php >/dev/null 2>&1
```

## Monitoring & Reporting

### Disk Usage
```bash
# Check disk usage in uploads
php scripts/upload-maintenance.php disk-usage

# Display breakdown by directory
php scripts/backup.php --list
```

### System Health
```bash
# Full system health check
php scripts/health-check.php
```

### Cache Statistics
```php
use App\Core\Cache;

$cache = new Cache();
$stats = $cache->stats();
echo "Total Cache Size: " . $stats['size_mb'] . " MB";
echo "Expired Entries: " . $stats['expired'];
```

### Log Analysis
```php
use App\Core\Logger;

$logger = new Logger();
$recent = $logger->getRecent(100); // Last 100 lines
echo $recent;
```

## Best Practices

1. **Regular Backups**: Run daily backups to prevent data loss
2. **Cache Clearing**: Clear cache after major updates
3. **Log Rotation**: Old logs are auto-rotated at 10MB
4. **Temp Files**: Clean temp files regularly to save space
5. **Permissions**: Verify permissions monthly
6. **Monitoring**: Check health weekly

## Troubleshooting

**"Permission denied" errors:**
```bash
php scripts/permissions.php --fix
```

**"Disk space low" warning:**
```bash
# Clean old files first
php scripts/cache-cleanup.php --all
php scripts/backup.php --clean

# Check usage
php scripts/upload-maintenance.php disk-usage
```

**"Cache not working" issues:**
```bash
# Clear and verify cache
php scripts/cache-cleanup.php --cache
php scripts/health-check.php
```

**"Session data lost":**
```bash
# Check session directory permissions
ls -la storage/sessions/
php scripts/permissions.php --fix
```

## File Structure Reference

```
storage/
├── logs/
│   ├── 2026-04-12.log          (Today's log)
│   ├── 2026-04-11.log          (Yesterday)
│   └── 2026-04-11_14-30-45.log (Rotated when >10MB)
├── cache/
│   ├── a1b2c3d4e5f6...cache
│   └── f6e5d4c3b2a1...cache
├── sessions/
│   ├── sess_abc123xyz789def456
│   └── sess_def456ghi789jkl012
├── temp/
│   ├── upload_1_tmp.jpg
│   └── processing_batch_1.tmp
├── backups/
│   ├── db_ims_final_2026-04-12_02-00-00.sql
│   └── files_2026-04-12_02-00-00.zip
├── exports/
│   ├── users_2026-04-12_10-30-45.csv
│   └── reports_2026-04-12_15-20-10.csv
└── .gitignore
```

## Security Notes

- Storage paths are **NOT** accessible via web server
- Session files are server-side only
- Backup files should be encrypted before remote storage
- Cache contains no sensitive data by default
- Logs contain application data (sanitize before sharing)
- Regular backups ensure data recovery

---

**Last Updated:** April 12, 2026  
**Version:** 1.0
