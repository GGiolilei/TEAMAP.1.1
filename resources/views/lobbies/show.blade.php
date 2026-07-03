<div class="bg-slate-900 border border-slate-800 rounded-2xl p-6 shadow-xl max-w-md">
    <div class="flex items-center justify-between mb-4 pb-3 border-b border-slate-800">
        <h3 class="text-sm font-bold tracking-wider text-indigo-400 uppercase">Team Candidates & Members</h3>
        <span class="text-xs text-slate-500 font-medium font-mono">{{ $lobby->members->count() }} / {{ $lobby->max_members }} Slots</span>
    </div>

    <div class="space-y-3.5">
        @foreach($lobby->members as $member)
            <div class="flex items-center justify-between p-3 bg-slate-950/60 border border-slate-800/80 rounded-xl hover:border-slate-700 transition">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-xs uppercase tracking-wider">
                        {{ substr($member->name, 0, 2) }}
                    </div>
                    <div>
                        <h5 class="text-xs font-bold text-slate-200">{{ $member->name }}</h5>
                        <p class="text-[11px] text-slate-500 capitalize">{{ $member->pivot->role ?? 'Applicant' }}</p>
                    </div>
                </div>

                <div>
                    @if($member->profile?->cv_path)
                        <a href="{{ route('lobby.member.cv', [$lobby->id, $member->id]) }}" target="_blank" 
                           class="text-[11px] font-semibold text-emerald-400 hover:text-emerald-300 bg-emerald-500/5 hover:bg-emerald-500/10 border border-emerald-500/10 hover:border-emerald-500/20 px-2.5 py-1.5 rounded-lg transition flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Review CV
                        </a>
                    @else
                        <span class="text-[10px] font-medium text-slate-500 bg-slate-900 border border-slate-800/60 px-2 py-1.5 rounded-lg select-none">
                            No Document
                        </span>
                    @endif
                </div>
            </div>
        @endforeach
    </div>
</div>