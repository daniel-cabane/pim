<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE games MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'conflicted', 'cancelled') DEFAULT 'pending'");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE games MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'conflicted') DEFAULT 'pending'");
    }
};
