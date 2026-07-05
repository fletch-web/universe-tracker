<?php

use App\Models\Championship;
use App\Models\Show;
use App\Models\ShowLog;
use App\Models\Storyline;
use App\Models\Superstar;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseCount;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\assertDatabaseMissing;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('allows authenticated users to CRUD a show', function () {
    actingAs($this->user)
        ->post(route('shows.store'), [
            'name' => 'SmackDown',
            'color' => '#0055ff',
            'image' => 'mock_image_base64_data',
        ])
        ->assertRedirect();

    assertDatabaseHas('shows', [
        'name' => 'SmackDown',
        'color' => '#0055ff',
    ]);

    $show = Show::first();

    actingAs($this->user)
        ->put(route('shows.update', $show), [
            'name' => 'SmackDown Live',
            'color' => '#00aaff',
            'image' => 'new_mock_image_base64_data',
        ])
        ->assertRedirect();

    assertDatabaseHas('shows', [
        'name' => 'SmackDown Live',
        'color' => '#00aaff',
    ]);

    actingAs($this->user)
        ->delete(route('shows.destroy', $show))
        ->assertRedirect();

    assertDatabaseMissing('shows', [
        'id' => $show->id,
    ]);
});

it('allows authenticated users to CRUD a superstar', function () {
    $show = Show::create(['name' => 'Raw', 'color' => '#ff0000']);

    actingAs($this->user)
        ->post(route('superstars.store'), [
            'name' => 'John Cena',
            'gender' => 'Male',
            'show_id' => $show->id,
            'image' => null,
        ])
        ->assertRedirect();

    assertDatabaseHas('superstars', [
        'name' => 'John Cena',
        'gender' => 'Male',
        'show_id' => $show->id,
    ]);

    $superstar = Superstar::first();

    actingAs($this->user)
        ->put(route('superstars.update', $superstar), [
            'name' => 'John Cena (Legend)',
            'gender' => 'Male',
            'show_id' => $show->id,
            'wins' => 10,
            'losses' => 2,
            'draws' => 1,
            'image' => null,
        ])
        ->assertRedirect();

    assertDatabaseHas('superstars', [
        'name' => 'John Cena (Legend)',
        'wins' => 10,
        'losses' => 2,
        'draws' => 1,
    ]);

    actingAs($this->user)
        ->delete(route('superstars.destroy', $superstar))
        ->assertRedirect();

    assertDatabaseMissing('superstars', [
        'id' => $superstar->id,
    ]);
});

it('allows authenticated users to import superstars in batch', function () {
    $show = Show::create(['name' => 'Raw', 'color' => '#ff0000']);

    actingAs($this->user)
        ->post(route('superstars.import'), [
            'superstars' => [
                ['name' => 'Triple H', 'gender' => 'Male', 'brand' => 'Raw'],
                ['name' => 'Rhea Ripley', 'gender' => 'Female', 'brand' => 'Raw'],
            ],
        ])
        ->assertRedirect();

    assertDatabaseHas('superstars', [
        'name' => 'Triple H',
        'show_id' => $show->id,
    ]);

    assertDatabaseHas('superstars', [
        'name' => 'Rhea Ripley',
        'show_id' => $show->id,
    ]);
});

it('allows authenticated users to CRUD teams', function () {
    $show = Show::create(['name' => 'NXT', 'color' => '#ffaa00']);
    $s1 = Superstar::create(['name' => 'Shawn Michaels', 'gender' => 'Male', 'show_id' => $show->id, 'wins' => 0, 'losses' => 0, 'draws' => 0]);
    $s2 = Superstar::create(['name' => 'Triple H', 'gender' => 'Male', 'show_id' => $show->id, 'wins' => 0, 'losses' => 0, 'draws' => 0]);

    actingAs($this->user)
        ->post(route('teams.store'), [
            'name' => 'D-Generation X',
            'members' => [$s1->id, $s2->id],
        ])
        ->assertRedirect();

    assertDatabaseHas('teams', [
        'name' => 'D-Generation X',
    ]);

    $team = Team::first();

    assertDatabaseHas('superstar_team', [
        'superstar_id' => $s1->id,
        'team_id' => $team->id,
    ]);

    actingAs($this->user)
        ->delete(route('teams.destroy', $team))
        ->assertRedirect();

    assertDatabaseMissing('teams', [
        'id' => $team->id,
    ]);
});

