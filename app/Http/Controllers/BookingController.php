<?php

namespace App\Http\Controllers;

use App\Models\MatchLog;
use App\Models\Show;
use App\Models\ShowLog;
use App\Models\Storyline;
use App\Models\StorylineEvent;
use App\Models\Superstar;
use App\Models\Team;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class BookingController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $request->validate([
            'show_id' => [
                'required',
                Rule::exists('shows', 'id')->where('user_id', auth()->id()),
            ],
            'date' => 'required|date',
            'matches' => 'required|array',
            'matches.*.division' => 'required|string|in:Singles,TagTeam',
            'matches.*.c1Id' => 'required',
            'matches.*.c2Id' => 'required',
            'matches.*.outcome' => 'required|string|in:Decisive,Draw',
            'matches.*.winnerSlot' => 'nullable|string|in:1,2',
            'matches.*.winningId' => 'required',
            'matches.*.storylineId' => 'nullable|string',
            'matches.*.notes' => 'nullable|string',
        ]);

        $showId = $request->input('show_id');
        $date = $request->input('date');
        $matchesData = $request->input('matches');
        $show = Show::findOrFail($showId);
        if (! $show instanceof Show || $show->user_id !== auth()->id()) {
            abort(403);
        }

        DB::transaction(function () use ($show, $date, $matchesData) {
            // 1. Create the ShowLog
            $showLog = ShowLog::create([
                'show_id' => $show->id,
                'date' => $date,
            ]);

            // 2. Process each match
            foreach ($matchesData as $matchData) {
                $division = $matchData['division'];
                $c1Id = $matchData['c1Id'];
                $c2Id = $matchData['c2Id'];
                $outcome = $matchData['outcome'];
                $winnerSlot = $matchData['winnerSlot'];
                $winningId = $matchData['winningId'];
                $storylineId = $matchData['storylineId'];
                $notes = $matchData['notes'] ?? null;

                $matchLogData = [
                    'show_log_id' => $showLog->id,
                    'division' => $division,
                    'outcome' => $outcome,
                    'winner_slot' => $outcome === 'Decisive' ? $winnerSlot : null,
                    'notes' => $notes,
                    'storyline_id' => ($storylineId && $storylineId !== 'NONE') ? (int) $storylineId : null,
                ];

                $comp1Name = '';
                $comp2Name = '';
                $winnerName = '';

                if ($division === 'Singles') {
                    $matchLogData['c1_superstar_id'] = (int) $c1Id;
                    $matchLogData['c2_superstar_id'] = (int) $c2Id;

                    $s1 = Superstar::where('id', $c1Id)->where('user_id', auth()->id())->first();
                    $s2 = Superstar::where('id', $c2Id)->where('user_id', auth()->id())->first();
                    if (! $s1 instanceof Superstar || ! $s2 instanceof Superstar) {
                        continue;
                    }
                    $comp1Name = $s1->name;
                    $comp2Name = $s2->name;

                    if ($outcome === 'Decisive') {
                        $matchLogData['winner_superstar_id'] = (int) $winningId;
                        $winner = Superstar::where('id', $winningId)->where('user_id', auth()->id())->first();
                        if (! $winner instanceof Superstar) {
                            continue;
                        }
                        $winnerName = $winner->name;

                        // Increment wins/losses
                        $winner->increment('wins');
                        $loserId = ((int) $winningId === (int) $c1Id) ? $c2Id : $c1Id;
                        $loser = Superstar::where('id', $loserId)->where('user_id', auth()->id())->first();
                        if ($loser instanceof Superstar) {
                            $loser->increment('losses');
                        }
                    } else {
                        // Increment draws for both
                        $s1->increment('draws');
                        $s2->increment('draws');
                    }
                } else {
                    $matchLogData['c1_team_id'] = (int) $c1Id;
                    $matchLogData['c2_team_id'] = (int) $c2Id;

                    $t1 = Team::where('id', $c1Id)->where('user_id', auth()->id())->first();
                    $t2 = Team::where('id', $c2Id)->where('user_id', auth()->id())->first();
                    if (! $t1 instanceof Team || ! $t2 instanceof Team) {
                        continue;
                    }
                    $comp1Name = $t1->name;
                    $comp2Name = $t2->name;

                    if ($outcome === 'Decisive') {
                        $matchLogData['winner_team_id'] = (int) $winningId;
                        $winner = Team::where('id', $winningId)->where('user_id', auth()->id())->first();
                        if (! $winner instanceof Team) {
                            continue;
                        }
                        $winnerName = $winner->name;

                        // Increment wins/losses
                        $winner->increment('wins');
                        $loserId = ((int) $winningId === (int) $c1Id) ? $c2Id : $c1Id;
                        $loser = Team::where('id', $loserId)->where('user_id', auth()->id())->first();
                        if ($loser instanceof Team) {
                            $loser->increment('losses');
                        }
                    } else {
                        // Increment draws for both
                        $t1->increment('draws');
                        $t2->increment('draws');
                    }
                }

                // 3. Create Storyline Event if linked
                if ($storylineId && $storylineId !== 'NONE') {
                    $storyline = Storyline::where('id', $storylineId)->where('user_id', auth()->id())->first();
                    if (! $storyline instanceof Storyline) {
                        continue;
                    }

                    $desc = "{$comp1Name} vs {$comp2Name}";
                    if ($outcome === 'Decisive') {
                        $desc .= " -> {$winnerName} scores the validation victory.";
                    } else {
                        $desc .= ' -> Ends in a chaotic draw dispute segment.';
                    }

                    StorylineEvent::create([
                        'storyline_id' => $storyline->id,
                        'date' => $date,
                        'show_name' => $show->name,
                        'description' => $desc,
                        'notes' => $notes,
                    ]);
                }

                // 4. Save the MatchLog
                MatchLog::create($matchLogData);
            }
        });

        return back()->with('toast', 'Show event run card successfully processed and committed!');
    }
}
