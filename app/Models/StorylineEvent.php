<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $storyline_id
 * @property string $date
 * @property string $show_name
 * @property string $description
 * @property string|null $notes
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['storyline_id', 'date', 'show_name', 'description', 'notes'])]
class StorylineEvent extends Model
{
    /**
     * @return BelongsTo<Storyline, $this>
     */
    public function storyline(): BelongsTo
    {
        return $this->belongsTo(Storyline::class);
    }
}
