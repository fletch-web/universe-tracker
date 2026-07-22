<?php

namespace App\Http\Controllers;

use App\Models\Championship;
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
            'location' => 'nullable|string|max:255',
            'matches' => 'required|array',
            'matches.*.division' => 'required|string|in:Singles,TagTeam,TripleThreat,Fatal4Way,TripleThreatTag,Fatal4WayTag,ThreeOnThreeTag,FourOnFourTag,Segment',
            'matches.*.c1Id' => 'nullable',
            'matches.*.c2Id' => 'nullable',
            'matches.*.c3Id' => 'nullable',
            'matches.*.c4Id' => 'nullable',
            'matches.*.team1_superstar_ids' => 'nullable|array',
            'matches.*.team1_superstar_ids.*' => 'integer',
            'matches.*.team2_superstar_ids' => 'nullable|array',
            'matches.*.team2_superstar_ids.*' => 'integer',
            'matches.*.outcome' => 'required_unless:matches.*.division,Segment|nullable|string|in:Decisive,Draw',
            'matches.*.winnerSlot' => 'nullable|string|in:1,2,3,4',
            'matches.*.winningId' => 'nullable',
            'matches.*.storylineId' => 'nullable|string',
            'matches.*.championshipId' => 'nullable|string',
            'matches.*.notes' => 'nullable|string',
            'matches.*.stipulation' => 'nullable|string|max:255',
        ]);

        $showId = $request->input('show_id');
        $date = $request->input('date');
        $location = $request->input('location');
        $matchesData = $request->input('matches');
        $show = Show::findOrFail($showId);
        if (! $show instanceof Show || $show->user_id !== auth()->id()) {
            abort(403);
        }

        DB::transaction(function () use ($show, $date, $location, $matchesData) {
            // 1. Create the ShowLog
            $showLog = ShowLog::create([
                'show_id' => $show->id,
                'date' => $date,
                'location' => $location,
            ]);

            // 2. Process each match/segment
            foreach ($matchesData as $matchData) {
                $division = $matchData['division'];
                $c1Id = $matchData['c1Id'] ?? null;
                $c2Id = $matchData['c2Id'] ?? null;
                $c3Id = $matchData['c3Id'] ?? null;
                $c4Id = $matchData['c4Id'] ?? null;
                $outcome = $matchData['outcome'] ?? null;
                $winnerSlot = $matchData['winnerSlot'] ?? null;
                $winningId = $matchData['winningId'] ?? null;
                $storylineId = $matchData['storylineId'] ?? null;
                $championshipId = $matchData['championshipId'] ?? null;
                $notes = $matchData['notes'] ?? null;
                $stipulation = $matchData['stipulation'] ?? null;

                if ($division === 'Segment') {
                    $matchLogData = [
                        'show_log_id' => $showLog->id,
                        'division' => 'Segment',
                        'outcome' => 'Segment',
                        'notes' => $notes,
                        'storyline_id' => ($storylineId && $storylineId !== 'NONE') ? (int) $storylineId : null,
                        'stipulation' => null,
                    ];

                    // Create Storyline Event if linked
                    if ($storylineId && $storylineId !== 'NONE') {
                        $storyline = Storyline::where('id', $storylineId)->where('user_id', auth()->id())->first();
                        if ($storyline instanceof Storyline) {
                            StorylineEvent::create([
                                'storyline_id' => $storyline->id,
                                'date' => $date,
                                'show_name' => $show->name,
                                'description' => $notes ?? 'Storyline segment promo / vignette segment.',
                                'notes' => null,
                            ]);
                        }
                    }

                    MatchLog::create($matchLogData);

                    continue;
                }

                $matchLogData = [
                    'show_log_id' => $showLog->id,
                    'division' => $division,
                    'outcome' => $outcome,
                    'winner_slot' => $outcome === 'Decisive' ? $winnerSlot : null,
                    'notes' => $notes,
                    'storyline_id' => ($storylineId && $storylineId !== 'NONE') ? (int) $storylineId : null,
                    'stipulation' => $stipulation,
                ];

                // Championship linking
                $championship = null;
                if ($championshipId && $championshipId !== 'NONE') {
                    $championship = Championship::where('id', $championshipId)
                        ->where('user_id', auth()->id())
                        ->first();
                    if ($championship instanceof Championship) {
                        $matchLogData['championship_id'] = $championship->id;
                    }
                }

                $isTeam = in_array($division, ['TagTeam', 'TripleThreatTag', 'Fatal4WayTag', 'ThreeOnThreeTag', 'FourOnFourTag']);
                $isAdHoc = in_array($division, ['TagTeam', 'ThreeOnThreeTag', 'FourOnFourTag']) && ! empty($matchData['team1_superstar_ids']);
                $comp1Name = '';
                $comp2Name = '';
                $comp3Name = '';
                $comp4Name = '';
                $winnerName = '';
                $matchParticipantsText = '';

                if ($isAdHoc) {
                    $team1SuperstarIds = $matchData['team1_superstar_ids'] ?? [];
                    $team2SuperstarIds = $matchData['team2_superstar_ids'] ?? [];

                    $t1Superstars = Superstar::whereIn('id', $team1SuperstarIds)->where('user_id', auth()->id())->get();
                    $t2Superstars = Superstar::whereIn('id', $team2SuperstarIds)->where('user_id', auth()->id())->get();

                    if ($t1Superstars->isEmpty() || $t2Superstars->isEmpty()) {
                        continue;
                    }

                    $t1Names = $t1Superstars->pluck('name')->join(' & ');
                    $t2Names = $t2Superstars->pluck('name')->join(' & ');
                    $matchParticipantsText = "{$t1Names} vs {$t2Names}";

                    $matchLogData['team1_superstar_ids'] = $team1SuperstarIds;
                    $matchLogData['team2_superstar_ids'] = $team2SuperstarIds;

                    if ($outcome === 'Decisive') {
                        $winnerName = ($winnerSlot === '1') ? $t1Names : $t2Names;

                        if ($winnerSlot === '1') {
                            foreach ($t1Superstars as $p) {
                                $p->increment('wins');
                            }
                            foreach ($t2Superstars as $p) {
                                $p->increment('losses');
                            }
                        } else {
                            foreach ($t2Superstars as $p) {
                                $p->increment('wins');
                            }
                            foreach ($t1Superstars as $p) {
                                $p->increment('losses');
                            }
                        }
                    } else {
                        foreach ($t1Superstars as $p) {
                            $p->increment('draws');
                        }
                        foreach ($t2Superstars as $p) {
                            $p->increment('draws');
                        }
                        $winnerName = 'Stalemate No Contest (Draw)';
                    }
                } elseif (! $isTeam) {
                    $matchLogData['c1_superstar_id'] = (int) $c1Id;
                    $matchLogData['c2_superstar_id'] = (int) $c2Id;

                    $s1 = Superstar::where('id', $c1Id)->where('user_id', auth()->id())->first();
                    $s2 = Superstar::where('id', $c2Id)->where('user_id', auth()->id())->first();
                    if (! $s1 instanceof Superstar || ! $s2 instanceof Superstar) {
                        continue;
                    }
                    $comp1Name = $s1->name;
                    $comp2Name = $s2->name;
                    $matchParticipantsText = "{$comp1Name} vs {$comp2Name}";

                    $s3 = null;
                    $s4 = null;
                    $participants = [$s1, $s2];

                    if (in_array($division, ['TripleThreat', 'Fatal4Way'])) {
                        $s3 = Superstar::where('id', $c3Id)->where('user_id', auth()->id())->first();
                        if (! $s3 instanceof Superstar) {
                            continue;
                        }
                        $matchLogData['c3_superstar_id'] = $s3->id;
                        $comp3Name = $s3->name;
                        $matchParticipantsText .= " vs {$comp3Name}";
                        $participants[] = $s3;
                    }

                    if ($division === 'Fatal4Way') {
                        $s4 = Superstar::where('id', $c4Id)->where('user_id', auth()->id())->first();
                        if (! $s4 instanceof Superstar) {
                            continue;
                        }
                        $matchLogData['c4_superstar_id'] = $s4->id;
                        $comp4Name = $s4->name;
                        $matchParticipantsText .= " vs {$comp4Name}";
                        $participants[] = $s4;
                    }

                    if ($outcome === 'Decisive') {
                        $matchLogData['winner_superstar_id'] = (int) $winningId;
                        $winner = Superstar::where('id', $winningId)->where('user_id', auth()->id())->first();
                        if (! $winner instanceof Superstar) {
                            continue;
                        }
                        $winnerName = $winner->name;

                        // Increment stats
                        foreach ($participants as $p) {
                            if ($p->id === (int) $winningId) {
                                $p->increment('wins');
                            } else {
                                $p->increment('losses');
                            }
                        }

                        // Update Champion if Championship Match
                        if ($championship instanceof Championship) {
                            $championship->champion_superstar_id = (int) $winningId;
                            $championship->champion_team_id = null;
                            $championship->save();
                        }
                    } else {
                        // Increment draws for all
                        foreach ($participants as $p) {
                            $p->increment('draws');
                        }
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
                    $matchParticipantsText = "{$comp1Name} vs {$comp2Name}";

                    $t3 = null;
                    $t4 = null;
                    $participants = [$t1, $t2];

                    if (in_array($division, ['TripleThreatTag', 'Fatal4WayTag'])) {
                        $t3 = Team::where('id', $c3Id)->where('user_id', auth()->id())->first();
                        if (! $t3 instanceof Team) {
                            continue;
                        }
                        $matchLogData['c3_team_id'] = $t3->id;
                        $comp3Name = $t3->name;
                        $matchParticipantsText .= " vs {$comp3Name}";
                        $participants[] = $t3;
                    }

                    if ($division === 'Fatal4WayTag') {
                        $t4 = Team::where('id', $c4Id)->where('user_id', auth()->id())->first();
                        if (! $t4 instanceof Team) {
                            continue;
                        }
                        $matchLogData['c4_team_id'] = $t4->id;
                        $comp4Name = $t4->name;
                        $matchParticipantsText .= " vs {$comp4Name}";
                        $participants[] = $t4;
                    }

                    if ($outcome === 'Decisive') {
                        if (! $winningId && $winnerSlot) {
                            if ($winnerSlot === '1') {
                                $winningId = $c1Id;
                            } elseif ($winnerSlot === '2') {
                                $winningId = $c2Id;
                            } elseif ($winnerSlot === '3' && $t3) {
                                $winningId = $t3->id;
                            } elseif ($winnerSlot === '4' && $t4) {
                                $winningId = $t4->id;
                            }
                        }

                        $matchLogData['winner_team_id'] = (int) $winningId;
                        $winner = Team::where('id', $winningId)->where('user_id', auth()->id())->first();
                        if (! $winner instanceof Team) {
                            continue;
                        }
                        $winnerName = $winner->name;

                        // Increment stats
                        foreach ($participants as $p) {
                            if ($p->id === (int) $winningId) {
                                $p->increment('wins');
                                foreach ($p->superstars as $superstar) {
                                    $superstar->increment('wins');
                                }
                            } else {
                                $p->increment('losses');
                                foreach ($p->superstars as $superstar) {
                                    $superstar->increment('losses');
                                }
                            }
                        }

                        // Update Champion if Championship Match
                        if ($championship instanceof Championship) {
                            $championship->champion_team_id = (int) $winningId;
                            $championship->champion_superstar_id = null;
                            $championship->save();
                        }
                    } else {
                        // Increment draws for all
                        foreach ($participants as $p) {
                            $p->increment('draws');
                            foreach ($p->superstars as $superstar) {
                                $superstar->increment('draws');
                            }
                        }
                    }
                }

                // 3. Create Storyline Event if linked
                if ($storylineId && $storylineId !== 'NONE') {
                    $storyline = Storyline::where('id', $storylineId)->where('user_id', auth()->id())->first();
                    if ($storyline instanceof Storyline) {
                        $desc = $matchParticipantsText;
                        if ($stipulation) {
                            $desc = "[{$stipulation}] ".$desc;
                        }
                        if ($championship instanceof Championship) {
                            $desc = "[{$championship->name} Match] ".$desc;
                        }
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
                }

                // 4. Save the MatchLog
                MatchLog::create($matchLogData);
            }
        });

        return back()->with('toast', 'Show event run card successfully processed and committed!');
    }
}
