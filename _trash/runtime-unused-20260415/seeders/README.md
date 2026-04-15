# Database Seeders

Database seeders are used to populate your database with initial and sample data for development and testing. This is useful for:

- Setting up initial admin users and roles
- Creating sample academic programs and semesters
- Populating test data for development
- Resetting database to a clean state

## Structure

```
database/seeders/
├── Seeder.php                    # Base seeder class with common methods
├── DatabaseSeeder.php            # Main seeder that orchestrates all seeders
├── UsersTableSeeder.php         # Seeds users (admin, teachers, students)
├── ProgramsTableSeeder.php      # Seeds academic programs
├── SemestersTableSeeder.php     # Seeds semesters for programs
├── SubjectsTableSeeder.php      # Seeds academic subjects/courses
├── TeacherAssignmentsTableSeeder.php  # Seeds teacher-subject-program mappings
├── StudentProfilesTableSeeder.php     # Seeds student enrollment records
└── README.md                     # This file
```

## Running Seeders

### Run all seeders interactively:

```bash
php scripts/seed.php
```

This will prompt you to confirm before seeding. You'll be asked:
```
This will seed your database with initial data.
Are you sure you want to continue? (yes/no):
```

### Run all seeders without confirmation:

```bash
php scripts/seed.php --force
```

### Display usage information:

```bash
php scripts/seed.php --help
```

## Available Seeders

### UsersTableSeeder
Creates initial users across all roles:
- 1 Admin user: `admin` / `password123`
- 1 Principal: `principal.wilson`
- 3 Teachers: `dr.johnson`, `mr.smith`, `ms.davis`
- 5 Students: `janderson`, `sbrown`, `mharris`, `ltaylor`, `dmiller`

All users have password: `password123` (bcrypt hashed)

### ProgramsTableSeeder
Creates 5 academic programs:
- Bachelor of Science in Computer Science (BSCS) - 4 years
- Bachelor of Arts in English Literature (BAEL) - 4 years
- Bachelor of Science in Biology (BSBI) - 4 years
- Bachelor of Science in Mathematics (BSMA) - 4 years
- Associate Degree in Business Administration (ASBA) - 2 years

### SemestersTableSeeder
Creates semesters for programs:
- Year 1 & 2, Semesters 1 & 2 for Computer Science
- Year 1, Semesters 1 & 2 for other programs
- Start and end dates configured per semester

### SubjectsTableSeeder
Creates 11 subjects across various disciplines:
- **CS**: Introduction to Programming, Data Structures, Web Development, Database Systems
- **EN**: Composition, British Literature, American Literature
- **BI**: Cell Biology, Genetics
- **MA**: Calculus I, Linear Algebra

### TeacherAssignmentsTableSeeder
Assigns teachers to subjects and programs:
- Dr. Johnson: CS101 (BSCS), MA102 (BSMA)
- Mr. Smith: CS102 (BSCS), CS201 (BSCS)
- Ms. Davis: EN101 (BAEL), EN102 (BAEL)

### StudentProfilesTableSeeder
Creates student enrollment records:
- 5 students enrolled in various programs
- GPAs ranging from 3.45 to 3.92
- All enrolled for 2026 academic year

## Creating Custom Seeders

To create a custom seeder, extend the `Seeder` base class:

```php
<?php

namespace App\Seeders;

class CustomTableSeeder extends Seeder
{
    public function run(): void
    {
        echo "Seeding custom_table...\n";

        $data = [
            [
                'field1' => 'value1',
                'field2' => 'value2',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ],
            // More rows...
        ];

        $this->insertMany('custom_table', $data);
        echo "Seeded " . count($data) . " records.\n";
    }
}
```

## Base Seeder Methods

The `Seeder` base class provides these helper methods:

### `insert(string $table, array $data): void`
Insert a single row into a table.

```php
$this->insert('users', [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'created_at' => date('Y-m-d H:i:s'),
]);
```

### `insertMany(string $table, array $rows): void`
Insert multiple rows into a table.

```php
$this->insertMany('users', [
    ['name' => 'John', ...],
    ['name' => 'Jane', ...],
]);
```

### `truncate(string $table): void`
Clear all data from a table.

```php
$this->truncate('users');
```

### `execute(string $sql, array $params = []): void`
Execute raw SQL statements.

```php
$this->execute('UPDATE users SET active = ?', [1]);
```

### `count(string $table): int`
Get the count of rows in a table.

```php
$count = $this->count('users');
echo "Total users: {$count}";
```

## Registering Seeders

To register a new seeder, add it to the `DatabaseSeeder.php` run method:

```php
public function run(): void
{
    $seeders = [
        UsersTableSeeder::class,
        ProgramsTableSeeder::class,
        // ... other seeders
        CustomTableSeeder::class,  // Add your new seeder here
    ];

    foreach ($seeders as $seederClass) {
        $this->call($seederClass);
    }
}
```

## Notes

- Seeders are ideal for development and testing environments
- In production, consider using schema migrations instead
- All seeded data is inserted with `created_at` and `updated_at` timestamps
- Passwords in seeders are examples; change them for production use
- Run seeders after migrations for consistency

## Troubleshooting

**"No such file or directory" error**
- Ensure you're running from the project root: `php scripts/seed.php`
- Check that the `bootstrap/app.php` file exists

**"SQLSTATE[HY000]" errors**
- Check database connection settings in `app/Config/database.php`
- Ensure the database exists and migrations have been run
- Verify user permissions on the database

**Duplicate Key errors**
- Truncate the table before re-seeding: `$this->truncate('table_name')`
- Or drop and recreate the table using migrations
