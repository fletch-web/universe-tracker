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
            $table->foreignId('c3_superstar_id')->nullable()->after('c2_superstar_id')->constrained('superstars')->nullOnDelete();
            $table->foreignId('c4_superstar_id')->nullable()->after('c3_superstar_id')->constrained('superstars')->nullOnDelete();
            $table->foreignId('c3_team_id')->nullable()->after('c2_team_id')->constrained('teams')->nullOnDelete();
            $table->foreignId('c4_team_id')->nullable()->after('c3_team_id')->constrained('teams')->nullOnDelete();
            $table->foreignId('championship_id')->nullable()->after('storyline_id')->constrained('championships')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('match_logs', function (Blueprint $table) {
            $table->dropColumn(['c3_superstar_id', 'c4_superstar_id', 'c3_team_id', 'c4_team_id', 'championship_id']);
        });
    }
};
