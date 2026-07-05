<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int|null $show_id
 * @property string $type
 * @property int|null $champion_superstar_id
 * @property int|null $champion_team_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'show_id', 'type', 'champion_superstar_id', 'champion_team_id'])]
class Championship extends Model
{
    /**
     * @return BelongsTo<Show, $this>
     */
    public function show(): BelongsTo
    {
        return $this->belongsTo(Show::class);
    }

    /**
     * @return BelongsTo<Superstar, $this>
     */
    public function championSuperstar(): BelongsTo
    {
        return $this->belongsTo(Superstar::class, 'champion_superstar_id');
    }

    /**
     * @return BelongsTo<Team, $this>
     */
    public function championTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'champion_team_id');
    }
}
