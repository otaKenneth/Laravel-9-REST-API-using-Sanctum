<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UsersSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::create([
            'role' => "admin",
            'name' => "Test User",
            'email' => "test@gmail.com",
            'password' => Hash::make('password')
        ]);

        $user = User::create([
            'role' => "admin",
            'name' => "Sabon Express",
            'email' => "admin@sabonexpress.ph",
            'password' => Hash::make('password')
        ]);
    }
}
