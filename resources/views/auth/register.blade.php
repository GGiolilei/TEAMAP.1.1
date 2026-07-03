<x-guest-layout>
    <div class="bg-slate-900 border border-slate-800 p-8 rounded-2xl shadow-xl max-w-xl mx-auto my-6 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-32 h-32 bg-indigo-500/5 rounded-full blur-2xl pointer-events-none"></div>
        
        <div class="mb-6 text-center">
            <h2 class="text-3xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">
                Create Your Account
            </h2>
            <p class="text-sm text-slate-400 mt-1">
                Join TeaMap to connect with project partners and start building.
            </p>
        </div>

        <form method="POST" action="{{ route('register') }}">
            @csrf

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" :value="__('Full Name')" class="text-xs font-semibold text-slate-300 uppercase tracking-wider" />
                    <x-text-input id="name" class="block mt-1.5 w-full bg-slate-950 border-slate-800 focus:border-indigo-500 focus:ring focus:ring-indigo-500/10 text-slate-200 text-sm py-2.5 rounded-xl shadow-inner" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs text-rose-400" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Email Address')" class="text-xs font-semibold text-slate-300 uppercase tracking-wider" />
                    <x-text-input id="email" class="block mt-1.5 w-full bg-slate-950 border-slate-800 focus:border-indigo-500 focus:ring focus:ring-indigo-500/10 text-slate-200 text-sm py-2.5 rounded-xl shadow-inner" type="email" name="email" :value="old('email')" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-rose-400" />
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-4">
                <div>
                    <x-input-label for="password" :value="__('Password')" class="text-xs font-semibold text-slate-300 uppercase tracking-wider" />
                    <x-text-input id="password" class="block mt-1.5 w-full bg-slate-950 border-slate-800 focus:border-indigo-500 focus:ring focus:ring-indigo-500/10 text-slate-200 text-sm py-2.5 rounded-xl shadow-inner" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-rose-400" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirm Password')" class="text-xs font-semibold text-slate-300 uppercase tracking-wider" />
                    <x-text-input id="password_confirmation" class="block mt-1.5 w-full bg-slate-950 border-slate-800 focus:border-indigo-500 focus:ring focus:ring-indigo-500/10 text-slate-200 text-sm py-2.5 rounded-xl shadow-inner" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs text-rose-400" />
                </div>
            </div>

            <div class="mt-8 border-t border-slate-800/80 pt-6">
                <div class="flex justify-between items-baseline mb-1">
                    <x-input-label :value="__('Choose areas of focus')" class="text-xs font-semibold text-indigo-400 uppercase tracking-wider" />
                    <span id="interest-counter" class="text-xs font-bold text-slate-500 bg-slate-950 px-3 py-1 rounded-full border border-slate-800 transition-all duration-300">
                        0 selected
                    </span>
                </div>
                <p class="text-sm text-slate-400 mb-4">
                    Pick 3 to 5 matching tags. This builds your custom dashboard feed!
                </p>
                
                <div class="relative mb-4">
                    <input type="text" id="interest-search" placeholder="Search focus areas (AI, Design, Web)..." 
                           class="w-full bg-slate-950 border-slate-800 focus:border-indigo-500 focus:ring focus:ring-indigo-500/10 text-slate-100 text-sm py-3 px-4 rounded-xl shadow-inner placeholder-slate-600 transition">
                </div>

                <div id="interests-container" class="flex flex-wrap gap-2.5 max-h-72 overflow-y-auto p-2 bg-slate-950 rounded-2xl border border-slate-800/80 shadow-inner scrollbar-thin scrollbar-thumb-slate-800">
                    @foreach(\App\Models\Interest::all() as $interest)
                        @php
                            $is_checked = is_array(old('interests')) && in_array($interest->id, old('interests'));
                        @endphp
                        <label data-interest-name="{{ strtolower($interest->name) }}" 
                               class="interest-tag group inline-flex items-center text-sm font-semibold px-5 py-3 rounded-2xl cursor-pointer transition-all duration-300 select-none border shadow-md transform active:scale-95
                                      {{ $is_checked ? 'bg-gradient-to-br from-indigo-600/30 to-violet-600/30 text-indigo-200 border-indigo-500 shadow-[0_0_15px_rgba(110,6,243,0.15)]' : 'bg-slate-900 text-slate-300 border-slate-800/60 hover:text-white hover:border-slate-600 hover:bg-slate-800/60' }}">
                            
                            <input type="checkbox" name="interests[]" value="{{ $interest->id }}"
                                   {{ $is_checked ? 'checked' : '' }}
                                   class="hidden interest-checkbox">
                            
                            <span>{{ $interest->name }}</span>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('interests')" class="mt-3 text-xs text-rose-400" />
            </div>

            <div class="flex items-center justify-between mt-8 border-t border-slate-800/80 pt-5">
                <a class="text-sm font-medium text-slate-400 hover:text-indigo-400 underline transition" href="{{ route('login') }}">
                    Already registered?
                </a>

                <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-sm py-2.5 px-6 rounded-xl transition duration-200 shadow-lg shadow-indigo-950/40">
                    Create Account
                </button>
            </div>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('interest-search');
            const tags = document.querySelectorAll('.interest-tag');
            const counter = document.getElementById('interest-counter');

            // 1. Unified State Sync (Loads data, applies styles, enforces 5-max limit)
            const syncTagStates = () => {
                const checkedBoxes = document.querySelectorAll('.interest-checkbox:checked');
                const count = checkedBoxes.length;
                
                // --- Counter Pill Updates (Fun 'Emerald/Rose' logic) ---
                if (count === 0) {
                    counter.textContent = '0 selected';
                    counter.className = "text-xs font-bold text-slate-500 bg-slate-950 px-3 py-1 rounded-full border border-slate-800";
                } else if (count >= 3 && count < 5) {
                    counter.textContent = `${count} selected`;
                    counter.className = "text-xs font-bold text-emerald-300 bg-emerald-950/30 px-3 py-1 rounded-full border border-emerald-500/20 shadow-[0_0_10px_rgba(16,185,129,0.1)]";
                } else if (count === 5) {
                    // Maximum reached - Show a "Fun" red/warning pill
                    counter.textContent = 'Max Reached!';
                    counter.className = "text-xs font-bold text-rose-300 bg-rose-950/30 px-3 py-1 rounded-full border border-rose-500/30 shadow-[0_0_10px_rgba(244,63,94,0.15)]";
                } else {
                    counter.textContent = `${count} selected`;
                    counter.className = "text-xs font-bold text-indigo-300 bg-indigo-950/30 px-3 py-1 rounded-full border border-indigo-500/20";
                }

                // --- Tag Card Visual Updates ---
                tags.forEach(tag => {
                    const checkbox = tag.querySelector('.interest-checkbox');
                    
                    if (checkbox.checked) {
                        // SELECTED: Show bigger, fun indigo/violet gradient
                        tag.classList.remove('bg-slate-900', 'text-slate-300', 'border-slate-800/60', 'hover:text-white', 'hover:border-slate-600', 'hover:bg-slate-800/60', 'opacity-30', 'pointer-events-none', 'border-rose-800/40');
                        tag.classList.add('bg-gradient-to-br', 'from-indigo-600/30', 'to-violet-600/30', 'text-indigo-200', 'border-indigo-500', 'shadow-[0_0_15px_rgba(110,6,243,0.15)]');
                    } else {
                        // UNSELECTED: Reset to base styles
                        tag.classList.remove('bg-gradient-to-br', 'from-indigo-600/30', 'to-violet-600/30', 'text-indigo-200', 'border-indigo-500', 'shadow-[0_0_15px_rgba(110,6,243,0.15)]');
                        tag.classList.add('bg-slate-900', 'text-slate-300', 'border-slate-800/60');

                        // CAPPED LOGIC (Count >= 5)
                        if (count >= 5) {
                            // Capped: Add subtle warning outline and disable clickability
                            tag.classList.add('opacity-30', 'pointer-events-none', 'border-rose-800/40');
                            tag.classList.remove('hover:text-white', 'hover:border-slate-600', 'hover:bg-slate-800/60');
                        } else {
                            // Not Capped: Normal unselected behavior
                            tag.classList.remove('opacity-30', 'pointer-events-none', 'border-rose-800/40');
                            tag.classList.add('hover:text-white', 'hover:border-slate-600', 'hover:bg-slate-800/60');
                        }
                    }
                });
            };

            // Run initial sync on load (handles old state)
            syncTagStates();

            // 2. Real-time Search Filtering (Faster/Easier navigation)
            searchInput.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                
                tags.forEach(tag => {
                    const name = tag.getAttribute('data-interest-name');
                    // Fast filter on tag name match
                    if (name.includes(query)) {
                        tag.style.display = 'inline-flex';
                    } else {
                        tag.style.display = 'none';
                    }
                });
            });

            // 3. Prevent clicking more than 5 tags
            tags.forEach(tag => {
                const checkbox = tag.querySelector('.interest-checkbox');
                
                tag.addEventListener('click', (e) => {
                    const currentCheckedCount = document.querySelectorAll('.interest-checkbox:checked').length;
                    
                    // If user tries to check a new box and they've already hit 5
                    if (!checkbox.checked && currentCheckedCount >= 5) {
                        e.preventDefault(); // Stop native behavior
                        return; // Exit
                    }

                    // A brief timeout allows the native HTML token to match the final UI state
                    setTimeout(syncTagStates, 10);
                });
            });
        });
    </script>
</x-guest-layout>