<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['name', 'user_id'])]
class Storyline extends Model
{
    /**
     * @return HasMany<StorylineEvent, $this>
     */
    public function events(): HasMany
    {
        return $this->hasMany(StorylineEvent::class);
    }
}
