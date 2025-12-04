<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AccountsSeeder extends Seeder
{
    public function run(): void
    {
        // Get our customers
        $john = User::where('email', 'john@gmail.com')->first();
        $sarah = User::where('email', 'sarah@gmail.com')->first();

        if ($john) {
            // John's Savings Account
            DB::table('accounts')->insert([
                'user_id' => $john->id,
                'account_number' => '1001001001',
                'balance' => 5000.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // John's Checking Account
            DB::table('accounts')->insert([
                'user_id' => $john->id,
                'account_number' => '1001001002',
                'balance' => 150.00,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        if ($sarah) {
            // Sarah's Savings Account
            DB::table('accounts')->insert([
                'user_id' => $sarah->id,
                'account_number' => '2002002001',
                'balance' => 12500.50,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}