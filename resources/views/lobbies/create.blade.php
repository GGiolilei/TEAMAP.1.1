<x-app-layout>
    <div class="py-10 bg-slate-950 min-h-screen text-slate-100">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 space-y-6">
            
            <div>
                <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-slate-400 hover:text-indigo-400 transition inline-flex items-center gap-1.5 mb-2">
                    ← Back to Dashboard
                </a>
                <h2 class="text-2xl font-extrabold tracking-tight text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">
                    Start a New Project Lobby
                </h2>
                <p class="text-sm text-slate-400 mt-0.5">
                    Set up your collaboration space, outline your goals, and discover the right teammates to bring your concept to life.
                </p>
            </div>

            <div class="bg-slate-900 border border-slate-800 p-6 rounded-2xl shadow-xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-48 h-48 bg-indigo-500/5 rounded-full blur-3xl pointer-events-none"></div>

                <form method="POST" action="{{ route('lobbies.store') }}">
                    @csrf

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label for="name" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Lobby Title</label>
                            <input id="name" name="name" type="text" value="{{ old('name') }}" required 
                                   class="block mt-1.5 w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring focus:ring-indigo-500/10 text-slate-200 text-sm py-2.5 px-3.5 rounded-xl shadow-inner" 
                                   placeholder="e.g., EdTech AI Platform" />
                            <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->get('name')" />
                        </div>

                        <div>
                            <label for="project_goal" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Primary Milestone / Goal</label>
                            <input id="project_goal" name="project_goal" type="text" value="{{ old('project_goal') }}" required 
                                   class="block mt-1.5 w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring focus:ring-indigo-500/10 text-slate-200 text-sm py-2.5 px-3.5 rounded-xl shadow-inner" 
                                   placeholder="e.g., Build functional MVP" />
                            <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->get('project_goal')" />
                        </div>
                    </div>

                    <div class="mt-5">
                        <label for="description" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Project Overview & Mission</label>
                        <textarea id="description" name="description" rows="4" required 
                                  class="block mt-1.5 w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring focus:ring-indigo-500/10 text-slate-200 text-sm p-3.5 rounded-xl leading-relaxed shadow-inner" 
                                  placeholder="Describe the problem you're solving, your target audience, and what makes your startup concept exciting..."></textarea>
                        <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->get('description')" />
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-5">
                        <div>
                            <label for="max_members" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Maximum Team Size</label>
                            <select id="max_members" name="max_members" 
                                    class="block mt-1.5 w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring focus:ring-indigo-500/10 text-slate-200 text-sm py-2.5 px-3.5 rounded-xl shadow-inner cursor-pointer">
                                <option value="2">2 Members (Small Duo)</option>
                                <option value="3">3 Members (Core Pod)</option>
                                <option value="4">4 Members (Standard Setup)</option>
                                <option value="5" selected>5 Members (Recommended Max)</option>
                                <option value="6">6 Members (Large Assembly)</option>
                            </select>
                            <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->get('max_members')" />
                        </div>

                        <div>
                            <label for="required_roles" class="block text-xs font-semibold text-slate-300 uppercase tracking-wider">Looking For Roles</label>
                            <input id="required_roles" name="required_roles" type="text" value="{{ old('required_roles') }}" 
                                   class="block mt-1.5 w-full bg-slate-950 border border-slate-800 focus:border-indigo-500 focus:ring focus:ring-indigo-500/10 text-slate-200 text-sm py-2.5 px-3.5 rounded-xl shadow-inner" 
                                   placeholder="e.g., Frontend Dev, UX Designer, Marketer" />
                            <x-input-error class="mt-1 text-xs text-rose-400" :messages="$errors->get('required_roles')" />
                        </div>
                    </div>

                    <div class="mt-6 border-t border-slate-800/80 pt-5">
                        <label class="block text-xs font-semibold text-indigo-400 uppercase tracking-wider">Project Categories & Focus Tags</label>
                        <p class="text-xs text-slate-400 mt-0.5 mb-3">
                            Select related tags so people with matching interest profiles can discover your lobby instantly.
                        </p>
                        
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 max-h-48 overflow-y-auto p-2 bg-slate-950 rounded-xl border border-slate-800/80 shadow-inner">
                            @foreach(\App\Models\Interest::all() as $interest)
                                <label class="flex items-center space-x-2.5 bg-slate-900 border border-slate-800/60 p-2.5 rounded-lg cursor-pointer hover:border-indigo-500/30 transition shadow-sm group">
                                    <input type="checkbox" name="interests[]" value="{{ $interest->id }}"
                                           {{ is_array(old('interests')) && in_array($interest->id, old('interests')) ? 'checked' : '' }}
                                           class="rounded bg-slate-950 border-slate-800 text-indigo-500 focus:ring-indigo-500/20 focus:ring-offset-0 w-4 h-4 cursor-pointer">
                                    <span class="text-xs font-medium text-slate-300 group-hover:text-slate-200 transition">{{ $interest->name }}</span>
                                </label>
                            @endforeach
                        </div>
                        <x-input-error class="mt-2 text-xs text-rose-400" :messages="$errors->get('interests')" />
                    </div>

                    <div class="mt-6 pt-4 border-t border-slate-800/80 flex items-center justify-end gap-3">
                        <a href="{{ route('dashboard') }}" class="text-xs font-semibold text-slate-400 hover:text-slate-200 transition bg-slate-800/40 border border-slate-800/60 px-4 py-2.5 rounded-xl">
                            Cancel
                        </a>
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-500 text-white font-semibold text-xs py-2.5 px-5 rounded-xl transition duration-200 shadow-lg shadow-indigo-950/40">
                            Launch Project Lobby
                        </button>
                    </div>

                </form>
            </div>

        </div>
    </div>
</x-app-layout>