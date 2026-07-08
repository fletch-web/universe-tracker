<?php

use App\Models\User;
use Laravel\Fortify\Features;

test('welcome page renders with expected inertia props for guest', function () {
    $response = $this->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Welcome')
        ->has('canResetPassword')
        ->has('status')
        ->has('passwordRules')
    );
});

test('welcome page redirects authenticated users to dashboard', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get(route('home'));

    $response->assertRedirect(route('dashboard'));
});

test('users can login successfully from the home page context', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('new users can register successfully from the home page context', function () {
    $this->skipUnlessFortifyHas(Features::registration());

    $response = $this->post(route('register.store'), [
        'name' => 'Home Page User',
        'email' => 'homepage@example.com',
        'username' => 'homepage_user',
        'password' => 'password',
        'password_confirmation' => 'password',
    ]);

    $this->assertAuthenticated();
    $response->assertRedirect(route('dashboard', absolute: false));
});

test('welcome page renders status message if flashed in session', function () {
    $response = $this->withSession(['status' => 'We have emailed your password reset link!'])
        ->get(route('home'));

    $response->assertOk();
    $response->assertInertia(fn ($page) => $page
        ->component('Welcome')
        ->where('status', 'We have emailed your password reset link!')
    );
});
