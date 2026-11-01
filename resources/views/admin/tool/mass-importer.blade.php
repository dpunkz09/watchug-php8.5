@extends('layouts.admin')
@section('content')
    <div class="max-w-7xl mx-auto w-full">
        <!-- Header -->
        <div class="mb-6">
            <div class="flex items-center gap-3 mb-2">
                <a href="{{ route('admin.tool.index') }}" 
                   class="text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-gray-800 dark:text-gray-200">{{ __('TMDB Mass Importer') }}</h1>
            </div>
            <p class="text-gray-600 dark:text-gray-400">{{ __('Import multiple movies or TV shows from TMDB in bulk with real-time progress tracking') }}</p>
        </div>

        <!-- Livewire Component -->
        <livewire:tmdb-mass-importer />
    </div>
@endsection
