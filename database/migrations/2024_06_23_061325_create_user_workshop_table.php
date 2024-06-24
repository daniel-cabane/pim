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
        Schema::create('user_workshop', function (Blueprint $table) {
            $table->primary(['user_id', 'workshop_id']);
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('workshop_id');
            $table->boolean('available')->default(0);
            $table->boolean('confirmed')->default(0);
            $table->text('comment')->nullable();
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('workshop_id')->references('id')->on('workshops')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_workshop');
    }
};
