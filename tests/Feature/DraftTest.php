<?php

use App\Models\Show;
use App\Models\Superstar;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create();
});

it('redirects guests to the login page on draft commit', function () {
    post(route('draft.commit'), ['draft' => []])
        ->assertRedirect(route('login'));
});

it('allows authenticated users to commit draft roster assignments', function () {
    $showA = Show::create(['name' => 'Raw', 'color' => '#ff0000', 'user_id' => $this->user->id]);
    $showB = Show::create(['name' => 'SmackDown', 'color' => '#0055ff', 'user_id' => $this->user->id]);

    $superstarA = Superstar::create(['name' => 'Cody Rhodes', 'gender' => 'Male', 'show_id' => $showA->id, 'user_id' => $this->user->id]);
    $superstarB = Superstar::create(['name' => 'Seth Rollins', 'gender' => 'Male', 'show_id' => $showA->id, 'user_id' => $this->user->id]);

    actingAs($this->user)
        ->post(route('draft.commit'), [
            'draft' => [
                $superstarA->id => $showB->id,
                $superstarB->id => $showB->id,
            ],
        ])
        ->assertRedirect();

    assertDatabaseHas('superstars', [
        'id' => $superstarA->id,
        'show_id' => $showB->id,
    ]);

    assertDatabaseHas('superstars', [
        'id' => $superstarB->id,
        'show_id' => $showB->id,
    ]);
});

it('prevents authenticated users from drafting superstars owned by others', function () {
    $otherUser = User::factory()->create();
    $showA = Show::create(['name' => 'Raw', 'color' => '#ff0000', 'user_id' => $this->user->id]);
    $otherShow = Show::create(['name' => 'Other Show', 'color' => '#111111', 'user_id' => $otherUser->id]);
    $otherSuperstar = Superstar::create(['name' => 'Other Superstar', 'gender' => 'Male', 'show_id' => $otherShow->id, 'user_id' => $otherUser->id]);

    actingAs($this->user)
        ->post(route('draft.commit'), [
            'draft' => [
                $otherSuperstar->id => $showA->id,
            ],
        ])
        ->assertStatus(403);
});

it('prevents authenticated users from drafting to shows owned by others', function () {
    $otherUser = User::factory()->create();
    $showA = Show::create(['name' => 'Raw', 'color' => '#ff0000', 'user_id' => $this->user->id]);
    $otherShow = Show::create(['name' => 'Other Show', 'color' => '#111111', 'user_id' => $otherUser->id]);
    $superstarA = Superstar::create(['name' => 'Cody Rhodes', 'gender' => 'Male', 'show_id' => $showA->id, 'user_id' => $this->user->id]);

    actingAs($this->user)
        ->post(route('draft.commit'), [
            'draft' => [
                $superstarA->id => $otherShow->id,
            ],
        ])
        ->assertStatus(403);
});

it('prevents authenticated users from drafting to PLE shows', function () {
    $showA = Show::create(['name' => 'Raw', 'color' => '#ff0000', 'user_id' => $this->user->id]);
    $pleShow = Show::create(['name' => 'WrestleMania', 'color' => '#f59e0b', 'is_ple' => true, 'user_id' => $this->user->id]);
    $superstarA = Superstar::create(['name' => 'Cody Rhodes', 'gender' => 'Male', 'show_id' => $showA->id, 'user_id' => $this->user->id]);

    actingAs($this->user)
        ->post(route('draft.commit'), [
            'draft' => [
                $superstarA->id => $pleShow->id,
            ],
        ])
        ->assertStatus(403);
});
