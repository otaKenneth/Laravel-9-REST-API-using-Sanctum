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
            'roles_id' => 1,
            'name' => "Ian Kenneth Mendoza",
            'email' => "admin01@sabonexpress.ph",
            'password' => Hash::make('password')
        ]);

        $user = User::create([
            'roles_id' => 1,
            'name' => "JC Basilio",
            'email' => "admin02@sabonexpress.ph",
            'password' => Hash::make('password')
        ]);

        $user->userDetail()->create([
            'firstname' => "JC",
            'lastname' => "Basilio"
        ]);
    }
}
