<x-app-layout>

{{-- GLOBAL NOTIFICATION SOUND --}}
<audio id="chatNotificationSound" src="{{ asset('sounds/notification.mp3') }}" preload="auto"></audio>

<script>
function playNotificationSound() {
    const sound = document.getElementById('chatNotificationSound');
    if (!sound) return;
    sound.currentTime = 0;
    sound.play().catch(() => {});
}
</script>

<div class="py-8 bg-slate-950 min-h-screen text-slate-100">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">

        {{-- SUCCESS / ERROR TOAST --}}
        @if(session('success'))
            <div class="p-3 rounded-xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="p-3 rounded-xl bg-rose-500/10 border border-rose-500/20 text-rose-400 text-sm">
                {{ session('error') }}
            </div>
        @endif


        {{-- PROFILE STATUS STRIP --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col md:flex-row md:items-center md:justify-between gap-3">
            {{-- Interests --}}
            <div class="flex flex-wrap gap-2 items-center">
                <span class="text-xs text-slate-500">Focus:</span>
                @forelse(auth()->user()->interests as $interest)
                    <span class="px-2 py-1 text-xs rounded-full bg-indigo-500/10 border border-indigo-500/20 text-indigo-400">
                        {{ $interest->name }}
                    </span>
                @empty
                    <span class="text-xs text-slate-500">No interests selected</span>
                @endforelse
            </div>

            {{-- CV + Settings --}}
            <div class="flex items-center gap-2">
                <span class="px-3 py-1 text-xs rounded-full border border-slate-800 bg-slate-950 text-slate-400">
                    CV:
                    <span class="{{ auth()->user()->profile?->cv_path ? 'text-emerald-400' : 'text-amber-400' }}">
                        {{ auth()->user()->profile?->cv_path ? 'Ready' : 'Missing' }}
                    </span>
                </span>

                <a href="{{ route('profile.edit') }}"
                   class="px-3 py-1 text-xs rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 transition">
                    Edit Profile
                </a>
            </div>
        </div>


        {{-- QUICK ACTION BAR --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-3 flex flex-wrap items-center justify-between gap-2">
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('lobby.create') }}"
                   class="px-3 py-1.5 text-xs rounded-lg bg-indigo-600 hover:bg-indigo-500 transition font-semibold">
                    + New Lobby
                </a>

                <a href="{{ route('lobbies.index') }}"
                   class="px-3 py-1.5 text-xs rounded-lg bg-slate-950 border border-slate-800 hover:border-indigo-500/30 text-slate-300">
                    Explore
                </a>

                @if($joinedLobbies->count() > 0)
                    <a href="{{ route('chat.index', $joinedLobbies->first()->id) }}"
                       class="px-3 py-1.5 text-xs rounded-lg bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 hover:bg-indigo-600 hover:text-white transition">
                        Open Chat
                    </a>
                @endif
            </div>

            <div class="flex gap-2 text-xs">
                <span class="px-3 py-1 rounded-full bg-slate-950 border border-slate-800 text-slate-400">
                    Joined <span class="text-indigo-400 font-bold">{{ $joinedLobbies->count() }}</span>
                </span>
            </div>
        </div>


        {{-- JOIN REQUESTS --}}
        @if(isset($ownedLobbiesWithRequests) && $ownedLobbiesWithRequests->count())
        <div class="bg-slate-900 border border-indigo-500/20 rounded-2xl p-5 space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="text-sm font-semibold">Incoming Requests</h3>
                <span class="text-xs text-indigo-400">Action Needed</span>
            </div>

            <div class="space-y-3 max-h-72 overflow-y-auto">
                @foreach($ownedLobbiesWithRequests as $lobby)
                    @foreach($lobby->members as $applicant)
                        <div class="flex items-center justify-between p-3 bg-slate-950 border border-slate-800 rounded-xl">
                            <div class="min-w-0">
                                <p class="text-xs text-slate-500 truncate">
                                    Target Lobby: <span class="text-indigo-400 font-medium">{{ $lobby->name }}</span>
                                </p>
                                <p class="text-sm font-semibold text-slate-100 mt-0.5">
                                    {{ $applicant->name }}
                                </p>
                            </div>

                            <div class="flex items-center gap-2">
                                @if($applicant->profile?->cv_path)
                                    <a href="{{ route('lobby.member.cv', [$lobby->id, $applicant->id]) }}"
                                       target="_blank"
                                       class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-slate-700 border border-slate-700 transition">
                                        CV
                                    </a>
                                @endif

                                {{-- FIXED: References $applicant->pivot->id inside a POST wrapper safely --}}
                                <form action="{{ route('membership.update', ['member' => $applicant->pivot->id, 'status' => 'accepted']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold px-3 py-1.5 rounded-lg text-xs transition">
                                        Approve
                                    </button>
                                </form>

                                {{-- FIXED: Standardized into POST layout matching your updateStatus engine endpoints --}}
                                <form action="{{ route('membership.update', ['member' => $applicant->pivot->id, 'status' => 'rejected']) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="text-xs px-3 py-1.5 rounded-lg bg-slate-800 hover:bg-rose-900/40 border border-slate-700 hover:border-rose-900/60 transition">
                                        Reject
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                @endforeach
            </div>
        </div>
        @endif


        {{-- SEARCH --}}
        <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4">
            <form method="GET" action="{{ route('dashboard') }}" class="space-y-3">
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Search lobbies..."
                       class="w-full bg-slate-950 border border-slate-800 rounded-xl px-4 py-2 text-sm focus:border-indigo-500/50 focus:ring-0 text-slate-200">

                <div class="flex flex-wrap gap-2 text-xs">
                    <a href="{{ route('dashboard') }}"
                       class="px-3 py-1 rounded-lg border {{ !request('interest') ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30' : 'border-slate-800 text-slate-400' }}">
                        All
                    </a>

                    @foreach(\App\Models\Interest::take(5)->get() as $tag)
                        <a href="{{ route('dashboard', ['interest' => $tag->id]) }}"
                           class="px-3 py-1 rounded-lg border {{ request('interest') == $tag->id ? 'bg-indigo-500/10 text-indigo-400 border-indigo-500/30' : 'border-slate-800 text-slate-400' }}">
                            #{{ $tag->name }}
                        </a>
                    @endforeach
                </div>
            </form>
        </div>


        {{-- ACTIVE LOBBIES --}}
        @if($joinedLobbies->count())
        <div class="space-y-3">
            <h3 class="text-sm text-slate-300 font-semibold">Active Lobbies</h3>

            <div class="grid md:grid-cols-2 gap-4">
                @foreach($joinedLobbies as $lobby)
                <div class="bg-slate-900 border border-slate-800 rounded-2xl p-4 flex flex-col justify-between gap-3">
                    <div class="flex justify-between items-start">
                        <div class="min-w-0">
                            <p class="font-semibold text-sm text-slate-100 truncate">{{ $lobby->name }}</p>
                            <p class="text-xs text-slate-500 truncate mt-0.5">{{ $lobby->project_goal }}</p>
                        </div>

                        <a href="{{ route('chat.index', $lobby->id) }}"
                           class="text-xs px-3 py-1 rounded-lg bg-indigo-600/20 text-indigo-400 hover:bg-indigo-600 hover:text-white transition font-medium">
                            Chat
                        </a>
                    </div>

                    <p class="text-xs text-slate-500">
                        {{ $lobby->members->count() }} members
                    </p>
                </div>
                @endforeach
            </div>
        </div>
        @endif


        {{-- RECOMMENDED (CLEAN + INTERACTIVE) --}}
        <div class="space-y-3">
            <div class="flex items-center gap-3">
                <h3 class="text-sm font-bold text-slate-200">Recommended For You</h3>
                <div class="h-[1px] flex-1 bg-gradient-to-r from-slate-800 to-transparent"></div>
                <span class="text-[10px] font-semibold text-indigo-400 bg-indigo-500/5 border border-indigo-500/10 px-2.5 py-0.5 rounded-full">
                    Smart Match
                </span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @forelse($recommendedLobbies as $lobby)
                <div class="group relative flex flex-col justify-between p-5 rounded-xl border border-slate-800 bg-slate-900/40
                            transition-all duration-300 ease-out
                            hover:border-indigo-500/30 hover:bg-slate-900
                            hover:-translate-y-1 hover:rotate-[0.3deg] hover:shadow-xl hover:shadow-indigo-500/10
                            active:scale-[0.99]">

                    {{-- TOP --}}
                    <div>
                        <div class="flex justify-between items-start gap-3 mb-2">
                            <h4 class="text-sm font-bold text-slate-100 group-hover:text-indigo-400 transition truncate">
                                {{ $lobby->name }}
                            </h4>

                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-md
                                         bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 shrink-0">
                                {{ $lobby->match_percentage ?? 0 }}% match
                            </span>
                        </div>

                        <p class="text-xs text-slate-400 line-clamp-2 leading-relaxed mb-3">
                            {{ $lobby->description }}
                        </p>

                        {{-- TAGS --}}
                        <div class="flex flex-wrap gap-1 mb-4">
                            @foreach($lobby->interests as $interest)
                                <span class="text-[10px] px-2 py-0.5 rounded bg-slate-950 border border-slate-800 text-slate-500">
                                    #{{ $interest->name }}
                                </span>
                            @endforeach
                        </div>
                    </div>

                    {{-- BOTTOM CONTAINER WITH CONDITIONAL ENTRY ACTIONS --}}
                    <div class="pt-3 border-t border-slate-800/60 flex items-center justify-between gap-2">
                        <span class="text-[11px] text-slate-500 truncate">
                            Goal: <span class="text-slate-300">{{ $lobby->project_goal }}</span>
                        </span>

                        @if(Auth::id() === $lobby->owner_id)
                            <span class="text-[10px] font-bold text-indigo-400 bg-indigo-500/10 border border-indigo-500/20 px-2.5 py-1 rounded-md shrink-0">
                                Your Lobby
                            </span>
                        @else
                            @php
                                $userApplication = \App\Models\LobbyMember::where('lobby_id', $lobby->id)
                                    ->where('user_id', Auth::id())
                                    ->first();
                            @endphp

                            @if(!$userApplication)
                                <form method="POST" action="{{ route('lobbies.join', $lobby->id) }}" class="shrink-0">
                                    @csrf
                                    <button class="text-[11px] px-3 py-1.5 rounded-lg font-semibold
                                                   bg-slate-950 border border-slate-800 text-indigo-400
                                                   hover:bg-indigo-600 hover:text-white hover:border-indigo-500
                                                   transition-all duration-200">
                                        Join
                                    </button>
                                </form>
                            @elseif($userApplication->status === 'pending')
                                <span class="text-[10px] font-bold text-amber-400 bg-amber-500/10 border border-amber-500/20 px-2.5 py-1 rounded-md shrink-0">
                                    ⏳ Pending Review
                                </span>
                            @elseif($userApplication->status === 'accepted')
                                <a href="{{ route('chat.index', $lobby->id) }}"
                                   class="text-[11px] px-3 py-1.5 rounded-lg font-semibold bg-emerald-600 hover:bg-emerald-500 text-white transition-all duration-200 shrink-0">
                                    Enter Chat →
                                </a>
                            @endif
                        @endif
                    </div>

                    {{-- Subtle card hover glow effect --}}
                    <div class="absolute inset-0 rounded-xl opacity-0 group-hover:opacity-100 transition duration-300
                                bg-gradient-to-tr from-indigo-500/5 to-transparent pointer-events-none"></div>
                </div>
                @empty
                <div class="col-span-full text-center p-8 text-sm text-slate-500 border border-slate-800 rounded-xl bg-slate-900/30">
                    No recommendations available right now.
                </div>
                @endforelse
            </div>
        </div>

    </div>
</div>

</x-app-layout>