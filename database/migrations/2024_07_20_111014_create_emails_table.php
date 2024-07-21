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
            $table->string('title_fr', 255)->nullable();
            $table->string('title_en', 255)->nullable();
            $table->json('data')->nullable();
            $table->json('recipients')->nullable();
            $table->string('language', 255)->default('fr');
            $table->unsignedBigInteger('sender_id');
            $table->boolean('sent')->default(0);
            $table->timestamp('schedule')->nullable();
            $table->timestamps();

            $table->foreign('sender_id')->references('id')->on('users')->onDelete('cascade');
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
