<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn('result');
        });

        Schema::table('games', function (Blueprint $table) {
            $table->enum('result1', ['win', 'draw', 'loss'])->nullable()->after('player2_id');
            $table->enum('result2', ['win', 'draw', 'loss'])->nullable()->after('result1');
        });

        // Update status enum to include 'conflicted'
        DB::statement("ALTER TABLE games MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed', 'conflicted') DEFAULT 'pending'");
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['result1', 'result2']);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->enum('result', ['player1_win', 'player2_win', 'draw'])->nullable()->after('player2_id');
        });

        DB::statement("ALTER TABLE games MODIFY COLUMN status ENUM('pending', 'in_progress', 'completed') DEFAULT 'pending'");
    }
};
