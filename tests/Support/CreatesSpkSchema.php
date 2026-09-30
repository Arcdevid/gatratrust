<?php

namespace Tests\Support;

trait CreatesSpkSchema
{
    protected function createSpkSchema(bool $includeProjectMigration = true): void
    {
        $migrations = [
            '0001_01_01_000000_create_users_table.php',
            '2025_05_13_000936_create_kerjaans_table.php',
            '2025_05_13_002024_create_projects_table.php',
            '2025_11_13_064612_create_project_user_table.php',
            '2026_02_23_160000_create_spks_table.php',
        ];

        if ($includeProjectMigration) {
            $migrations[] = '2026_02_25_000001_create_spks_table.php';
        }

        foreach ($migrations as $file) {
            (require database_path('migrations/'.$file))->up();
        }
    }
}
