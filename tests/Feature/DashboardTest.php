<?php

use App\Models\Championship;
use App\Models\Show;
use App\Models\Superstar;
use App\Models\User;

test('guests are redirected to the login page', function () {
    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('login'));
});

test('authenticated users are redirected to their profile url', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->get(route('dashboard'));
    $response->assertRedirect(route('universe.public', ['username' => $user->username]));
});

test('a championship can be created without a show assigned', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $response = $this->post(route('championships.store'), [
        'name' => 'Independent Championship',
        'type' => 'Singles',
        'show_id' => '',
        'champion_id' => 'VACANT',
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('championships', [
        'name' => 'Independent Championship',
        'show_id' => null,
        'user_id' => $user->id,
    ]);
});

test('a championship cannot be assigned to a PLE show', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $pleShow = Show::create([
        'name' => 'WrestleMania',
        'color' => '#ff0000',
        'is_ple' => true,
        'user_id' => $user->id,
    ]);

    $response = $this->post(route('championships.store'), [
        'name' => 'PLE Championship',
        'type' => 'Singles',
        'show_id' => $pleShow->id,
        'champion_id' => 'VACANT',
    ]);

    $response->assertSessionHasErrors(['show_id']);
    $this->assertDatabaseMissing('championships', [
        'name' => 'PLE Championship',
    ]);
});

test('draft commits preserve champion superstars on their show', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $showA = Show::create([
        'name' => 'Raw',
        'color' => '#ff0000',
        'user_id' => $user->id,
    ]);

    $showB = Show::create([
        'name' => 'Smackdown',
        'color' => '#0000ff',
        'user_id' => $user->id,
    ]);

    $superstar = Superstar::create([
        'name' => 'John Cena',
        'gender' => 'Male',
        'show_id' => $showA->id,
        'user_id' => $user->id,
    ]);

    // John Cena is champion of a Raw title
    $championship = Championship::create([
        'name' => 'Raw Title',
        'show_id' => $showA->id,
        'type' => 'Singles',
        'champion_superstar_id' => $superstar->id,
        'user_id' => $user->id,
    ]);

    // Commit a draft attempting to move John Cena to Smackdown
    $response = $this->post('/draft/commit', [
        'draft' => [
            $superstar->id => $showB->id,
        ],
    ]);

    $response->assertSessionHasNoErrors();
    // John Cena should remain on Show A (Raw)
    expect($superstar->fresh()->show_id)->toBe($showA->id);
});

test('recording a show accepts a location parameter and stores it in show logs', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $show = Show::create([
        'name' => 'Raw',
        'color' => '#ff0000',
        'user_id' => $user->id,
    ]);

    $response = $this->post('/booking/commit', [
        'show_id' => $show->id,
        'date' => '2026-07-15',
        'location' => 'Madison Square Garden',
        'matches' => [
            [
                'division' => 'Segment',
                'notes' => 'Promo segment',
            ],
        ],
    ]);

    $response->assertSessionHasNoErrors();
    $this->assertDatabaseHas('show_logs', [
        'show_id' => $show->id,
        'date' => '2026-07-15',
        'location' => 'Madison Square Garden',
    ]);
});
