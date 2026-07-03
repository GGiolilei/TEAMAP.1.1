<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use App\Models\Interest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        // Fetch all available interests for the checkboxes/tags
        $allInterests = Interest::all();
        
        // Ensure user profile exists
        $profile = $request->user()->profile ?? $request->user()->profile()->create();

        return view('profile.edit', [
            'user' => $request->user(),
            'profile' => $profile,
            'allInterests' => $allInterests,
            'userInterests' => $request->user()->interests()->pluck('interests.id')->toArray(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();
        
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'bio' => ['nullable', 'string', 'max:1000'],
            'skills' => ['nullable', 'string', 'max:500'],
            'cv' => ['nullable', 'file', 'mimes:pdf,docx', 'max:4096'], // 4MB max
            'interests' => ['required', 'array', 'min:3', 'max:5'], // Force 3-5 interests
        ]);

        // Update User credentials
        $user->update($request->only('name', 'email'));

        // Update Profile details
        $profileData = $request->only('bio', 'skills');

        // Handle CV file upload safely
        if ($request->hasFile('cv')) {
            $path = $request->file('cv')->store('cvs', 'public');
            $profileData['cv_path'] = $path;
        }

        $user->profile()->updateOrCreate(['user_id' => $user->id], $profileData);

        // Sync interests using the pivot table
        $user->interests()->sync($request->input('interests'));

        return Redirect::route('profile.edit')->with('status', 'profile-updated');
    }

    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();
        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
    public function updateCv(Request $request)
{
    $request->validate([
        'cv' => 'required|file|mimes:pdf,docx|max:5120', // 5MB limit
    ]);

    $user = $request->user();

    // Store the raw file in the secure 'cvs' folder under public storage
    $path = $request->file('cv')->store('cvs', 'public');

    // Update or create the profile record linked to the user
    $user->profile()->updateOrCreate(
        ['user_id' => $user->id],
        ['cv_path' => $path]
    );

    return back()->with('success', 'Your CV file has been securely uploaded and linked.');
}
}