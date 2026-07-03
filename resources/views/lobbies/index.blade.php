<x-app-layout>
    <div class="py-10 bg-slate-950 min-h-screen text-slate-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 space-y-8">

            <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-800 pb-6">
                <div>
                    <h1 class="text-2xl font-black tracking-tight text-slate-100 sm:text-3xl">Project Lobbies</h1>
                    <p class="text-sm text-slate-400 mt-1">Discover, join, or manage custom collaboration workspaces.</p>
                </div>
                <a href="{{ route('lobbies.create') }}" class="inline-flex items-center justify-center bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm px-5 py-2.5 rounded-xl transition duration-200 shadow-md shadow-indigo-950/50 shrink-0 gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                    </svg>
                    Create New Lobby
                </a>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-5 rounded-2xl shadow-xl">
                <form method="GET" action="{{ route('lobby.index') }}" class="space-y-4">
                    <div class="relative flex items-center">
                        <div class="absolute left-4 pointer-events-none text-slate-500">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               placeholder="Search all open lobbies by name, milestone metrics, or description keywords..." 
                               class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring focus:ring-indigo-500/10 text-slate-200 placeholder-slate-500 text-sm pl-12 pr-24 py-3 rounded-xl shadow-inner transition" />
                        
                        <div class="absolute right-2.5">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs px-4 py-2 rounded-lg transition duration-150 shadow">
                                Search
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <div>
                <div class="flex items-center gap-3 mb-6">
                    <h3 class="text-base font-bold text-slate-200 tracking-tight">Available Openings</h3>
                    <div class="h-[1px] flex-1 bg-gradient-to-r from-slate-800 to-transparent"></div>
                    <span class="text-xs text-slate-500 font-mono">Showing {{ $lobbies->count() }} Writable Hubs</span>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    @forelse($lobbies as $lobby)
                        <div class="bg-slate-900 border border-slate-800/80 rounded-2xl p-6 flex flex-col justify-between hover:border-slate-700/80 transition duration-300 shadow-lg group">
                            
                            <div>
                                <div class="flex items-start justify-between gap-4 mb-3">
                                    <h4 class="text-base font-bold text-slate-100 group-hover:text-indigo-400 transition truncate" title="{{ $lobby->name }}">
                                        {{ $lobby->name }}
                                    </h4>
                                    
                                    @if($lobby->owner_id === auth()->id())
                                        <span class="bg-amber-500/10 text-amber-400 border border-amber-500/20 text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">
                                            Owner
                                        </span>
                                    @elseif($lobby->members->contains(auth()->id()))
                                        <span class="bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 text-[10px] font-bold px-2 py-0.5 rounded-md uppercase tracking-wider">
                                            Joined
                                        </span>
                                    @endif
                                </div>
                                
                                <p class="text-slate-400 text-sm line-clamp-3 leading-relaxed mb-4">
                                    {{ $lobby->description ?? 'No description provided for this specific configuration space.' }}
                                </p>

                                <div class="mb-4 bg-slate-950/50 p-3 rounded-xl border border-slate-800/50 text-xs">
                                    <span class="text-slate-500 block text-[10px] uppercase font-bold tracking-wider mb-0.5">Project Goal:</span>
                                    <p class="text-slate-300 font-medium line-clamp-1">{{ $lobby->project_goal }}</p>
                                </div>
                            </div>

                            <div class="pt-4 border-t border-slate-800/60 flex items-center justify-between text-xs gap-4">
                                <div class="flex items-center gap-1.5 text-slate-500">
                                    <svg class="w-4 h-4 text-slate-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.5l-8-8 8-8"/>
                                    </svg>
                                    <span class="font-medium text-slate-400">{{ $lobby->members->count() }}</span> members
                                </div>

                                @if($lobby->owner_id === auth()->id() || $lobby->members->contains(auth()->id()))
                                    <a href="{{ route('chat.index', $lobby->id) }}" class="inline-flex items-center gap-1 bg-indigo-600/10 hover:bg-indigo-600 border border-indigo-500/20 hover:border-indigo-500 text-indigo-400 hover:text-white font-bold px-3 py-2 rounded-lg transition shadow-sm">
                                        Open Chat →
                                    </a>
                                @else
                                    <form action="{{ route('lobby.join', $lobby->id) }}" method="POST" class="m-0">
                                        @csrf
                                        <button type="submit" class="bg-slate-950 hover:bg-slate-850 border border-slate-800 hover:border-indigo-500 text-slate-300 hover:text-indigo-400 font-bold px-3 py-2 rounded-lg transition shadow-sm">
                                            Request to Join
                                        </button>
                                    </form>
                                @endif
                            </div>

                        </div>
                    @empty
                        <div class="col-span-full p-12 bg-slate-900/40 border border-dashed border-slate-800 rounded-2xl text-center text-slate-500 text-sm shadow-inner">
                            No open collaboration lobbies found matching those filters. Try clearing your search parameters.
                        </div>
                    @endforelse
                </div>
            </div>

        </div>
    </div>
</x-app-layout>