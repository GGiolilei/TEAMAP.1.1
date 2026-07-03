<?php

use Illuminate\Support\Facades\Route;
use App\Models\Lobby;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LobbyController;
use App\Http\Controllers\LobbyMembershipController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\ChannelController;
use App\Http\Controllers\MessageController;

/*
|--------------------------------------------------------------------------
| Public Routes
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| Auth Routes (Login, Registration, etc.)
|--------------------------------------------------------------------------
*/
require __DIR__ . '/auth.php';

/*
|--------------------------------------------------------------------------
| Protected App Workspace Layer
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {

    // --- Dashboard ---
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // --- Profile Management ---
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::patch('/profile/cv', [ProfileController::class, 'updateCv'])->name('profile.cv.update');

    // --- Lobbies Hub Management ---
    Route::get('/lobbies', [LobbyController::class, 'index'])->name('lobbies.index');
    Route::get('/lobbies/create', [LobbyController::class, 'create'])->name('lobbies.create');
    Route::post('/lobbies', [LobbyController::class, 'store'])->name('lobbies.store');
    
    // --- Legacy / Alternative Singular Aliases for Lobbies ---
    Route::get('/lobby', [LobbyController::class, 'index'])->name('lobby.index');
    Route::get('/lobby/create', [LobbyController::class, 'create'])->name('lobby.create');

    // --- Lobby Membership Actions & Moderation ---
    Route::post('/lobby/{lobby}/join', [LobbyMembershipController::class, 'join'])->name('lobby.join');
    Route::post('/lobbies/{lobby}/join', [LobbyController::class, 'join'])->name('lobbies.join');
    Route::patch('/membership/{member}/{status}', [LobbyMembershipController::class, 'updateStatus'])->name('lobby.member.status');
    Route::get('/lobby/{lobby}/member/{user}/cv', [LobbyMembershipController::class, 'viewMemberCv'])->name('lobby.member.cv');
        Route::post('/membership/{member}/status/{status}', [LobbyMembershipController::class, 'updateStatus'])
    ->name('membership.update')
    ->middleware(['auth']);


    // Change Route::post to Route::patch
Route::patch('/membership/{member}/status/{status}', [LobbyMembershipController::class, 'updateStatus'])
    ->name('membership.update')
    ->middleware(['auth']);

    
    // --- Channel Provisioning Engine ---
    Route::get('/lobbies/{lobby}/channels/create', [ChannelController::class, 'create'])->name('lobbies.channels.create');
    Route::post('/lobbies/{lobby}/channels', [ChannelController::class, 'store'])->name('lobbies.channels.store');

    // --- CORE WORKSPACE TEXT CHAT INTERFACE ---
    // This maps exactly to ChatController index mapping your workspace layouts
    Route::get('/chat/{lobby}', [ChatController::class, 'index'])->name('chat.index');
    // Ensure it looks exactly like this:
Route::patch('/membership/{member}/{status}', [LobbyMembershipController::class, 'updateStatus'])
    ->middleware('auth');

    // --- MESSAGING ENDPOINTS ---
    Route::post('/channels/{channel}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/channels/{channel}/stream', [ChatController::class, 'stream'])->name('channels.stream')->middleware('auth');

   // Change the name at the end to chat.huddle
Route::get('/chat/channel/{channel}/huddle', function ($channelId) {
    // Added "chat." before huddle
    return view('chat.huddle', ['channelId' => $channelId]); 
})->name('chat.huddle')->middleware(['auth']);

});
