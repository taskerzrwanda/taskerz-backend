<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'taskerzrwanda@gmail.com'],
            [
                'name' => 'Admin',
                'password' => Hash::make('mb1234567'),
                'role' => 'admin',
            ]
        );

        // User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);

        $this->call([
            TaskersSeeder::class,
            TasksAndSubTasksSeeder::class,
            FaqsTableSeeder::class,
            TestimonialsTableSeeder::class,
        ]);
    }
}

