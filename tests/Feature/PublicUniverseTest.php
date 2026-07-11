<?php

use App\Models\Show;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\actingAs;
use function Pest\Laravel\assertDatabaseHas;
use function Pest\Laravel\get;
use function Pest\Laravel\post;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->user = User::factory()->create([
        'username' => 'john_doe',
        'is_public' => false,
    ]);
});

it('allows registration with a unique username', function () {
    $response = post(route('register'), [
        'name' => 'Jane Doe',
        'username' => 'jane_doe',
        'email' => 'jane@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertRedirect();
    assertDatabaseHas('users', [
        'username' => 'jane_doe',
        'email' => 'jane@example.com',
    ]);
});

it('rejects registration with a duplicate username', function () {
    $response = post(route('register'), [
        'name' => 'John Duplicate',
        'username' => 'john_doe', // Already taken by $this->user
        'email' => 'duplicate@example.com',
        'password' => 'Password123!',
        'password_confirmation' => 'Password123!',
    ]);

    $response->assertSessionHasErrors(['username']);
});

it('allows updating profile username and public visibility settings', function () {
    actingAs($this->user)
        ->patch(route('profile.update'), [
            'name' => 'John Modified',
            'email' => $this->user->email,
            'username' => 'new_john',
            'is_public' => '1',
        ])
        ->assertRedirect();

    assertDatabaseHas('users', [
        'id' => $this->user->id,
        'username' => 'new_john',
        'is_public' => true,
    ]);
});

it('returns 404 for a private user universe page', function () {
    get('/@john_doe')
        ->assertNotFound();
});

it('returns 404 if the username does not exist', function () {
    get('/@nonexistent_user')
        ->assertNotFound();
});

it('allows anyone to access a public user read-only universe page', function () {
    // Mark user public
    $this->user->update([
        'is_public' => true,
    ]);

    // Create a show for this user
    Show::create([
        'name' => 'Raw',
        'color' => '#ff0000',
        'user_id' => $this->user->id,
    ]);

    get('/@john_doe')
        ->assertOk()
        ->assertInertia(function ($page) {
            $props = $page->toArray()['props'];

            expect($props['isReadOnly'])->toBeTrue();
            expect($props['owner']['name'])->toBe($this->user->name);
            expect($props['owner']['username'])->toBe('john_doe');
            expect($props['shows'])->toHaveCount(1);
            expect($props['shows'][0]['name'])->toBe('Raw');
        });
});

it('allows the owner to access their own private universe page with edit permissions', function () {
    actingAs($this->user)
        ->get('/@john_doe')
        ->assertOk()
        ->assertInertia(function ($page) {
            $props = $page->toArray()['props'];

            expect($props['isReadOnly'])->toBeFalse();
            expect($props['owner']['username'])->toBe('john_doe');
        });
});

it('lists users whose universe is public on the landing page', function () {
    $publicUser = User::factory()->create([
        'username' => 'public_user',
        'is_public' => true,
    ]);

    get(route('home'))
        ->assertOk()
        ->assertInertia(function ($page) {
            $props = $page->toArray()['props'];

            expect($props['publicUsers'])->toHaveCount(1);
            expect($props['publicUsers'][0]['username'])->toBe('public_user');
        });
});
