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
        return Inertia::render('Dashboard', [
            'shows' => Show::all(),
            'superstars' => Superstar::with('show')->get(),
            'teams' => Team::with('superstars')->get(),
            'championships' => Championship::with(['show', 'championSuperstar', 'championTeam'])->get(),
            'storylines' => Storyline::with('events')->get(),
            'history' => ShowLog::with([
                'show',
                'matches.c1Superstar',
                'matches.c2Superstar',
                'matches.c1Team',
                'matches.c2Team',
                'matches.winnerSuperstar',
                'matches.winnerTeam',
                'matches.storyline',
            ])->latest()->get(),
        ]);
    }
}
