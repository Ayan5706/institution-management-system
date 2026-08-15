<?php

namespace App\Seeders;

use PDO;

class DatabaseSeeder
{
    protected PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Run the database seeders.
     *
     * @return void
     */
    public function run(): void
    {
        $seeders = [
            UsersTableSeeder::class,
            ProgramsTableSeeder::class,
            SemestersTableSeeder::class,
            SubjectsTableSeeder::class,
            TeacherAssignmentsTableSeeder::class,
            StudentProfilesTableSeeder::class,
            TimetablesTableSeeder::class,
        ];

        foreach ($seeders as $seederClass) {
            $this->call($seederClass);
        }
    }

    /**
     * Call a seeder and run it.
     *
     * @param string $seederClass The seeder class name
     * @return void
     */
    protected function call(string $seederClass): void
    {
        try {
            $seeder = new $seederClass($this->pdo);
            $seeder->run();
        } catch (\Exception $e) {
            echo "Error running seeder {$seederClass}: {$e->getMessage()}\n";
        }
    }
}
