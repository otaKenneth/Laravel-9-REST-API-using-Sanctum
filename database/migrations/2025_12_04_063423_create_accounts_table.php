<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            // Foreign key to users table
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            
            $table->string('account_number')->unique();
            
            // Decimal is safer for money than float/double
            // 15 digits total, 2 after decimal (e.g., 1234567890123.99)
            $table->decimal('balance', 15, 2)->default(0.00);
            
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('accounts');
    }
};