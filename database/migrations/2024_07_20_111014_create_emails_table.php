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
        Schema::create('emails', function (Blueprint $table) {
            $table->id();
            $table->string('subject_fr', 255)->nullable();
            $table->string('subject_en', 255)->nullable();
            $table->string('language', 255)->default('fr');
            $table->json('data')->nullable();
            $table->unsignedBigInteger('sender_id');
            $table->unsignedBigInteger('workshop_id')->nullable();
            $table->boolean('admin')->default(0);
            $table->boolean('sent')->default(0);
            $table->timestamp('schedule')->nullable();
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('workshop_id')->references('id')->on('workshops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('emails');
    }
};
