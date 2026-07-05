<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int $wins
 * @property int $losses
 * @property int $draws
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'wins', 'losses', 'draws', 'user_id'])]
class Team extends Model
{
    /**
     * @return BelongsToMany<Superstar, $this>
     */
    public function superstars(): BelongsToMany
    {
        return $this->belongsToMany(Superstar::class);
    }
}
