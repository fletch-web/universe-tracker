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
        Schema::table('match_logs', function (Blueprint $table) {
            $table->json('team1_superstar_ids')->nullable()->after('stipulation');
            $table->json('team2_superstar_ids')->nullable()->after('team1_superstar_ids');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('match_logs', function (Blueprint $table) {
            $table->dropColumn(['team1_superstar_ids', 'team2_superstar_ids']);
        });
    }
};
