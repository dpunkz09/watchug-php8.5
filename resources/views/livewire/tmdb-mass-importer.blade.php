<div class="space-y-6" @if($isImporting && !$completed) wire:poll.1s="processNextPage" @endif>
    <!-- Configuration Form -->
    @if(!$isImporting && !$completed)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
            <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200 mb-4">Import Configuration</h3>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <!-- Type Selection -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Content Type</label>
                    <select wire:model="type" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="movie">Movies</option>
                        <option value="tv">TV Shows</option>
                    </select>
                    @error('type') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Sort By -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Sort By</label>
                    <select wire:model="sortBy" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                        <option value="popularity.desc">Popularity (Descending)</option>
                        <option value="popularity.asc">Popularity (Ascending)</option>
                        <option value="release_date.desc">Release Date (Newest)</option>
                        <option value="release_date.asc">Release Date (Oldest)</option>
                        <option value="vote_average.desc">Rating (Highest)</option>
                        <option value="vote_average.asc">Rating (Lowest)</option>
                        <option value="revenue.desc">Revenue (Highest)</option>
                    </select>
                    @error('sortBy') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Start Page -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Start Page</label>
                    <input type="number" wire:model="startPage" min="1" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    @error('startPage') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>

                <!-- Total Pages -->
                <div>
                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Number of Pages</label>
                    <input type="number" wire:model="totalPages" min="1" max="100" class="w-full px-4 py-2 border border-gray-300 dark:border-gray-700 rounded-lg bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">Each page contains ~20 items. Max: 100 pages</p>
                    @error('totalPages') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
                </div>
            </div>

            <!-- Import Options -->
            <div class="mt-4 p-4 bg-gray-50 dark:bg-gray-800/50 rounded-lg border border-gray-200 dark:border-gray-700 space-y-3">
                <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">Import Options</h4>
                
                <label class="flex items-start gap-3 cursor-pointer">
                    <input type="checkbox" wire:model="importPeople" class="mt-1 w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                    <div>
                        <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Import Cast & Crew</span>
                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                            ⚠️ Warning: Importing people significantly increases processing time as it downloads and processes images for each person. 
                            Recommended to leave disabled for large imports.
                        </p>
                    </div>
                </label>

                @if($type === 'tv')
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input type="checkbox" wire:model="importSeasons" class="mt-1 w-4 h-4 text-primary-600 bg-gray-100 border-gray-300 rounded focus:ring-primary-500 dark:focus:ring-primary-600 dark:ring-offset-gray-800 focus:ring-2 dark:bg-gray-700 dark:border-gray-600">
                        <div>
                            <span class="text-sm font-medium text-gray-900 dark:text-gray-100">Import Seasons & Episodes</span>
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
                                ✅ Recommended: Import complete season and episode data for TV shows. Required for proper episode management.
                            </p>
                        </div>
                    </label>
                @endif
            </div>

            <div class="mt-6 flex items-center gap-4">
                <button wire:click="startImport" 
                        wire:loading.attr="disabled"
                        class="px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-lg transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed">
                    <span class="flex items-center gap-2">
                        <svg wire:loading.remove class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        <svg wire:loading class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span wire:loading.remove>Start Mass Import</span>
                        <span wire:loading>Starting...</span>
                    </span>
                </button>
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    Estimated items: <span class="font-semibold">{{ $totalPages * 20 }}</span>
                </p>
            </div>

            <!-- Debug Info -->
            <div class="mt-4 p-3 bg-gray-100 dark:bg-gray-800 rounded text-xs">
                <strong>Debug:</strong> isImporting={{ $isImporting ? 'true' : 'false' }}, 
                currentPage={{ $currentPage }}, 
                logs={{ count($logs) }}
            </div>
        </div>
    @endif

    <!-- Progress Section -->
    @if($isImporting || $completed)
        <div class="bg-white dark:bg-gray-900 rounded-xl shadow-sm border border-gray-200 dark:border-gray-800 p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-200">Import Progress</h3>
                @if($completed)
                    <span class="px-3 py-1 bg-green-100 dark:bg-green-900/30 text-green-600 dark:text-green-400 text-sm font-medium rounded-full">
                        Completed
                    </span>
                @else
                    <span class="px-3 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-600 dark:text-blue-400 text-sm font-medium rounded-full animate-pulse">
                        In Progress
                    </span>
                @endif
            </div>

            <!-- Progress Bar -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-sm text-gray-600 dark:text-gray-400">Page {{ $currentPage }} of {{ $startPage + $totalPages - 1 }}</span>
                    <span class="text-sm font-semibold text-gray-800 dark:text-gray-200">{{ $progress }}%</span>
                </div>
                <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-primary-500 to-primary-600 rounded-full transition-all duration-300 ease-out" 
                         style="width: {{ $progress }}%"></div>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                    <div class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ $totalItems }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Total Found</div>
                </div>
                <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                    <div class="text-2xl font-bold text-green-600 dark:text-green-400">{{ $importedCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Imported</div>
                </div>
                <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                    <div class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">{{ $skippedCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Skipped</div>
                </div>
                <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                    <div class="text-2xl font-bold text-red-600 dark:text-red-400">{{ $errorCount }}</div>
                    <div class="text-sm text-gray-600 dark:text-gray-400">Errors</div>
                </div>
            </div>

            <!-- Current Item -->
            @if($isImporting && $currentItem)
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center gap-2">
                        <svg class="w-5 h-5 text-blue-500 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                        <span class="text-sm text-gray-700 dark:text-gray-300">Processing: <span class="font-medium">{{ $currentItem }}</span></span>
                    </div>
                </div>
            @endif

            <!-- Logs -->
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4 max-h-96 overflow-y-auto">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">Activity Log</h4>
                <div class="space-y-2 font-mono text-xs">
                    @forelse($logs as $log)
                        <div class="flex gap-2">
                            <span class="text-gray-500 dark:text-gray-500 shrink-0">{{ $log['time'] }}</span>
                            <span class="
                                @if($log['type'] === 'success') text-green-600 dark:text-green-400
                                @elseif($log['type'] === 'error') text-red-600 dark:text-red-400
                                @elseif($log['type'] === 'warning') text-yellow-600 dark:text-yellow-400
                                @else text-gray-600 dark:text-gray-400
                                @endif
                            ">{{ $log['message'] }}</span>
                        </div>
                    @empty
                        <p class="text-gray-500 dark:text-gray-400">No logs yet...</p>
                    @endforelse
                </div>
            </div>

            <!-- Action Buttons -->
            @if($completed)
                <div class="mt-6 flex gap-4">
                    <button wire:click="resetImport" 
                            class="px-6 py-3 bg-primary-500 hover:bg-primary-600 text-white font-medium rounded-lg transition-colors">
                        Start New Import
                    </button>
                    <a href="{{ route('admin.movie.index') }}" 
                       class="px-6 py-3 bg-gray-200 dark:bg-gray-700 hover:bg-gray-300 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-200 font-medium rounded-lg transition-colors">
                        View Posts
                    </a>
                </div>
            @endif
        </div>
    @endif

    <!-- Info Box -->
    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
        <div class="flex gap-3">
            <svg class="w-5 h-5 text-blue-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
            </svg>
            <div class="text-sm text-gray-700 dark:text-gray-300">
                <p class="font-semibold mb-1">Important Notes:</p>
                <ul class="list-disc list-inside space-y-1 text-gray-600 dark:text-gray-400">
                    <li>The import process may take several minutes depending on the number of pages</li>
                    <li>Items that already exist in your database will be skipped</li>
                    <li>Make sure your TMDB API key is configured in settings</li>
                    <li>Large imports may consume significant API quota</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Image Settings Notice -->
    @if(config('settings.tmdb_image') != 'active')
        <div class="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-yellow-500 shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div class="text-sm">
                    <p class="font-semibold text-yellow-800 dark:text-yellow-200 mb-1">⚠️ Image Setting Not Configured</p>
                    <p class="text-yellow-700 dark:text-yellow-300 mb-2">
                        The "Use themoviedb image" setting is currently disabled. Images may not display correctly for imported content.
                    </p>
                    <p class="text-yellow-700 dark:text-yellow-300 mb-2">
                        <strong>Recommended:</strong> Go to <a href="{{ route('admin.tmdb.settings') }}" class="underline font-semibold hover:text-yellow-900 dark:hover:text-yellow-100">Tools → Themoviedb → Config</a> and enable "Use themoviedb image" before importing.
                    </p>
                    <p class="text-xs text-yellow-600 dark:text-yellow-400">
                        This allows the system to use TMDB's CDN for images instead of downloading them locally (which is too slow for mass imports).
                    </p>
                </div>
            </div>
        </div>
    @endif
</div>
