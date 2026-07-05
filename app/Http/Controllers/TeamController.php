<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class TeamController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'members' => 'required|array|min:2',
            'members.*' => [
                'required',
                Rule::exists('superstars', 'id')->where('user_id', auth()->id()),
            ],
        ]);

        $userId = auth()->id();

        DB::transaction(function () use ($validated, $userId) {
            $team = Team::create([
                'name' => $validated['name'],
                'wins' => 0,
                'losses' => 0,
                'draws' => 0,
                'user_id' => $userId,
            ]);

            $team->superstars()->attach($validated['members']);
        });

        return back()->with('toast', 'Faction established successfully!');
    }

    public function destroy(Team $team): RedirectResponse
    {
        if ($team->user_id !== auth()->id()) {
            abort(403);
        }

        $team->delete();

        return back()->with('toast', 'Faction disbanded successfully!');
    }
}
