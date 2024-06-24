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
        Schema::create('workshops', function (Blueprint $table) {
            $table->id();
            $table->string('title_fr', 255)->nullable();
            $table->string('title_en', 255)->nullable();
            $table->json('description')->nullable();
            $table->string('language', 255)->default('fr');
            $table->json('details');
            $table->unsignedTinyInteger('term');
            $table->unsignedBigInteger('organiser_id');
            $table->date('start_date')->nullable();
            $table->string('status', 255)->default('draft');
            $table->boolean('accepting_students')->default(0);
            $table->boolean('archived')->default(0);
            $table->timestamps();

            $table->foreign('organiser_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('workshops');
    }
};
