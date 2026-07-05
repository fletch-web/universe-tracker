<?php

namespace App\Http\Controllers;

use App\Models\Championship;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ChampionshipController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'show_id' => 'required|exists:shows,id',
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
                $data['champion_superstar_id'] = (int) $champId;
            } else {
                $data['champion_team_id'] = (int) $champId;
            }
        }

        Championship::create($data);

        return back()->with('toast', 'Championship minted successfully!');
    }

    public function update(Request $request, Championship $championship): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'show_id' => 'required|exists:shows,id',
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
                $data['champion_superstar_id'] = (int) $champId;
            } else {
                $data['champion_team_id'] = (int) $champId;
            }
        }

        $championship->update($data);

        return back()->with('toast', 'Championship updated successfully!');
    }

    public function destroy(Championship $championship): RedirectResponse
    {
        $championship->delete();

        return back()->with('toast', 'Championship decommissioned successfully!');
    }
}
