# IMS Migrations

This folder contains ordered SQL migrations for the IMS database.

## Files
- `2026_04_11_000000_create_migrations_table.sql`
- `2026_04_11_000001_create_ims_core_schema.sql`

## Run migrations
From project root:

```bash
C:\xampp\php\php.exe scripts\migrate.php
```

The runner tracks applied files in the `migrations` table and skips already-applied migrations.
