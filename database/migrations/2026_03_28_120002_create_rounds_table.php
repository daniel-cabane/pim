<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rounds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tournament_id');
            $table->integer('round_number');
            $table->enum('status', ['pending', 'in_progress', 'completed'])->default('pending');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->timestamps();
            
            $table->foreign('tournament_id')->references('id')->on('tournaments')->cascadeOnDelete();
            $table->unique(['tournament_id', 'round_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rounds');
    }
};
