<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string $color
 * @property string|null $image
 * @property bool $is_ple
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'color', 'image', 'user_id', 'is_ple'])]
class Show extends Model
{
    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_ple' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Superstar, $this>
     */
    public function superstars(): HasMany
    {
        return $this->hasMany(Superstar::class);
    }

    /**
     * @return HasMany<Championship, $this>
     */
    public function championships(): HasMany
    {
        return $this->hasMany(Championship::class);
    }

    /**
     * @return HasMany<ShowLog, $this>
     */
    public function showLogs(): HasMany
    {
        return $this->hasMany(ShowLog::class);
    }
}
