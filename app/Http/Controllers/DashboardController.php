<?php

namespace App\Http\Controllers;

use App\Models\Championship;
use App\Models\Show;
use App\Models\ShowLog;
use App\Models\Storyline;
use App\Models\Superstar;
use App\Models\Team;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $userId = auth()->id();

        return Inertia::render('Dashboard', [
            'shows' => Show::where('user_id', $userId)->get(),
            'superstars' => Superstar::where('user_id', $userId)->with('show')->get(),
            'teams' => Team::where('user_id', $userId)->with('superstars')->get(),
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
}
