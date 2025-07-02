<?php

use App\Http\Controllers\MusicPlayerController;
use App\Livewire\Settings\Appearance;
use App\Livewire\Settings\Password;
use App\Livewire\Settings\Profile;
use Illuminate\Support\Facades\Route;

// Music Player Routes (no authentication required)
Route::get('/', [MusicPlayerController::class, 'index'])->name('home');
Route::get('/music-player', function () {
    return view('music-player.index');
})->name('music-player');
Route::get('/simple', function () {
    return view('music-player.simple');
})->name('simple');

Route::get('/test', function () {
    return view('test');
})->name('test');

Route::get('/working', function () {
    return view('music-working');
})->name('working');

// Health check endpoint
Route::get('/health', [MusicPlayerController::class, 'healthCheck'])->name('health');

// Admin routes
Route::get('/admin/metadata', [MusicPlayerController::class, 'showMetadataManager'])->name('admin.metadata');

// API Routes for music player with rate limiting
Route::prefix('api/music')->middleware(['api.rate_limit:api'])->group(function () {
    Route::get('tracks', [MusicPlayerController::class, 'getTracks'])->name('api.tracks');
    Route::get('tracks/{track}', [MusicPlayerController::class, 'getTrack'])->name('api.track');
    Route::get('genres', [MusicPlayerController::class, 'getGenres'])->name('api.genres');
    Route::get('artists', [MusicPlayerController::class, 'getArtists'])->name('api.artists');
    Route::get('stats', [MusicPlayerController::class, 'getStats'])->name('api.stats');
    Route::get('stream/{track}', [MusicPlayerController::class, 'streamTrack'])->name('api.stream');
    Route::get('download/{track}', [MusicPlayerController::class, 'downloadTrack'])->name('api.download');
    Route::get('download-url/{track}', [MusicPlayerController::class, 'getDownloadUrl'])->name('api.download-url');
    Route::post('update-duration', [MusicPlayerController::class, 'updateDuration'])->name('api.update-duration');
    Route::patch('tracks/{track}', [MusicPlayerController::class, 'updateTrack'])->name('api.track.update');
    Route::post('tracks/bulk-update', [MusicPlayerController::class, 'bulkUpdateTracks'])->name('api.tracks.bulk-update');

    // Sync endpoint with stricter rate limiting
    Route::post('sync', [MusicPlayerController::class, 'syncTracks'])
        ->middleware('api.rate_limit:sync')
        ->name('api.sync');
});

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', 'settings/profile');

    Route::get('settings/profile', Profile::class)->name('settings.profile');
    Route::get('settings/password', Password::class)->name('settings.password');
    Route::get('settings/appearance', Appearance::class)->name('settings.appearance');
});

require __DIR__.'/auth.php';
