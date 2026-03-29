<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournament_player', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tournament_id');
            $table->unsignedBigInteger('user_id');
            $table->decimal('score', 8, 1)->default(0);
            $table->integer('wins')->default(0);
            $table->integer('draws')->default(0);
            $table->integer('losses')->default(0);
            $table->smallInteger('rating')->default(800);
            $table->boolean('banned')->default(false);
            $table->timestamps();
            
            $table->foreign('tournament_id')->references('id')->on('tournaments')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->unique(['tournament_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournament_player');
    }
};
