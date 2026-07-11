<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $show_log_id
 * @property string $division
 * @property int|null $c1_superstar_id
 * @property int|null $c2_superstar_id
 * @property int|null $c1_team_id
 * @property int|null $c2_team_id
 * @property string $outcome
 * @property string|null $winner_slot
 * @property int|null $winner_superstar_id
 * @property int|null $winner_team_id
 * @property int|null $storyline_id
 * @property int|null $championship_id
 * @property string|null $notes
 * @property string|null $stipulation
 * @property int|null $c3_superstar_id
 * @property int|null $c4_superstar_id
 * @property int|null $c3_team_id
 * @property int|null $c4_team_id
 * @property array<int>|null $team1_superstar_ids
 * @property array<int>|null $team2_superstar_ids
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'show_log_id',
    'division',
    'c1_superstar_id',
    'c2_superstar_id',
    'c3_superstar_id',
    'c4_superstar_id',
    'c1_team_id',
    'c2_team_id',
    'c3_team_id',
    'c4_team_id',
    'team1_superstar_ids',
    'team2_superstar_ids',
    'outcome',
    'winner_slot',
    'winner_superstar_id',
    'winner_team_id',
    'storyline_id',
    'championship_id',
    'notes',
    'stipulation',
])]
class MatchLog extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'team1_superstar_ids' => 'array',
            'team2_superstar_ids' => 'array',
        ];
    }

    /**
     * @return BelongsTo<ShowLog, $this>
     */
    public function showLog(): BelongsTo
    {
        return $this->belongsTo(ShowLog::class);
    }

    /**
     * @return BelongsTo<Superstar, $this>
     */
    public function c1Superstar(): BelongsTo
    {
        return $this->belongsTo(Superstar::class, 'c1_superstar_id');
    }

    /**
     * @return BelongsTo<Superstar, $this>
     */
    public function c2Superstar(): BelongsTo
    {
        return $this->belongsTo(Superstar::class, 'c2_superstar_id');
    }

    /**
     * @return BelongsTo<Superstar, $this>
     */
    public function c3Superstar(): BelongsTo
    {
        return $this->belongsTo(Superstar::class, 'c3_superstar_id');
    }

    /**
     * @return BelongsTo<Superstar, $this>
     */
    public function c4Superstar(): BelongsTo
    {
        return $this->belongsTo(Superstar::class, 'c4_superstar_id');
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function c1Team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'c1_team_id');
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function c2Team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'c2_team_id');
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function c3Team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'c3_team_id');
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function c4Team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'c4_team_id');
    }

    /**
     * @return BelongsTo<Superstar, $this>
     */
    public function winnerSuperstar(): BelongsTo
    {
        return $this->belongsTo(Superstar::class, 'winner_superstar_id');
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function winnerTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    /**
     * @return BelongsTo<Storyline, $this>
     */
    public function storyline(): BelongsTo
    {
        return $this->belongsTo(Storyline::class);
    }

    /**
     * @return BelongsTo<Championship, $this>
     */
    public function championship(): BelongsTo
    {
        return $this->belongsTo(Championship::class);
    }
}
