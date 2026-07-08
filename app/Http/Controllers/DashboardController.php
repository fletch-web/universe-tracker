<?php

namespace App\Http\Controllers;

use App\Models\Championship;
use App\Models\Show;
use App\Models\ShowLog;
use App\Models\Storyline;
use App\Models\Superstar;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(Request $request): Response
    {
        $userId = auth()->id();

        return Inertia::render('Dashboard', [
            'shows' => Show::where('user_id', $userId)->get(),
            'superstars' => Superstar::where('user_id', $userId)->with('show')->get(),
            'teams' => Team::where('user_id', $userId)->with('superstars')->get(),
            'paginatedSuperstars' => Inertia::scroll(fn () => Superstar::where('user_id', $userId)->with('show')->paginate(10)),
            'paginatedTeams' => Inertia::scroll(fn () => Team::where('user_id', $userId)->with('superstars')->paginate(10)),
            'championships' => Championship::where('user_id', $userId)->with(['show', 'championSuperstar', 'championTeam'])->get(),
            'storylines' => Storyline::where('user_id', $userId)->with('events')->get(),
            'history' => ShowLog::whereHas('show', function ($q) use ($userId) {
                $q->where('user_id', $userId);
            })->with([
                'show',
                'matches.c1Superstar',
                'matches.c2Superstar',
                'matches.c3Superstar',
                'matches.c4Superstar',
                'matches.c1Team',
                'matches.c2Team',
                'matches.c3Team',
                'matches.c4Team',
                'matches.winnerSuperstar',
                'matches.winnerTeam',
                'matches.storyline',
                'matches.championship',
            ])->latest()->get(),
        ]);
    }

    public function clear(): \Illuminate\Http\RedirectResponse
    {
        $userId = auth()->id();

        // 1. Delete ShowLogs (cascade-deletes MatchLogs)
        ShowLog::whereHas('show', function ($q) use ($userId) {
            $q->where('user_id', $userId);
        })->delete();

        // 2. Delete user-owned records (cascade-deletes stories, storylines, teams, superstar pivots)
        Championship::where('user_id', $userId)->delete();
        Superstar::where('user_id', $userId)->delete();
        Team::where('user_id', $userId)->delete();
        Storyline::where('user_id', $userId)->delete();
        Show::where('user_id', $userId)->delete();

        return redirect()->route('dashboard')->with('toast', 'Universe data cleared completely!');
    }
}
