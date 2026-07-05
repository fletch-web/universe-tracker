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
        Schema::create('shows', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('color');
            $table->longText('image')->nullable();
            $table->timestamps();
        });

        Schema::create('superstars', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('gender');
            $table->foreignId('show_id')->nullable()->constrained('shows')->nullOnDelete();
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('losses')->default(0);
            $table->unsignedInteger('draws')->default(0);
            $table->longText('image')->nullable();
            $table->timestamps();
        });

        Schema::create('teams', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->unsignedInteger('wins')->default(0);
            $table->unsignedInteger('losses')->default(0);
            $table->unsignedInteger('draws')->default(0);
            $table->timestamps();
        });

        Schema::create('superstar_team', function (Blueprint $table) {
            $table->foreignId('superstar_id')->constrained('superstars')->cascadeOnDelete();
            $table->foreignId('team_id')->constrained('teams')->cascadeOnDelete();
            $table->primary(['superstar_id', 'team_id']);
        });

        Schema::create('championships', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('show_id')->nullable()->constrained('shows')->nullOnDelete();
            $table->string('type'); // Singles, TagTeam
            $table->foreignId('champion_superstar_id')->nullable()->constrained('superstars')->nullOnDelete();
            $table->foreignId('champion_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('storylines', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->timestamps();
        });

        Schema::create('storyline_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('storyline_id')->constrained('storylines')->cascadeOnDelete();
            $table->date('date');
            $table->string('show_name');
            $table->text('description');
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        Schema::create('show_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('show_id')->nullable()->constrained('shows')->nullOnDelete();
            $table->date('date');
            $table->timestamps();
        });

        Schema::create('match_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('show_log_id')->constrained('show_logs')->cascadeOnDelete();
            $table->string('division'); // Singles, TagTeam
            $table->foreignId('c1_superstar_id')->nullable()->constrained('superstars')->nullOnDelete();
            $table->foreignId('c2_superstar_id')->nullable()->constrained('superstars')->nullOnDelete();
            $table->foreignId('c1_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('c2_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->string('outcome'); // Decisive, Draw
            $table->string('winner_slot')->nullable(); // 1, 2
            $table->foreignId('winner_superstar_id')->nullable()->constrained('superstars')->nullOnDelete();
            $table->foreignId('winner_team_id')->nullable()->constrained('teams')->nullOnDelete();
            $table->foreignId('storyline_id')->nullable()->constrained('storylines')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('match_logs');
        Schema::dropIfExists('show_logs');
        Schema::dropIfExists('storyline_events');
        Schema::dropIfExists('storylines');
        Schema::dropIfExists('championships');
        Schema::dropIfExists('superstar_team');
        Schema::dropIfExists('teams');
        Schema::dropIfExists('superstars');
        Schema::dropIfExists('shows');
    }
};
