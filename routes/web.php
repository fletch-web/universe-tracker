<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\ChampionshipController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ShowController;
use App\Http\Controllers\StorylineController;
use App\Http\Controllers\SuperstarController;
use App\Http\Controllers\TeamController;
use Illuminate\Support\Facades\Route;
use Illuminate\Validation\Rules\Password;
use Inertia\Inertia;
use Laravel\Fortify\Features;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return Inertia::render('Welcome', [
        'canResetPassword' => Features::enabled(Features::resetPasswords()),
        'status' => session('status'),
        'passwordRules' => Password::defaults()->toPasswordRulesString(),
    ]);
})->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('universe/clear', [DashboardController::class, 'clear'])->name('universe.clear');

    Route::post('shows', [ShowController::class, 'store'])->name('shows.store');
    Route::put('shows/{show}', [ShowController::class, 'update'])->name('shows.update');
    Route::delete('shows/{show}', [ShowController::class, 'destroy'])->name('shows.destroy');

    Route::post('superstars', [SuperstarController::class, 'store'])->name('superstars.store');
    Route::put('superstars/{superstar}', [SuperstarController::class, 'update'])->name('superstars.update');
    Route::delete('superstars/{superstar}', [SuperstarController::class, 'destroy'])->name('superstars.destroy');
    Route::post('superstars/import', [SuperstarController::class, 'import'])->name('superstars.import');

    Route::post('teams', [TeamController::class, 'store'])->name('teams.store');
    Route::delete('teams/{team}', [TeamController::class, 'destroy'])->name('teams.destroy');

    Route::post('championships', [ChampionshipController::class, 'store'])->name('championships.store');
    Route::put('championships/{championship}', [ChampionshipController::class, 'update'])->name('championships.update');
    Route::delete('championships/{championship}', [ChampionshipController::class, 'destroy'])->name('championships.destroy');

    Route::post('storylines', [StorylineController::class, 'store'])->name('storylines.store');

    Route::post('booking/commit', BookingController::class)->name('booking.commit');
});

require __DIR__.'/settings.php';

Route::get('/@{username}', [App\Http\Controllers\PublicUniverseController::class, 'show'])->name('universe.public');
