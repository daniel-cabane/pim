<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tournament_id');
            $table->unsignedBigInteger('round_id');
            $table->unsignedBigInteger('player1_id');
            $table->unsignedBigInteger('player2_id')->nullable();
            $table->enum('result', ['player1_win', 'player2_win', 'draw'])->nullable();
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            
            $table->foreign('tournament_id')->references('id')->on('tournaments')->cascadeOnDelete();
            $table->foreign('round_id')->references('id')->on('rounds')->cascadeOnDelete();
            $table->foreign('player1_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('player2_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
