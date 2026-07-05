<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'members' => 'required|array|min:2',
            'members.*' => 'required|exists:superstars,id',
        ]);

        DB::transaction(function () use ($validated) {
            $team = Team::create([
                'name' => $validated['name'],
                'wins' => 0,
                'losses' => 0,
                'draws' => 0,
            ]);

            $team->superstars()->attach($validated['members']);
        });

        return back()->with('toast', 'Faction established successfully!');
    }

    public function destroy(Team $team): RedirectResponse
    {
        $team->delete();

        return back()->with('toast', 'Faction disbanded successfully!');
    }
}
