<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use App\Models\Interest;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    /**
     * Display the unified dashboard workflow space.
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        // Ensure user profile record initialization exists
        if (!$user->profile) {
            $user->profile()->create();
        }

        // Radar Scan: Fetch current user's attribute tags
        $userInterestIds = $user->interests()->pluck('interests.id')->toArray();

        $search = $request->input('search');
        $interestId = $request->input('interest');

        // 1. Build Base Filterable Query Stack (ADDED: has('owner') check to prevent null property crashes)
        $baseQuery = Lobby::has('owner')->with([
            'owner', 
            'interests', 
            'members' => function ($query) {
                $query->with('profile');
            }
        ]);

        if ($search) {
            $baseQuery->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('description', 'LIKE', "%{$search}%")
                  ->orWhere('project_goal', 'LIKE', "%{$search}%");
            });
        }

        if ($interestId) {
            $baseQuery->whereHas('interests', function ($q) use ($interestId) {
                $q->where('interests.id', $interestId);
            });
        }

        // 2. Fetch Recommended Campaign Lobbies (Filtered + Interest Score Intersections)
        $recommendedLobbies = (clone $baseQuery)
            ->where('owner_id', '!=', $user->id)
            ->withCount(['interests as matching_score' => function ($query) use ($userInterestIds) {
                $query->whereIn('interests.id', $userInterestIds);
            }])
            ->orderBy('matching_score', 'desc')
            ->orderBy('created_at', 'desc')
            ->take(3)
            ->get()
            ->map(function ($lobby) {
                $percentage = $lobby->matching_score * 20;
                $lobby->match_percentage = min($percentage, 100); 
                return $lobby;
            });

        // 3. Fetch Freshly Discovered Guilds (Global Feed)
        $newestLobbies = (clone $baseQuery)
            ->latest()
            ->take(6)
            ->get();

        // 4. Fetch User's Active Parties & Connected Roster Nodes (FIXED: Added owner and interests to avoid N+1 errors)
        $joinedLobbies = Lobby::has('owner')
            ->whereHas('members', function ($q) use ($user) {
                $q->where('lobby_members.user_id', $user->id)
                  ->where('lobby_members.status', 'accepted'); 
            })
            ->with([
                'owner',
                'interests',
                'members' => function ($query) {
                    $query->with('profile');
                }
            ])
            ->get();

        // 5. Fetch incoming applications using explicit pivot intermediate extraction loaders
        // FIXED: Explicitly bringing along pivot columns so your dashboard view can read `withPivot('id')` to handle accept/deny links
        $ownedLobbiesWithRequests = Lobby::has('owner')
            ->where('owner_id', $user->id)
            ->whereHas('members', function ($query) {
                $query->where('lobby_members.status', 'pending');
            })
            ->with([
                'interests',
                'members' => function ($query) {
                    $query->where('lobby_members.status', 'pending')
                          ->withPivot('id', 'status', 'created_at') // Crucial for getting the exact LobbyMember record key for your approval routes
                          ->with('profile');
                }
            ])
            ->get();

        // Fetch global filter tags for search bar support dropdown menus
        $allInterests = Interest::all();

        // Return unified collection payload to dashboard blade
        return view('dashboard', compact(
            'recommendedLobbies', 
            'newestLobbies', 
            'joinedLobbies', 
            'ownedLobbiesWithRequests',
            'allInterests'
        ));
    }
}