it('allows authenticated users to CRUD championships', function () {
    $show = Show::create(['name' => 'SmackDown', 'color' => '#0055ff']);
    $superstar = Superstar::create(['name' => 'Roman Reigns', 'gender' => 'Male', 'show_id' => $show->id, 'wins' => 0, 'losses' => 0, 'draws' => 0]);

    actingAs($this->user)
        ->post(route('championships.store'), [
            'name' => 'WWE Championship',
            'show_id' => $show->id,
            'type' => 'Singles',
            'champion_id' => $superstar->id,
        ])
        ->assertRedirect();

    assertDatabaseHas('championships', [
        'name' => 'WWE Championship',
        'champion_superstar_id' => $superstar->id,
        'champion_team_id' => null,
    ]);

    $champ = Championship::first();

    actingAs($this->user)
        ->put(route('championships.update', $champ), [
            'name' => 'WWE Championship (Updated)',
            'show_id' => $show->id,
            'type' => 'Singles',
            'champion_id' => 'VACANT',
        ])
        ->assertRedirect();

    assertDatabaseHas('championships', [
        'name' => 'WWE Championship (Updated)',
        'champion_superstar_id' => null,
        'champion_team_id' => null,
    ]);

    actingAs($this->user)
        ->delete(route('championships.destroy', $champ))
        ->assertRedirect();

    assertDatabaseMissing('championships', [
        'id' => $champ->id,
    ]);
});

it('allows authenticated users to initiate storylines', function () {
    actingAs($this->user)
        ->post(route('storylines.store'), [
            'name' => 'The Bloodline Drama',
        ])
        ->assertRedirect();

    assertDatabaseHas('storylines', [
        'name' => 'The Bloodline Drama',
    ]);
});

it('allows authenticated users to commit show bookings and updates metrics', function () {
    $show = Show::create(['name' => 'SmackDown', 'color' => '#0055ff']);
    $s1 = Superstar::create(['name' => 'Cody Rhodes', 'gender' => 'Male', 'show_id' => $show->id, 'wins' => 0, 'losses' => 0, 'draws' => 0]);
    $s2 = Superstar::create(['name' => 'Roman Reigns', 'gender' => 'Male', 'show_id' => $show->id, 'wins' => 0, 'losses' => 0, 'draws' => 0]);
    $storyline = Storyline::create(['name' => 'Civil War']);

    actingAs($this->user)
        ->post(route('booking.commit'), [
            'show_id' => $show->id,
            'date' => '2026-07-05',
            'matches' => [
                [
                    'division' => 'Singles',
                    'c1Id' => $s1->id,
                    'c2Id' => $s2->id,
                    'outcome' => 'Decisive',
                    'winnerSlot' => '1',
                    'winningId' => $s1->id,
                    'storylineId' => (string) $storyline->id,
                    'notes' => 'A clean victory after a spectacular layout.',
                ],
            ],
        ])
        ->assertRedirect();

    // Verify ShowLog and MatchLog recorded
    assertDatabaseCount('show_logs', 1);
    assertDatabaseCount('match_logs', 1);

    $s1->refresh();
    $s2->refresh();

    // Winner wins incremented, loser losses incremented
    expect($s1->wins)->toBe(1);
    expect($s2->losses)->toBe(1);

    // Verify Storyline Event added
    assertDatabaseHas('storyline_events', [
        'storyline_id' => $storyline->id,
        'date' => '2026-07-05',
        'show_name' => 'SmackDown',
        'notes' => 'A clean victory after a spectacular layout.',
    ]);
});
