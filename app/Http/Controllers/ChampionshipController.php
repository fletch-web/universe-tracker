<?php

namespace App\Http\Controllers;

use App\Models\Championship;
use App\Models\Superstar;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ChampionshipController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'show_id' => [
                'required',
                Rule::exists('shows', 'id')->where('user_id', auth()->id()),
            ],
            'type' => 'required|string|in:Singles,TagTeam',
            'champion_id' => 'nullable',
        ]);

        $data = [
            'name' => $validated['name'],
            'show_id' => $validated['show_id'],
            'type' => $validated['type'],
            'champion_superstar_id' => null,
            'champion_team_id' => null,
            'user_id' => auth()->id(),
        ];

        $champId = $validated['champion_id'] ?? null;
        if ($champId && $champId !== 'VACANT') {
            if ($validated['type'] === 'Singles') {
                $superstar = Superstar::where('id', $champId)->where('user_id', auth()->id())->first();
                if (! $superstar) {
                    return back()->withErrors(['champion_id' => 'Invalid superstar selected.']);
                }
                $data['champion_superstar_id'] = $superstar->id;
            } else {
                $team = Team::where('id', $champId)->where('user_id', auth()->id())->first();
                if (! $team) {
                    return back()->withErrors(['champion_id' => 'Invalid team selected.']);
                }
                $data['champion_team_id'] = $team->id;
            }
        }

        Championship::create($data);

        return back()->with('toast', 'Championship minted successfully!');
    }

    public function update(Request $request, Championship $championship): RedirectResponse
    {
        if ($championship->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'show_id' => [
                'required',
                Rule::exists('shows', 'id')->where('user_id', auth()->id()),
            ],
            'type' => 'required|string|in:Singles,TagTeam',
            'champion_id' => 'nullable',
        ]);

        $data = [
            'name' => $validated['name'],
            'show_id' => $validated['show_id'],
            'type' => $validated['type'],
            'champion_superstar_id' => null,
            'champion_team_id' => null,
        ];

        $champId = $validated['champion_id'] ?? null;
        if ($champId && $champId !== 'VACANT') {
            if ($validated['type'] === 'Singles') {
                $superstar = Superstar::where('id', $champId)->where('user_id', auth()->id())->first();
                if (! $superstar) {
                    return back()->withErrors(['champion_id' => 'Invalid superstar selected.']);
                }
                $data['champion_superstar_id'] = $superstar->id;
            } else {
                $team = Team::where('id', $champId)->where('user_id', auth()->id())->first();
                if (! $team) {
                    return back()->withErrors(['champion_id' => 'Invalid team selected.']);
                }
                $data['champion_team_id'] = $team->id;
            }
        }

        $championship->update($data);

        return back()->with('toast', 'Championship updated successfully!');
    }

    public function destroy(Championship $championship): RedirectResponse
    {
        if ($championship->user_id !== auth()->id()) {
            abort(403);
        }

        $championship->delete();

        return back()->with('toast', 'Championship decommissioned successfully!');
    }
}
