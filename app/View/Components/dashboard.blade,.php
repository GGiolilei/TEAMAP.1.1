<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Startup Matchmaking Hub') }}
        </h2>
    </x-slot>

    <div class="py-12 bg-gray-950 min-h-screen text-gray-100">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-12">
            
            @if(session('success'))
                <div class="p-4 bg-emerald-900/40 border border-emerald-500 text-emerald-200 rounded-lg">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="p-4 bg-rose-900/40 border border-rose-500 text-rose-200 rounded-lg">
                    {{ session('error') }}
                </div>
            @endif

            <div class="bg-gray-900 border border-gray-800 p-6 rounded-xl flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="text-lg font-bold">Your Matchmaking Status</h3>
                    <p class="text-sm text-gray-400">Interests picked: 
                        @foreach(auth()->user()->interests as $interest)
                            <span class="text-emerald-400 font-semibold text-xs bg-emerald-500/10 px-2 py-0.5 rounded">#{{ $interest->name }}</span>
                        @endforeach
                    </p>
                </div>
                <div class="flex items-center gap-4">
                    <span class="text-sm">
                        CV Upload Status: 
                        @if(auth()->user()->profile?->cv_path)
                            <strong class="text-emerald-400">✓ Uploaded & Active</strong>
                        @else
                            <strong class="text-amber-400">✗ No CV Uploaded (Cannot Join Lobbies)</strong>
                        @endif
                    </span>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-gradient-to-br from-gray-900 to-emerald-950/40 p-6 rounded-xl border border-gray-800 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-emerald-400 mb-2">Build Your Own Team</h3>
                        <p class="text-gray-400 text-sm mb-4">Create a brand new startup lobby based around your product goals, choose required skills, and review applicants' CVs.</p>
                    </div>
                    <a href="#" class="inline-block text-center bg-emerald-600 hover:bg-emerald-500 text-white font-medium py-2.5 px-4 rounded-lg transition">
                        + Open a New Lobby
                    </a>
                </div>

                <div class="bg-gray-900 p-6 rounded-xl border border-gray-800 flex flex-col justify-between">
                    <div>
                        <h3 class="text-xl font-bold text-indigo-400 mb-2">Your Joined & Pending Lobbies</h3>
                        <p class="text-gray-400 text-sm mb-4">Track your current memberships, check application review statuses, and jump into real-time team chats.</p>
                    </div>
                    <span class="text-xs text-gray-500 bg-gray-950 p-2 rounded block">Active connections: {{ $joinedLobbies->count() }} total rooms</span>
                </div>
            </div>

            <div>
                <div class="flex items-center gap-3 mb-6">
                    <h2 class="text-2xl font-black text-white tracking-tight">Recommended for You</h2>
                    <span class="bg-emerald-500/10 text-emerald-400 text-xs px-2.5 py-1 rounded-full border border-emerald-500/20 font-bold">Based on your interests</span>
                </div>
                
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($recommendedLobbies as $lobby)
                        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 flex flex-col justify-between hover:border-gray-700 transition">
                            <div>
                                <div class="flex justify-between items-start mb-3">
                                    <h4 class="text-lg font-bold text-white">{{ $lobby->name }}</h4>
                                    <span class="bg-indigo-500/10 text-indigo-400 text-xs px-2 py-0.5 rounded border border-indigo-500/20 font-mono">
                                        Match Score: {{ $lobby->matching_score }}
                                    </span>
                                </div>
                                <p class="text-sm text-gray-400 line-clamp-2 mb-4">{{ $lobby->description }}</p>
                                
                                <div class="flex flex-wrap gap-1.5 mb-4">
                                    @foreach($lobby->interests as $interest)
                                        <span class="bg-gray-950 text-gray-400 text-xs px-2 py-0.5 rounded">#{{ $interest->name }}</span>
                                    @endforeach
                                </div>
                            </div>

                            <div class="pt-4 border-t border-gray-800 flex items-center justify-between mt-4">
                                <span class="text-xs text-gray-500">Goal: <strong class="text-gray-300">{{ $lobby->project_goal }}</strong></span>
                                
                                <form action="{{ route('lobby.join', $lobby->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-gray-800 hover:bg-gray-700 text-gray-200 text-xs font-bold px-3 py-1.5 rounded-lg border border-gray-700 transition">
                                        Apply to Join
                                    </button>
                                </form>
                            </div>
                        </div>
                    @empty
                        <p class="text-gray-500 text-sm">No matched lobbies found right now. Try adding more interests in your Profile setups!</p>
                    @endforelse
                </div>
            </div>

            <div>
                <h2 class="text-2xl font-black text-white tracking-tight mb-6">Discover Newest Lobbies</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @foreach($newestLobbies as $lobby)
                        <div class="bg-gray-900 rounded-xl border border-gray-800 p-6 flex flex-col justify-between">
                            <div>
                                <h4 class="text-lg font-bold text-white mb-2">{{ $lobby->name }}</h4>
                                <p class="text-sm text-gray-400 line-clamp-2 mb-4">{{ $lobby->description }}</p>
                            </div>
                            <div class="pt-4 border-t border-gray-800 flex items-center justify-between">
                                <span class="text-xs text-gray-400">By {{ $lobby->owner->name }}</span>
                                <form action="{{ route('lobby.join', $lobby->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs text-emerald-400 font-semibold hover:underline">
                                        Send Join Request →
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

        </div>
    </div>
</x-app-layout>