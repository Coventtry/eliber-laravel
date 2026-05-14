<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            InstitucionesSeeder::class,
            AreasSeeder::class,
            RolesAndPermissionsSeeder::class,
            DefaultAdminSeeder::class,
        ]);

        if (filter_var(env('SEED_SAMPLE_DATA', false), FILTER_VALIDATE_BOOL)) {
            $this->call([
                SampleDataSeeder::class,
            ]);
        }
    }
}
