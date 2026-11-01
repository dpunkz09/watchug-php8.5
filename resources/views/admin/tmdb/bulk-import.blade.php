@extends('layouts.admin')
@section('content')

    <div class="container-fluid">
        <div class="bg-white border border-gray-200 rounded-lg shadow-sm overflow-hidden dark:bg-gray-900 dark:border-gray-800">
            <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{__('Bulk Import from TMDB')}}</h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{__('Import movies or TV shows in bulk from VidSrc TMDB ID lists')}}</p>
            </div>

            <div class="px-6 py-5">
                <div id="import-form" class="space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">
                            {{__('Select Type')}}
                        </label>
                        <div class="grid grid-cols-2 gap-4">
                            <label class="relative flex cursor-pointer rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm focus:outline-none hover:border-primary-500 dark:hover:border-primary-500">
                                <input type="radio" name="import_type" value="movie" class="sr-only" checked>
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900 dark:text-white">{{__('Movies')}}</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">{{__('Import movie collection')}}</span>
                                    </span>
                                </span>
                                <svg class="h-5 w-5 text-primary-500 hidden radio-check" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </label>

                            <label class="relative flex cursor-pointer rounded-lg border border-gray-300 dark:border-gray-700 bg-white dark:bg-gray-800 p-4 shadow-sm focus:outline-none hover:border-primary-500 dark:hover:border-primary-500">
                                <input type="radio" name="import_type" value="tv" class="sr-only">
                                <span class="flex flex-1">
                                    <span class="flex flex-col">
                                        <span class="block text-sm font-medium text-gray-900 dark:text-white">{{__('TV Shows')}}</span>
                                        <span class="mt-1 flex items-center text-sm text-gray-500 dark:text-gray-400">{{__('Import TV series collection')}}</span>
                                    </span>
                                </span>
                                <svg class="h-5 w-5 text-primary-500 hidden radio-check" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </label>
                        </div>
                    </div>

                    <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-lg p-4">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-blue-400" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-blue-800 dark:text-blue-200">{{__('Important Information')}}</h3>
                                <div class="mt-2 text-sm text-blue-700 dark:text-blue-300">
                                    <ul class="list-disc list-inside space-y-1">
                                        <li>{{__('This will import all items from the selected source')}}</li>
                                        <li>{{__('Already existing items will be skipped automatically')}}</li>
                                        <li>{{__('The import process runs in the background')}}</li>
                                        <li>{{__('You can close this page - the import will continue')}}</li>
                                        <li>{{__('Make sure your queue worker is running')}}</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end">
                        <button type="button" id="start-import-btn" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500">
                            <svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            {{__('Start Import')}}
                        </button>
                    </div>
                </div>

                <div id="import-progress" class="hidden space-y-6">
                    <div>
                        <div class="flex justify-between items-center mb-2">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Import Progress')}}</span>
                            <span class="text-sm text-gray-500 dark:text-gray-400">
                                <span id="progress-processed">0</span> / <span id="progress-total">0</span>
                                (<span id="progress-percentage">0</span>%)
                            </span>
                        </div>
                        <div class="w-full bg-gray-200 dark:bg-gray-700 rounded-full h-3">
                            <div id="progress-bar" class="bg-primary-600 h-3 rounded-full transition-all duration-300" style="width: 0%"></div>
                        </div>
                    </div>

                    <div class="grid grid-cols-4 gap-4">
                        <div class="bg-blue-50 dark:bg-blue-900/20 rounded-lg p-4">
                            <div class="text-sm font-medium text-blue-800 dark:text-blue-200">{{__('Processed')}}</div>
                            <div class="mt-1 text-2xl font-semibold text-blue-900 dark:text-blue-100" id="stat-processed">0</div>
                        </div>
                        <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-4">
                            <div class="text-sm font-medium text-green-800 dark:text-green-200">{{__('Completed')}}</div>
                            <div class="mt-1 text-2xl font-semibold text-green-900 dark:text-green-100" id="stat-completed">0</div>
                        </div>
                        <div class="bg-yellow-50 dark:bg-yellow-900/20 rounded-lg p-4">
                            <div class="text-sm font-medium text-yellow-800 dark:text-yellow-200">{{__('Skipped')}}</div>
                            <div class="mt-1 text-2xl font-semibold text-yellow-900 dark:text-yellow-100" id="stat-skipped">0</div>
                        </div>
                        <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-4">
                            <div class="text-sm font-medium text-red-800 dark:text-red-200">{{__('Failed')}}</div>
                            <div class="mt-1 text-2xl font-semibold text-red-900 dark:text-red-100" id="stat-failed">0</div>
                        </div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-4">
                        <div class="flex justify-between items-center">
                            <div>
                                <div class="text-sm font-medium text-gray-700 dark:text-gray-300">{{__('Status')}}</div>
                                <div class="mt-1 text-sm text-gray-500 dark:text-gray-400" id="import-status-text">{{__('Processing...')}}</div>
                            </div>
                            <div class="flex space-x-3">
                                <button type="button" id="cancel-import-btn" class="inline-flex items-center px-3 py-2 border border-gray-300 dark:border-gray-600 shadow-sm text-sm leading-4 font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none">
                                    {{__('Cancel Import')}}
                                </button>
                                <button type="button" id="new-import-btn" class="hidden inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none">
                                    {{__('New Import')}}
                                </button>
                            </div>
                        </div>
                    </div>

                    <div>
                        <h4 class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-3">{{__('Recent Activity')}}</h4>
                        <div id="activity-log" class="space-y-2 max-h-64 overflow-y-auto">
                            <!-- Activity items will be inserted here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('javascript')
    <script>
        let importId = null;
        let progressInterval = null;

        // Radio button styling
        document.querySelectorAll('input[type="radio"]').forEach(radio => {
            radio.addEventListener('change', function() {
                document.querySelectorAll('.radio-check').forEach(check => check.classList.add('hidden'));
                if (this.checked) {
                    this.closest('label').querySelector('.radio-check').classList.remove('hidden');
                }
            });
        });
        // Trigger initial check
        document.querySelector('input[name="import_type"]:checked').closest('label').querySelector('.radio-check').classList.remove('hidden');

        // Start import
        document.getElementById('start-import-btn').addEventListener('click', function() {
            const type = document.querySelector('input[name="import_type"]:checked').value;
            const btn = this;
            
            btn.disabled = true;
            btn.innerHTML = '<svg class="animate-spin mr-2 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> {{__("Starting...")}}';

            fetch('{{ route("admin.tmdb.bulk-import-start") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({
                    type: type,
                    source: 'vidsrc'
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    importId = data.import_id;
                    document.getElementById('import-form').classList.add('hidden');
                    document.getElementById('import-progress').classList.remove('hidden');
                    document.getElementById('progress-total').textContent = data.total;
                    startProgressPolling();
                } else {
                    alert(data.message);
                    btn.disabled = false;
                    btn.innerHTML = '<svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg> {{__("Start Import")}}';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('{{__("Failed to start import")}}');
                btn.disabled = false;
                btn.innerHTML = '<svg class="mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg> {{__("Start Import")}}';
            });
        });

        // Progress polling
        function startProgressPolling() {
            progressInterval = setInterval(checkProgress, 2000); // Check every 2 seconds
        }

        function checkProgress() {
            if (!importId) return;

            fetch('{{ route("admin.tmdb.bulk-import-progress") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ import_id: importId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    updateProgress(data.progress);
                }
            })
            .catch(error => console.error('Error:', error));
        }

        function updateProgress(progress) {
            document.getElementById('progress-processed').textContent = progress.processed;
            document.getElementById('progress-percentage').textContent = progress.percentage;
            document.getElementById('progress-bar').style.width = progress.percentage + '%';
            
            document.getElementById('stat-processed').textContent = progress.processed;
            document.getElementById('stat-completed').textContent = progress.completed;
            document.getElementById('stat-skipped').textContent = progress.skipped;
            document.getElementById('stat-failed').textContent = progress.failed;

            // Update activity log with latest items
            if (progress.items) {
                const activityLog = document.getElementById('activity-log');
                const items = Object.values(progress.items).slice(-10).reverse(); // Show last 10 items
                
                activityLog.innerHTML = items.map(item => {
                    const statusColors = {
                        completed: 'green',
                        failed: 'red',
                        skipped: 'yellow'
                    };
                    const color = statusColors[item.status] || 'gray';
                    
                    return `
                        <div class="flex items-center justify-between p-2 bg-${color}-50 dark:bg-${color}-900/20 rounded">
                            <span class="text-sm text-${color}-800 dark:text-${color}-200">
                                ${item.type.toUpperCase()} - TMDB ID: ${item.tmdb_id}
                            </span>
                            <span class="text-xs text-${color}-600 dark:text-${color}-400">
                                ${item.status} ${item.message ? '- ' + item.message : ''}
                            </span>
                        </div>
                    `;
                }).join('');
            }

            if (progress.is_complete) {
                clearInterval(progressInterval);
                document.getElementById('import-status-text').textContent = '{{__("Import completed!")}}';
                document.getElementById('cancel-import-btn').classList.add('hidden');
                document.getElementById('new-import-btn').classList.remove('hidden');
            }
        }

        // Cancel import
        document.getElementById('cancel-import-btn').addEventListener('click', function() {
            if (!confirm('{{__("Are you sure you want to cancel the import?")}}')) return;

            fetch('{{ route("admin.tmdb.bulk-import-cancel") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ import_id: importId })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    clearInterval(progressInterval);
                    document.getElementById('import-status-text').textContent = '{{__("Import cancelled")}}';
                    document.getElementById('cancel-import-btn').classList.add('hidden');
                    document.getElementById('new-import-btn').classList.remove('hidden');
                }
            });
        });

        // New import
        document.getElementById('new-import-btn').addEventListener('click', function() {
            location.reload();
        });

        // Resume progress if page is refreshed
        const urlParams = new URLSearchParams(window.location.search);
        const resumeImportId = urlParams.get('import_id');
        if (resumeImportId) {
            importId = resumeImportId;
            document.getElementById('import-form').classList.add('hidden');
            document.getElementById('import-progress').classList.remove('hidden');
            startProgressPolling();
        }
    </script>
    @endpush

@endsection
