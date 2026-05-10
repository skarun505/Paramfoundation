<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create default admin user
        User::firstOrCreate(
            ['email' => 'admin@parsec.in'],
            [
                'name'      => 'ParSEC Admin',
                'password'  => Hash::make('admin@1234'),
                'is_admin'  => true,
            ]
        );

        $this->command->info('✅ Admin user: admin@parsec.in / admin@1234');

        // Seed sample slots
        $this->call(SlotSeeder::class);
    }
}
