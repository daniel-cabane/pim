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
        Schema::create('objective_user', function (Blueprint $table) {
            $table->primary(['user_id', 'objective_id']);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('objective_id');
            $table->unsignedTinyInteger('score');
            $table->timestamps();

            $table->foreign('objective_id')->references('id')->on('objectives')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('objective_user');
    }
};
