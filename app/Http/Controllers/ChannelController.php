<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use App\Models\Channel;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ChannelController extends Controller
{
    public function index(Lobby $lobby)
    {
        // Load relationships to make sure they are available in your blade layout
        $lobby->load(['channels.messages.user', 'members']);

        return view('chat.index', compact('lobby'));
    }
    /**
     * Show the form for creating a new channel.
     */
    public function create(Lobby $lobby)
    {
        // Security Guard: Only the lobby organizer can build new channels
        if (auth()->id() !== $lobby->owner_id) {
            abort(403, 'Only the workspace organizer can create new channels.');
        }

        return view('channel.create', compact('lobby'));
    }

    /**
     * Store a newly created channel in storage.
     */
   public function store(Request $request, Lobby $lobby)
{
    if (auth()->id() !== $lobby->owner_id) {
        abort(403, 'Unauthorized action.');
    }

    // 1. Clean the incoming name input FIRST to remove '#' and force slug format
    $rawName = $request->input('name');
    $cleanedName = Str::slug(str_replace('#', '', $rawName));

    // 2. Merge it back into the request payload so the validator checks the clean string
    $request->merge(['name' => $cleanedName]);

    // 3. Now run the validation safely
    $validated = $request->validate([
        'name' => [
            'required', 
            'string', 
            'max:50',
            'unique:channels,name,NULL,id,lobby_id,' . $lobby->id
        ],
        'description' => 'nullable|string|max:150',
    ], [
        'name.unique' => 'A channel with this name already exists in this workspace.'
    ]);

    $validated['lobby_id'] = $lobby->id;

    // 4. Persist to database
    $lobby->channels()->create($validated);

    return redirect()->route('chat.index', $lobby->id)
        ->with('success', "Channel #{$cleanedName} was created successfully!");
}
}