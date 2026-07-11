<?php

namespace App\Http\Controllers;

use App\Models\Show;
use App\Models\Superstar;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DraftController extends Controller
{
    public function commit(Request $request): RedirectResponse
    {
        $userId = auth()->id();
        $validated = $request->validate([
            'draft' => ['required', 'array'],
            'draft.*' => ['required', 'integer'],
        ]);

        $superstarIds = array_keys($validated['draft']);
        $showIds = array_values($validated['draft']);

        // Verify all superstars belong to the user
        $superstarsCount = Superstar::where('user_id', $userId)
            ->whereIn('id', $superstarIds)
            ->count();

        if ($superstarsCount !== count($superstarIds)) {
            abort(403, 'Unauthorized superstars in draft.');
        }

        // Verify all shows belong to the user and are not PLEs
        $uniqueShowIds = array_unique($showIds);
        $showsCount = Show::where('user_id', $userId)
            ->where('is_ple', false)
            ->whereIn('id', $uniqueShowIds)
            ->count();

        if ($showsCount !== count($uniqueShowIds)) {
            abort(403, 'Unauthorized or PLE shows in draft.');
        }

        DB::transaction(function () use ($validated) {
            foreach ($validated['draft'] as $superstarId => $showId) {
                Superstar::where('id', $superstarId)->update(['show_id' => $showId]);
            }
        });

        return back()->with('toast', 'Draft completed and rosters updated successfully!');
    }
}
