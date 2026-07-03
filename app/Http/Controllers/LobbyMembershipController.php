<?php

namespace App\Http\Controllers;

use App\Models\Lobby;
use App\Models\LobbyMember;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Auth\Access\AuthorizationException;

class LobbyMembershipController extends Controller
{
    public function join(Request $request, Lobby $lobby)
    {
        $user = auth()->user();

        // INVENTORY CHECK: Reject if applicant hasn't dropped their CV Scroll
        if (!$user->profile || !$user->profile->cv_path) {
            return redirect()->back()->with('error', 'Inventory Missing: You must upload your CV scroll in your Profile settings before signing onto a campaign guild!');
        }

        // Check if they have an active or pending application to avoid duplicates
        $alreadyApplied = LobbyMember::where('lobby_id', $lobby->id)
            ->where('user_id', $user->id)
            ->whereIn('status', ['pending', 'accepted'])
            ->exists();

        if ($alreadyApplied) {
            return redirect()->back()->with('error', 'You are already an active member or in queue for this active party.');
        }

        LobbyMember::create([
            'lobby_id' => $lobby->id,
            'user_id' => $user->id,
            'role_in_team' => $request->input('role_in_team', 'Mercenary'),
            'status' => 'pending'
        ]);

        return redirect()->back()->with('success', 'Application transmitted! Your build specs and CV are pending review by the Guild Leader.');
    }

    /**
     * FIXED: Changed second parameter to explicit ID lookup. 
     * Since LobbyMember is an intermediate pivot table model, implicit Route Model Binding 
     * often fails unless custom keys are explicitly bound in RouteServiceProvider.
     */
    public function updateStatus(Request $request, $memberId, $status)
    {
        // Explicitly locate the pivot record row safely
        $member = LobbyMember::findOrFail($memberId);
        
        // Force-load the missing relationship to bypass race conditions
        $member->loadMissing('lobby');

        // Safety fallback check if route mismatch or record deletion occurred
        if (!$member->lobby) {
            abort(404, 'The target project workspace lobby link could not be resolved.');
        }

        // Gatekeeping check: Verify current user is actually the owner
        if (auth()->id() !== $member->lobby->owner_id) {
            abort(403, 'Unauthorized Guild Leader modification action.');
        }

        // Normalize incoming status modifications cleanly
        $cleanStatus = ($status === 'accepted' || $status === 'approved') ? 'accepted' : 'rejected';
        
        $member->update(['status' => $cleanStatus]);

        $message = $cleanStatus === 'accepted' 
            ? "Contract approved! Target operative is now a squad member." 
            : "Application declined and removed from queue.";

        return redirect()->back()->with('success', $message);
    }

    public function viewMemberCv(Lobby $lobby, User $user)
    {
        // FIXED SAFETY GATES: Check if the user is the owner, OR an already APPROVED team member.
        $isOwner = $lobby->owner_id === auth()->id();
        
        $isApprovedTeamMember = $lobby->members()
            ->where('user_id', auth()->id())
            ->where('lobby_members.status', 'accepted')
            ->exists();

        // Also allow the owner to look at someone's CV if they are currently PENDING
        $isPendingApplicant = $lobby->members()
            ->where('user_id', $user->id)
            ->where('lobby_members.status', 'pending')
            ->exists();

        // Check if the user trying to view is authorized
        if (!$isOwner && !$isApprovedTeamMember) {
            abort(403, 'Unauthorized. You must be an approved member of this project workspace to review assets.');
        }

        // If a regular member is trying to spy on another applicant's CV who isn't even approved yet, block them
        if (!$isOwner && !$isPendingApplicant && ($user->id !== auth()->id())) {
             // Only let owners see pending applicants' CVs
             $isApprovedTarget = $lobby->members()
                ->where('user_id', $user->id)
                ->where('lobby_members.status', 'accepted')
                ->exists();
                
             if (!$isApprovedTarget) {
                 abort(403, 'Unauthorized asset lookup configuration.');
             }
        }

        // Grab the file path from the requested user's profile relationship profile
        $cvPath = $user->profile?->cv_path;

        if (!$cvPath || !Storage::disk('public')->exists($cvPath)) {
            abort(404, 'The requested professional background document could not be located on our storage nodes.');
        }

        // Stream the raw asset inside the browser sandboxed window securely instead of auto-downloading it
        return response()->file(storage_path('app/public/' . $cvPath), [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . \Illuminate\Support\Str::snake($user->name) . '_resume.pdf"'
        ]);
    }
}