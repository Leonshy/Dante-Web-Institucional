<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            PermissionSeeder::class,
            AdminUserSeeder::class,
            SiteSettingSeeder::class,
            PageTreeSeeder::class,
            MenuSeeder::class,
            LocationSeeder::class,
        ]);
    }
}
