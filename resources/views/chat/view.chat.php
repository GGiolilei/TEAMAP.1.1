<x-app-layout>
    {{-- Fetch the currently highlighted channel from the URL query string, fallback to the first channel --}}
    @php
        $activeChannelId = request('channel', $lobby->channels->first()?->id);
        $currentChannel = $lobby->channels->firstWhere('id', $activeChannelId) ?? $lobby->channels->first();
    @endphp

    <style>
        @keyframes bounce-slow {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
        .animate-bounce-slow-1 { animation: bounce-slow 1s infinite 0.1s; }
        .animate-bounce-slow-2 { animation: bounce-slow 1s infinite 0.2s; }
        .animate-bounce-slow-3 { animation: bounce-slow 1s infinite 0.3s; }
        
        @keyframes slide-in-up {
            0% { opacity: 0; transform: translateY(12px); }
            100% { opacity: 1; transform: translateY(0); }
        }
        .animate-slide-up { animation: slide-in-up 0.25s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    </style>

    <div class="bg-slate-950 min-h-screen text-slate-100 flex flex-col lg:flex-row">
        
        <div class="w-full lg:w-80 bg-slate-900 border-b lg:border-b-0 lg:border-r border-slate-800 p-6 flex flex-col justify-between shrink-0 overflow-y-auto lg:h-[calc(100vh-65px)]">
            <div class="space-y-6">
                <div class="flex items-center justify-between">
                    <a href="{{ route('dashboard') }}" class="px-3 py-1.5 bg-slate-950 hover:bg-slate-800 border border-slate-800 text-slate-400 hover:text-white rounded-xl transition-all duration-200 text-xs font-semibold flex items-center gap-1.5">
                        ← Dashboard
                    </a>
                    <span class="bg-indigo-500/10 text-indigo-400 border border-indigo-500/20 text-[10px] uppercase font-bold tracking-wider px-2 py-0.5 rounded-md">
                        Active Room
                    </span>
                </div>

                <div>
                    <h2 class="text-lg font-black text-slate-100 tracking-tight">{{ $lobby->name }}</h2>
                    <p class="text-[11px] text-slate-400 mt-1 leading-relaxed">
                        Goal: <span class="text-slate-300 font-medium">{{ $lobby->project_goal }}</span>
                    </p>
                </div>

                <div class="h-[1px] bg-slate-800/60"></div>

                <div>
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider">
                            Text Channels
                        </h3>
                        @if(auth()->id() === $lobby->owner_id)
                            <a href="{{ route('lobbies.channels.create', $lobby->id) }}" class="p-1 hover:bg-slate-800 rounded text-slate-400 hover:text-indigo-400 transition-colors duration-150" title="Create New Channel">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
                                </svg>
                            </a>
                        @endif
                    </div>
                    
                    <div class="space-y-1">
                        @forelse($lobby->channels as $roomChannel)
                            @php $isActive = $currentChannel?->id === $roomChannel->id; @endphp
                            <a href="{{ route('chat.index', ['lobby' => $lobby->id, 'channel' => $roomChannel->id]) }}" 
                               class="flex items-center gap-2 px-3 py-2 rounded-xl transition-all duration-200 text-xs {{ $isActive ? 'bg-indigo-600/10 text-indigo-400 border border-indigo-500/20 font-bold' : 'text-slate-400 hover:text-slate-200 hover:bg-slate-950/60 border border-transparent hover:border-slate-800/40 font-medium' }}">
                                <span class="{{ $isActive ? 'text-indigo-500' : 'text-slate-600' }} font-mono text-sm">#</span> 
                                {{ $roomChannel->name }}
                            </a>
                        @empty
                            <div class="text-[11px] text-slate-600 p-3 bg-slate-950/30 rounded-xl text-center border border-dashed border-slate-800">
                                No active channels found.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="h-[1px] bg-slate-800/60"></div>

                <div>
                    <h3 class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-3">
                        Workspace Roster (<span id="roster-count">{{ $lobby->members->count() }}</span>)
                    </h3>
                    <div id="roster-container" class="space-y-2 max-h-48 overflow-y-auto pr-1">
                        @foreach($lobby->members as $member)
                            <div data-user-id="{{ $member->id }}" class="flex items-center justify-between p-2 bg-slate-950/40 border border-slate-800/40 rounded-xl transition-all duration-200">
                                <div class="flex items-center gap-2 truncate">
                                    <div class="w-6 h-6 rounded-md bg-indigo-500/10 border border-indigo-500/20 text-indigo-400 flex items-center justify-center font-bold text-[10px] uppercase shrink-0">
                                        {{ substr($member->name, 0, 2) }}
                                    </div>
                                    <div class="truncate">
                                        <h5 class="text-xs font-bold text-slate-200 truncate">{{ $member->name }}</h5>
                                        <p class="text-[9px] text-slate-500 uppercase tracking-wide">{{ $member->id === $lobby->owner_id ? 'Organizer' : 'Partner' }}</p>
                                    </div>
                                </div>
                                {{-- Default status indicator to offline, updated via Echo presence channels if needed --}}
                                <span class="status-dot w-1.5 h-1.5 rounded-full bg-slate-600 shrink-0 shadow-sm transition-colors duration-300"></span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="pt-6 border-t border-slate-800/60 mt-6 lg:mt-0 text-xs text-slate-500 text-center font-mono">
                Lobby Reference: #{{ $lobby->id }}
            </div>
        </div>

        <div class="flex-1 flex flex-col min-h-[500px] lg:h-[calc(100vh-65px)] bg-slate-950">
            
            @if($currentChannel)
                <div class="px-6 py-4 bg-slate-900/40 border-b border-slate-800 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-600/10 border border-indigo-500/20 text-indigo-400 rounded-xl">
                            <span class="font-mono font-bold text-sm">#</span>
                        </div>
                        <div>
                            <h3 class="text-sm font-bold text-slate-100">{{ $currentChannel->name }}</h3>
                            <p class="text-[11px] text-slate-500">{{ $currentChannel->description ?? 'Secure team sync for verified partners' }}</p>
                        </div>
                    </div>
                </div>

                <div id="chat-timeline" class="flex-1 overflow-y-auto p-6 space-y-4 shadow-inner scroll-smooth">
                    <div class="flex items-center justify-center my-2">
                        <div class="bg-indigo-950/30 border border-indigo-900/40 px-3 py-1 rounded-full text-[10px] text-indigo-400 font-mono tracking-wide">
                            🛡️ Channel stream #{{ $currentChannel->name }} initialized securely
                        </div>
                    </div>

                    @forelse($currentChannel->messages ?? [] as $message)
                        @php $isMe = $message->user_id === auth()->id(); @endphp
                        
                        <div class="flex items-start gap-3 max-w-[85%] {{ $isMe ? 'ml-auto flex-row-reverse' : '' }} animate-slide-up">
                            <div class="w-8 h-8 rounded-lg text-white flex items-center justify-center font-bold text-xs uppercase shrink-0 select-none {{ $isMe ? 'bg-indigo-600 border border-indigo-500' : 'bg-slate-800 border border-slate-700 text-slate-300' }}">
                                {{ substr($message->user->name ?? '?', 0, 2) }}
                            </div>
                            <div class="{{ $isMe ? 'text-right' : '' }}">
                                <div class="flex items-baseline gap-2 {{ $isMe ? 'flex-row-reverse' : '' }}">
                                    <span class="text-xs font-bold text-slate-200">{{ $isMe ? 'You' : ($message->user->name ?? 'Anonymous') }}</span>
                                    <span class="text-[9px] text-slate-500">{{ $message->created_at->format('g:i A') }}</span>
                                </div>
                                <div class="mt-1 px-4 py-2.5 rounded-2xl text-sm transition-all duration-200 transform hover:scale-[1.01] shadow-sm cursor-default select-none {{ $isMe ? 'bg-indigo-600/10 border border-indigo-500/20 text-indigo-300 rounded-tr-none text-left hover:bg-indigo-600/20 hover:border-indigo-400/40' : 'bg-slate-900 border border-slate-800/80 text-slate-300 rounded-tl-none hover:border-slate-700/60 hover:bg-slate-900/80' }}">
                                    {{ $message->content }}
                                </div>
                            </div>
                        </div>
                    @empty
                        <div id="empty-history-notice" class="text-center py-12 text-slate-600 text-xs font-mono">
                            #{{ $currentChannel->name }} is entirely quiet. Type a message down below to start history!
                        </div>
                    @endforelse
                </div>

                <div id="typing-container" class="px-6 py-1 hidden">
                    <div class="flex items-center gap-2 text-slate-500 bg-slate-900/30 border border-slate-900 w-fit px-3 py-1.5 rounded-full shadow-inner animate-slide-up">
                        <div class="flex items-center gap-1">
                            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-bounce-slow-1"></span>
                            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-bounce-slow-2"></span>
                            <span class="w-1.5 h-1.5 bg-indigo-500 rounded-full animate-bounce-slow-3"></span>
                        </div>
                        <span id="typing-text" class="text-[10px] font-mono tracking-wide text-slate-400">You are typing...</span>
                    </div>
                </div>

                <div class="p-4 bg-slate-900/40 border-t border-slate-800/80">
                    <form id="chat-form" action="{{ route('messages.store', ['channel' => $currentChannel->id]) }}" method="POST" class="flex items-center gap-2 relative">
                        @csrf
                        <input id="message-input" name="content" type="text" autocomplete="off" required placeholder="Message #{{ $currentChannel->name }}..." 
                               class="w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring focus:ring-indigo-500/10 text-slate-200 text-sm pl-4 pr-16 py-3 rounded-xl transition-all duration-200" />
                        <div class="absolute right-2">
                            <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-bold p-2 rounded-lg transition-all duration-200 active:scale-95 shadow-md">
                                <svg class="w-4 h-4 transform rotate-90" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19l9-7-9-7v14z"/>
                                </svg>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="flex-1 flex flex-col items-center justify-center text-slate-500 text-sm p-6">
                    <svg class="w-12 h-12 text-slate-700 mb-3" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z"/>
                    </svg>
                    <span>This workspace has no channels yet. Click the <strong class="text-indigo-400">+</strong> icon to get started.</span>
                </div>
            @endif

        </div>

    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const currentUserId = {{ auth()->id() }};
            const currentChannelId = {{ $currentChannel?->id ?? 'null' }};
            const chatForm = document.getElementById('chat-form');
            const messageInput = document.getElementById('message-input');
            const typingContainer = document.getElementById('typing-container');
            const typingText = document.getElementById('typing-text');
            const chatTimeline = document.getElementById('chat-timeline');
            const emptyNotice = document.getElementById('empty-history-notice');

            if (!chatTimeline) return;

            // Helper to auto-scroll chat window
            const scrollToBottom = () => {
                chatTimeline.scrollTop = chatTimeline.scrollHeight;
            };
            scrollToBottom();

            // 1. AJAX Form submission prevents layout jarring page refreshes
            if (chatForm) {
                chatForm.addEventListener('submit', (e) => {
                    e.preventDefault();
                    const content = messageInput.value.trim();
                    if (!content) return;

                    const formData = new FormData(chatForm);
                    messageInput.value = ''; // Instantly clear input for snappy UX
                    
                    // Fire backend event notification that client stopped typing
                    if (window.Echo && currentChannelId) {
                        window.Echo.private(`chat.${currentChannelId}`).whisper('typing', { typing: false });
                    }

                    fetch(chatForm.action, {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(res => res.json())
                    .then(data => {
                        // Optional: Append instantly if broadcast loop doesn't echo back to sender
                    })
                    .catch(err => console.error("Message delivery failed:", err));
                });
            }

            // 2. Real-time Listeners via Laravel Echo (WebSockets / Reverb)
            if (window.Echo && currentChannelId) {
                
                // Track typing indicators across clients
                let typingTimeout;
                messageInput.addEventListener('input', () => {
                    window.Echo.private(`chat.${currentChannelId}`).whisper('typing', {
                        name: "{{ auth()->user()->name }}",
                        typing: messageInput.value.trim().length > 0
                    });

                    clearTimeout(typingTimeout);
                    typingTimeout = setTimeout(() => {
                        window.Echo.private(`chat.${currentChannelId}`).whisper('typing', { typing: false });
                    }, 3000);
                });

                // Listen to incoming messages & whisper triggers
                window.Echo.private(`chat.${currentChannelId}`)
                    .listen('MessageSent', (e) => {
                        if (emptyNotice) emptyNotice.remove();

                        const isMe = e.message.user_id === currentUserId;
                        const formattedTime = new Date(e.message.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
                        const userInitial = (e.user?.name || '?').substring(0, 2);

                        const msgHtml = `
                            <div class="flex items-start gap-3 max-w-[85%] ${isMe ? 'ml-auto flex-row-reverse' : ''} animate-slide-up">
                                <div class="w-8 h-8 rounded-lg text-white flex items-center justify-center font-bold text-xs uppercase shrink-0 select-none ${isMe ? 'bg-indigo-600 border border-indigo-500' : 'bg-slate-800 border border-slate-700 text-slate-300'}">
                                    ${userInitial}
                                </div>
                                <div class="${isMe ? 'text-right' : ''}">
                                    <div class="flex items-baseline gap-2 ${isMe ? 'flex-row-reverse' : ''}">
                                        <span class="text-xs font-bold text-slate-200">${isMe ? 'You' : (e.user?.name || 'Anonymous')}</span>
                                        <span class="text-[9px] text-slate-500">${formattedTime}</span>
                                    </div>
                                    <div class="mt-1 px-4 py-2.5 rounded-2xl text-sm transition-all duration-200 transform hover:scale-[1.01] shadow-sm cursor-default select-none ${isMe ? 'bg-indigo-600/10 border border-indigo-500/20 text-indigo-300 rounded-tr-none text-left hover:bg-indigo-600/20 hover:border-indigo-400/40' : 'bg-slate-900 border border-slate-800/80 text-slate-300 rounded-tl-none hover:border-slate-700/60 hover:bg-slate-900/80'}">
                                        ${e.message.content}
                                    </div>
                                </div>
                            </div>
                        `;
                        
                        chatTimeline.insertAdjacentHTML('beforeend', msgHtml);
                        scrollToBottom();
                    })
                    .listenForWhisper('typing', (e) => {
                        if (e.typing) {
                            typingText.textContent = `${e.name} is typing...`;
                            typingContainer.classList.remove('hidden');
                            scrollToBottom();
                        } else {
                            typingContainer.classList.add('hidden');
                        }
                    });

                // Optional: Connect Presence to Lobby for Active Roster Lights
                const lobbyId = {{ $lobby->id }};
                window.Echo.join(`lobby.${lobbyId}`)
                    .here((users) => {
                        users.forEach(u => updateRosterStatus(u.id, true));
                    })
                    .joining((user) => {
                        updateRosterStatus(user.id, true);
                    })
                    .leaving((user) => {
                        updateRosterStatus(user.id, false);
                    });
            }

            function updateRosterStatus(userId, isOnline) {
                const userRow = document.querySelector(`[data-user-id="${userId}"]`);
                if (userRow) {
                    const dot = userRow.querySelector('.status-dot');
                    if (isOnline) {
                        dot.className = "status-dot w-1.5 h-1.5 rounded-full bg-emerald-500 shrink-0 shadow-sm shadow-emerald-500/50 transition-colors duration-300";
                    } else {
                        dot.className = "status-dot w-1.5 h-1.5 rounded-full bg-slate-600 shrink-0 shadow-sm transition-colors duration-300";
                    }
                }
            }
        });
    </script>
</x-app-layout>