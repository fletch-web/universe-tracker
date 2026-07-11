<?php

namespace App\Http\Controllers;

use App\Models\Championship;
use App\Models\Show;
use App\Models\ShowLog;
use App\Models\Storyline;
use App\Models\Superstar;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return redirect()->route('universe.public', ['username' => auth()->user()->username]);
    }

    public function clear(): RedirectResponse
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
