<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LobbyController extends Controller
{
    public function index(Request $request)
{
    $query = Lobby::with(['members']);

    // Handle incoming search query requests
    if ($request->filled('search')) {
        $query->where('name', 'like', '%' . $request->search . '%')
              ->orWhere('project_goal', 'like', '%' . $request->search . '%');
    }

    $lobbies = $query->latest()->get();

    return view('lobbies.index', compact('lobbies'));
}
    /**
     * Show the form for creating a new lobby.
     */
    public function create()
    {
        // Renders your clean, user-friendly blade view
        return view('lobbies.create');
    }

    /**
     * Store a newly created lobby in storage.
     */
    public function store(Request $request)
    {
        // 1. Validate incoming form inputs
        $validated = $request->validate([
            'name'           => 'required|string|max:255',
            'project_goal'   => 'required|string|max:255',
            'description'    => 'required|string',
            'max_members'    => 'required|integer|min:2|max:10',
            'required_roles' => 'nullable|string|max:255',
            'interests'      => 'required|array|min:1', // Ensures at least one tag is selected
            'interests.*'    => 'exists:interests,id',
        ]);

        // 2. Create the lobby and link it to the authenticated user as owner
        $lobby = Lobby::create([
            'owner_id'       => Auth::id(),
            'name'           => $validated['name'],
            'project_goal'   => $validated['project_goal'],
            'description'    => $validated['description'],
            'max_members'    => $validated['max_members'],
            'required_roles' => $validated['required_roles'],
            'status'         => 'active',
        ]);

        // 3. Attach the chosen interest tags to the database pivot table
        $lobby->interests()->attach($request->interests);

        // 4. Redirect cleanly back to the workspace dashboard
        return redirect()->route('dashboard')->with('success', 'Your new project lobby has been launched successfully!');
    }
    /**
 * Attach the authenticated user to the specified lobby workspace roster.
 */
public function join(\App\Models\Lobby $lobby)
{
    // 1. Security Check: Prevent the owner from joining their own lobby redundantly
    if (auth()->id() === $lobby->owner_id) {
        return redirect()->back()->with('error', 'You are already the owner/organizer of this workspace.');
    }

    // 2. Prevent duplicate entries in your pivot table
    if ($lobby->members->contains(auth()->id())) {
        return redirect()->route('chat.index', $lobby->id)
            ->with('success', 'You are already a member of this workspace.');
    }

    // 3. Attach the user using your Many-to-Many relationship mapping
    $lobby->members()->attach(auth()->id());

    // 4. Redirect to the newly opened chat screen with a success flag
    return redirect()->route('chat.index', $lobby->id)
        ->with('success', "You have successfully joined {$lobby->name}!");
}
}