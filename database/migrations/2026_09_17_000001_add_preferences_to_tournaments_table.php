<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->json('preferences')->nullable()->after('created_by');
        });

        DB::table('tournaments')->update([
            'preferences' => json_encode([
                'time' => [
                    'title' => '5min',
                    'subtitle' => 'Blitz',
                ],
                'nbRounds' => 6,
            ]),
        ]);
    }

    public function down(): void
    {
        Schema::table('tournaments', function (Blueprint $table) {
            $table->dropColumn('preferences');
        });
    }
};
