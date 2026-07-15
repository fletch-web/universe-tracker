<?php

namespace App\Http\Controllers;

use App\Models\Championship;
use App\Models\Show;
use App\Models\Superstar;
use App\Models\Team;
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

        // Find championships that belong to a show for this user
        $championships = Championship::where('user_id', $userId)
            ->whereNotNull('show_id')
            ->get();

        $excludedSuperstarIds = [];
        $championshipShowMapping = [];

        foreach ($championships as $championship) {
            if ($championship->type === 'Singles') {
                if ($championship->champion_superstar_id) {
                    $excludedSuperstarIds[] = $championship->champion_superstar_id;
                    $championshipShowMapping[$championship->champion_superstar_id] = $championship->show_id;
                }
            } else {
                if ($championship->champion_team_id) {
                    $team = Team::with('superstars')->find($championship->champion_team_id);
                    if ($team) {
                        foreach ($team->superstars as $superstar) {
                            $excludedSuperstarIds[] = $superstar->id;
                            $championshipShowMapping[$superstar->id] = $championship->show_id;
                        }
                    }
                }
            }
        }

        DB::transaction(function () use ($validated, $excludedSuperstarIds, $championshipShowMapping) {
            foreach ($validated['draft'] as $superstarId => $showId) {
                if (in_array($superstarId, $excludedSuperstarIds)) {
                    $champShowId = $championshipShowMapping[$superstarId];
                    Superstar::where('id', $superstarId)->update(['show_id' => $champShowId]);
                } else {
                    Superstar::where('id', $superstarId)->update(['show_id' => $showId]);
                }
            }
        });

        return back()->with('toast', 'Draft completed and rosters updated successfully!');
    }
}
