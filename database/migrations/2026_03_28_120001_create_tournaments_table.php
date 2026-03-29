<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tournaments', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->enum('format', ['swiss', 'round_robin', 'knockout'])->default('swiss');
            $table->enum('status', ['draft', 'preparation', 'ongoing', 'completed'])->default('draft');
            $table->integer('rounds_count')->default(0);
            $table->integer('players_count')->default(0);
            $table->timestamp('started_at')->nullable();
            $table->timestamp('ended_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamps();
            
            $table->foreign('created_by')->references('id')->on('users')->nullOnDelete();
            $table->index('slug');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tournaments');
    }
};
