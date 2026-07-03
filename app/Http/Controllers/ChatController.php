<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use App\Models\Message;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    /**
     * Display the main chat lobby interface.
     */
    public function index(Lobby $lobby)
    {
        // Load relationships to make sure they are available in your blade layout
        $lobby->load(['channels.messages.user', 'members']);

        // Fetch the lobbies the currently authenticated user belongs to
        $joinedLobbies = auth()->user()->lobbies; 

        // Pass both the current lobby and all joined lobbies to the view
        return view('chat.index', compact('lobby', 'joinedLobbies'));
    }

    /**
     * Show the conversation pane for an active workspace party.
     */
    public function show(Lobby $lobby)
    {
        // Guard: Prevent unauthorized users outside the party row from eavesdropping
        if (!$lobby->members->contains(auth()->id()) && $lobby->owner_id !== auth()->id()) {
            return redirect()->route('dashboard')->with('error', 'You must be an approved team member to open this communication feed.');
        }

        $messages = $lobby->messages()->with('user')->oldest()->take(100)->get();
        
        // Fetch the lobbies the currently authenticated user belongs to
        $joinedLobbies = auth()->user()->lobbies;

        // Pass all required variables to the view
        return view('chat.index', compact('lobby', 'messages', 'joinedLobbies'));
    }

    /**
     * Store and push a newly transmitted dialogue message node.
     */
    public function store(Request $request, Lobby $lobby)
    {
        if (!$lobby->members->contains(auth()->id()) && $lobby->owner_id !== auth()->id()) {
            return abort(403);
        }

        $request->validate([
            'content' => 'required|string|max:2000',
        ]);

        $lobby->messages()->create([
            'user_id' => auth()->id(),
            'content' => $request->input('content'),
        ]);

        return redirect()->back();
    }
/**
 * Stream new messages in real-time using Server-Sent Events (SSE).
 */
public function stream(Request $request, $channelId)
{
    return response()->stream(function () use ($channelId, $request) {
        // Set an execution time limit to let the stream persist safely
        set_time_limit(0);

        $lastId = $request->query('lastId', 0);

        // Keep connection open for 30 seconds max per lifecycle connection loop
        $timeout = 30; 
        $start = time();

        while ((time() - $start) < $timeout) {
            // Check if any new message node has been committed since the client's last viewpoint checkpoint
            $newMessages = \App\Models\Message::where('lobby_id', $channelId) // Check if relation matches your schema (e.g., channel_id)
                ->where('id', '>', $lastId)
                ->with('user')
                ->oldest()
                ->get();

            if ($newMessages->count() > 0) {
                foreach ($newMessages as $msg) {
                    echo "data: " . json_encode([
                        'id' => $msg->id,
                        'user_id' => $msg->user_id,
                        'user_name' => $msg->user->name ?? 'Anonymous',
                        'content' => $msg->content,
                        'time' => $msg->created_at->format('g:i A'),
                    ]) . "\n\n";
                    
                    $lastId = $msg->id;
                }
                ob_flush();
                flush();
            }

            // Sleep for 2 seconds before inspecting database nodes again
            sleep(2);
        }
    }, 2000, [
        'Content-Type' => 'text/event-stream',
        'Cache-Control' => 'no-cache',
        'Connection' => 'keep-alive',
    ]);
}}