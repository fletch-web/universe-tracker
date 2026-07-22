<?php

namespace App\Http\Controllers;

use App\Models\Championship;
use App\Models\Show;
use App\Models\ShowLog;
use App\Models\Storyline;
use App\Models\Superstar;
use App\Models\Team;
use App\Models\User;
use Inertia\Inertia;
use Inertia\Response;

class PublicUniverseController extends Controller
{
    public function show(string $username): Response
    {
        $user = User::where('username', $username)
            ->when(! auth()->check() || auth()->user()->username !== $username, function ($query) {
                $query->where('is_public', true);
            })
            ->firstOrFail();
        $userId = $user->id;

        return Inertia::render('Dashboard', [
            'shows' => Show::where('user_id', $userId)->orderBy('name')->get(),
            'superstars' => Superstar::where('user_id', $userId)->with('show')->orderBy('name')->get(),
            'teams' => Team::where('user_id', $userId)->with('superstars')->orderBy('name')->get(),
            'paginatedSuperstars' => Inertia::scroll(fn () => Superstar::where('user_id', $userId)->with('show')->orderBy('name')->paginate(10)),
            'paginatedTeams' => Inertia::scroll(fn () => Team::where('user_id', $userId)->with('superstars')->orderBy('name')->paginate(10)),
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
            ])->orderBy('date', 'desc')->orderBy('created_at', 'desc')->get(),
            'isReadOnly' => ! auth()->check() || auth()->user()->id !== $userId,
            'owner' => [
                'name' => $user->name,
                'username' => $user->username,
            ],
        ]);
    }
}
