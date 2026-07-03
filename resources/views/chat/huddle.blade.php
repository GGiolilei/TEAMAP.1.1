<x-app-layout>
    <div class="bg-slate-950 min-h-screen text-stone-100 flex flex-col items-center justify-center p-4">

        {{-- Hidden Waiting Audio Track --}}
        <audio id="huddle-audio" src="{{ asset('sounds/Huddle.mp3') }}" preload="auto"></audio>

        <div class="w-full max-w-5xl bg-slate-900 border border-slate-800 rounded-2xl shadow-2xl overflow-hidden flex flex-col h-[80vh]">

            {{-- Header --}}
            <div class="px-6 py-4 bg-slate-900/50 border-b border-slate-800 flex items-center justify-between">
                <div>
                    <h2 class="text-sm font-bold text-white flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        Live Channel Huddle
                    </h2>
                    <p class="text-xs text-stone-400 mt-0.5">
                        Channel ID:
                        <span class="text-indigo-400 font-mono">#{{ $channelId }}</span>
                    </p>
                </div>

                <a href="{{ url()->previous() }}"
                   class="px-4 py-2 bg-slate-800 hover:bg-slate-700 text-stone-300 text-xs font-bold rounded-xl transition-all">
                    ← Back to Chat
                </a>
            </div>

            {{-- Video Area --}}
            <div class="flex-1 bg-slate-950 relative">
                <div id="jitsi-container" class="w-full h-full"></div>

                {{-- Waiting Overlay --}}
                <div id="waiting-status"
                     class="hidden absolute inset-0 flex items-center justify-center bg-slate-950/40 backdrop-blur-sm">
                    <div class="bg-slate-900/90 border border-slate-800 px-4 py-2.5 rounded-xl flex items-center gap-3 shadow-lg">
                        <div class="w-4 h-4 border-2 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
                        <span class="text-xs font-medium text-stone-300">
                            Waiting for others to join...
                        </span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://meet.jit.si/external_api.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const domain = "meet.jit.si";
            const channelId = @json($channelId);
            const userName = @json(auth()->user()->name ?? 'Team Member');

            const audio = document.getElementById('huddle-audio');
            const waitingStatus = document.getElementById('waiting-status');

            let audioUnlocked = false;
            let waitingActive = false;

            // ---------------------------
            // 🔊 AUDIO UNLOCK (IMPORTANT)
            // ---------------------------
            function unlockAudio() {
                if (audioUnlocked || !audio) return;

                audio.play()
                    .then(() => {
                        audio.pause();
                        audio.currentTime = 0;
                        audioUnlocked = true;
                        console.log("🔊 Audio unlocked");
                    })
                    .catch(() => {});

            }

            document.addEventListener('click', unlockAudio, { once: true });
            document.addEventListener('keydown', unlockAudio, { once: true });

            // ---------------------------
            // JITSI INIT
            // ---------------------------
            const api = new JitsiMeetExternalAPI(domain, {
                roomName: `LobbyHuddle-Channel-${channelId}`,
                width: "100%",
                height: "100%",
                parentNode: document.getElementById("jitsi-container"),
                userInfo: { displayName: userName },

                configOverwrite: {
                    startWithAudioMuted: false,
                    startWithVideoMuted: true,
                    prejoinPageEnabled: false,
                    disableWelcomePage: true
                },

                interfaceConfigOverwrite: {
                    TOOLBAR_BUTTONS: [
                        'microphone',
                        'camera',
                        'desktop',
                        'fullscreen',
                        'chat',
                        'hangup',
                        'raisehand'
                    ]
                }
            });

            // ---------------------------
            // 🔥 EVENT-BASED LOGIC (NO POLLING)
            // ---------------------------

            function startWaiting() {
                if (waitingActive) return;
                waitingActive = true;

                waitingStatus.classList.remove('hidden');

                if (audioUnlocked && audio) {
                    audio.loop = true;
                    audio.play().catch(() => {});
                }
            }

            function stopWaiting() {
                waitingActive = false;
                waitingStatus.classList.add('hidden');

                if (audio) {
                    audio.pause();
                    audio.currentTime = 0;
                }
            }

            function checkState() {
                const count = api.getNumberOfParticipants();

                if (count <= 1) {
                    startWaiting();
                } else {
                    stopWaiting();
                }
            }

            // Initial check after join
            setTimeout(checkState, 3000);

            // Jitsi real-time events
            api.addEventListener('participantJoined', () => {
                stopWaiting();
            });

            api.addEventListener('participantLeft', () => {
                setTimeout(checkState, 1000);
            });

            api.addEventListener('videoConferenceJoined', () => {
                setTimeout(checkState, 1500);
            });

            api.addEventListener('videoConferenceLeft', () => {
                stopWaiting();
                window.location.href = "{{ url()->previous() }}";
            });

        });
    </script>
</x-app-layout>