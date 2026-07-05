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
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable([
    'show_log_id',
    'division',
    'c1_superstar_id',
    'c2_superstar_id',
    'c1_team_id',
    'c2_team_id',
    'outcome',
    'winner_slot',
    'winner_superstar_id',
    'winner_team_id',
    'storyline_id',
    'notes',
])]
class MatchLog extends Model
{
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
}
