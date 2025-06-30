<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->id();
            $table->string('title_fr', 255)->nullable();
            $table->string('title_en', 255)->nullable();
            $table->json('description')->nullable();
            $table->json('rewards')->nullable();
            $table->string('join_code', 255)->unique();
            $table->json('sections')->nullable();
            $table->unsignedBigInteger('instructor_id');
            $table->timestamps();

            $table->foreign('instructor_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('courses');
    }
};
