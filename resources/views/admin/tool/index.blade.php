@extends('layouts.admin')
@section('content')
    <div class="max-w-6xl mx-auto w-full">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-10">
            <!-- TMDB Mass Importer Card -->
            <div
                class="group flex flex-col h-full bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-900 dark:border-gray-800 dark:shadow-0">
                <div class="h-52 flex flex-col justify-center items-center bg-gradient-to-br from-[#01b4e4] to-[#0190b4] text-white rounded-t-xl relative overflow-hidden">
                    <svg class="w-20 h-20 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"/>
                    </svg>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                </div>
                <div class="p-4 md:p-6 flex-1">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-300 dark:hover:text-white mb-2">
                        TMDB Mass Importer
                    </h3>
                    <p class="text-sm text-gray-500">
                        Import multiple movies or TV shows from TMDB in bulk with real-time progress tracking and detailed logs.
                    </p>
                </div>
                <div class="mt-auto flex border-t border-gray-200 dark:border-gray-700">
                    <a class="w-full py-3 px-4 inline-flex justify-center items-center gap-2 rounded-b-xl font-medium bg-white text-gray-700 shadow-sm align-middle hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-primary-500 transition-all text-sm sm:p-4 dark:bg-gray-900 dark:hover:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:focus:ring-offset-gray-800"
                       href="{{route('admin.tool.mass-importer')}}">
                        {{__('Open Importer')}}
                    </a>
                </div>
            </div>
            <!-- End TMDB Mass Importer Card -->

            <!-- TMDB Bulk Import Card -->
            <div
                class="group flex flex-col h-full bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-900 dark:border-gray-800 dark:shadow-0">
                <div class="h-52 flex flex-col justify-center items-center bg-gradient-to-br from-purple-600 to-purple-800 text-white rounded-t-xl relative overflow-hidden">
                    <svg class="w-20 h-20 opacity-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4m0 5c0 2.21-3.582 4-8 4s-8-1.79-8-4"/>
                    </svg>
                    <div class="absolute inset-0 bg-gradient-to-t from-black/20 to-transparent"></div>
                </div>
                <div class="p-4 md:p-6 flex-1">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-300 dark:hover:text-white mb-2">
                        TMDB Bulk Import
                    </h3>
                    <p class="text-sm text-gray-500">
                        Server-side bulk import from VidSrc TMDB ID lists. Background processing with real-time progress monitoring.
                    </p>
                </div>
                <div class="mt-auto flex border-t border-gray-200 dark:border-gray-700">
                    <a class="w-full py-3 px-4 inline-flex justify-center items-center gap-2 rounded-b-xl font-medium bg-white text-gray-700 shadow-sm align-middle hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-primary-500 transition-all text-sm sm:p-4 dark:bg-gray-900 dark:hover:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:focus:ring-offset-gray-800"
                       href="{{route('admin.tmdb.bulk-import')}}">
                        {{__('Start Bulk Import')}}
                    </a>
                </div>
            </div>
            <!-- End TMDB Bulk Import Card -->

            <!-- Themoviedb Card -->
            <div
                class="group flex flex-col h-full bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-900 dark:border-gray-800 dark:shadow-0">
                <div class="h-52 flex flex-col justify-center items-center bg-[#01b4e4] text-[#01b4e4] rounded-t-xl">
                </div>
                <div class="p-4 md:p-6 flex-1">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-300 dark:hover:text-white">
                        Themoviedb
                    </h3>
                    <p class="mt-3 text-sm text-gray-500">
                        A software that develops products for software developers and developments.
                    </p>
                </div>
                <div
                    class="mt-auto flex border-t border-gray-200 divide-x divide-gray-200 dark:border-gray-700 dark:divide-gray-700">
                    <a class="w-full py-3 px-4 inline-flex justify-center items-center gap-2 rounded-bl-xl font-medium bg-white text-gray-700 shadow-sm align-middle hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-primary-500 transition-all text-sm sm:p-4 dark:bg-gray-900 dark:hover:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:focus:ring-offset-gray-800"
                       href="{{route('admin.tmdb.settings')}}">
                        {{__('Config')}}
                    </a>
                    <a class="w-full py-3 px-4 inline-flex justify-center items-center gap-2 rounded-br-xl font-medium bg-white text-gray-700 shadow-sm align-middle hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-primary-500 transition-all text-sm sm:p-4 dark:bg-gray-900 dark:hover:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:focus:ring-offset-gray-800"
                       href="{{route('admin.tmdb.show')}}">
                        {{__('View tool')}}
                    </a>
                </div>
            </div>
            <!-- End Themoviedb Card -->
            <!-- Card -->
            <div
                class="group flex flex-col h-full bg-white border border-gray-200 shadow-sm rounded-xl dark:bg-gray-900 dark:border-gray-800 dark:shadow-0">
                <div class="h-52 flex flex-col justify-center items-center bg-[#e54b4d] text-[#e54b4d] rounded-t-xl">
                </div>
                <div class="p-4 md:p-6">
                    <h3 class="text-lg font-semibold text-gray-800 dark:text-gray-300 dark:hover:text-white">
                        One Signal
                    </h3>
                    <p class="mt-3 text-sm text-gray-500">
                        A software that develops products for software developers and developments.
                    </p>
                </div>
                <div
                    class="mt-auto flex border-t border-gray-200 divide-x divide-gray-200 dark:border-gray-700 dark:divide-gray-700">
                    <a class="w-full py-3 px-4 inline-flex justify-center items-center gap-2 rounded-bl-xl font-medium bg-white text-gray-700 shadow-sm align-middle hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-primary-500 transition-all text-sm sm:p-4 dark:bg-gray-900 dark:hover:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:focus:ring-offset-gray-800"
                       href="{{route('admin.onesignal.settings')}}">
                        {{__('Config')}}
                    </a>
                    <a class="w-full py-3 px-4 inline-flex justify-center items-center gap-2 rounded-br-xl font-medium bg-white text-gray-700 shadow-sm align-middle hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-white focus:ring-primary-500 transition-all text-sm sm:p-4 dark:bg-gray-900 dark:hover:bg-gray-800 dark:border-gray-700 dark:text-gray-400 dark:hover:text-white dark:focus:ring-offset-gray-800"
                       href="{{route('admin.onesignal.show')}}">
                        {{__('View tool')}}
                    </a>
                </div>
            </div>
            <!-- End Card -->

        </div>
        <!-- End Grid -->
    </div>
@endsection
