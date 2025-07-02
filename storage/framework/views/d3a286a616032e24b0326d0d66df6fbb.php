<!DOCTYPE html>
<html lang="<?php echo e(str_replace('_', '-', app()->getLocale())); ?>">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">

    <title><?php echo e(config('app.name', 'Laravel')); ?> - TEC Sunday School</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'yellow': {
                            400: '#fbbf24',
                            500: '#f59e0b',
                        }
                    }
                }
            }
        }
    </script>

    <!-- Custom Styles -->
    <style>
        /* Music Player Custom Styles - Black & Yellow Theme */
        .music-player-gradient {
            background: linear-gradient(135deg, #000000 0%, #1a1a1a 50%, #fbbf24 100%);
        }

        .track-item {
            transition: all 0.2s ease-in-out;
        }

        .track-item:hover {
            transform: translateX(4px);
            background: rgba(251, 191, 36, 0.05);
        }

        .track-item.playing {
            background: linear-gradient(90deg, rgba(251, 191, 36, 0.15) 0%, rgba(251, 191, 36, 0.05) 100%);
            border-left: 4px solid #fbbf24;
        }

        .volume-slider {
            background: linear-gradient(to right, #fbbf24 0%, #fbbf24 50%, #374151 50%, #374151 100%);
            -webkit-appearance: none;
            appearance: none;
            height: 8px;
            border-radius: 4px;
            outline: none;
        }

        .volume-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fbbf24;
            cursor: pointer;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .volume-slider::-moz-range-thumb {
            width: 16px;
            height: 16px;
            border-radius: 50%;
            background: #fbbf24;
            cursor: pointer;
            border: none;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3);
        }

        .progress-container {
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .progress-container:hover {
            transform: scaleY(1.2);
        }

        .loading-spinner {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .fade-in {
            animation: fadeIn 0.3s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .pulse-on-play {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.7; }
        }

        .glass-effect {
            backdrop-filter: blur(10px);
            background: rgba(0, 0, 0, 0.85);
            border: 1px solid rgba(251, 191, 36, 0.2);
        }

        .yellow-accent {
            color: #fbbf24;
        }

        .yellow-bg {
            background-color: #fbbf24;
        }

        .yellow-border {
            border-color: #fbbf24;
        }

        .black-bg {
            background-color: #000000;
        }

        .mobile-player-controls {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: rgba(0, 0, 0, 0.95);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(251, 191, 36, 0.3);
        }

        .mobile-track-item {
            padding: 12px 16px;
            border-bottom: 1px solid rgba(251, 191, 36, 0.1);
        }

        .mobile-track-item:last-child {
            border-bottom: none;
        }

        /* Cover Art Styles */
        .cover-art-container {
            position: relative;
            overflow: hidden;
        }

        .cover-art-container img {
            transition: transform 0.3s ease;
        }

        .track-item:hover .cover-art-container img {
            transform: scale(1.05);
        }

        .cover-art-placeholder {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        }

        /* Now Playing Panel Styles */
        .now-playing-panel {
            position: fixed;
            top: 20px;
            left: 50%;
            transform: translateX(-50%) translateY(-100px);
            width: calc(100% - 40px);
            max-width: 600px;
            z-index: 9999;
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border-radius: 16px;
            box-shadow:
                0 20px 60px rgba(251, 191, 36, 0.5),
                0 8px 24px rgba(0, 0, 0, 0.3),
                0 0 0 1px rgba(255, 255, 255, 0.1);
            opacity: 0;
            transition: all 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(20px);
            border: 2px solid rgba(255, 255, 255, 0.2);
        }

        .now-playing-panel.show {
            transform: translateX(-50%) translateY(0);
            opacity: 0.95;
            animation: slideInBounce 0.6s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .now-playing-panel.show:hover {
            opacity: 1;
            transform: translateX(-50%) translateY(-2px);
            box-shadow:
                0 25px 70px rgba(251, 191, 36, 0.6),
                0 12px 30px rgba(0, 0, 0, 0.4),
                0 0 0 1px rgba(255, 255, 255, 0.2);
        }

        @keyframes slideInBounce {
            0% {
                transform: translateX(-50%) translateY(-100px) scale(0.8);
                opacity: 0;
            }
            60% {
                transform: translateX(-50%) translateY(5px) scale(1.02);
                opacity: 0.9;
            }
            100% {
                transform: translateX(-50%) translateY(0) scale(1);
                opacity: 1;
            }
        }

        .now-playing-panel .pulse-animation {
            animation: nowPlayingPulse 2s ease-in-out infinite;
        }

        @keyframes nowPlayingPulse {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 0 0 rgba(251, 191, 36, 0.7);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 0 10px rgba(251, 191, 36, 0);
            }
        }

        .now-playing-panel .equalizer-bars {
            display: flex;
            align-items: center;
            gap: 2px;
        }

        .now-playing-panel .equalizer-bar {
            width: 3px;
            background: #000;
            border-radius: 2px;
            animation: equalizer 1.5s ease-in-out infinite;
        }

        .now-playing-panel .equalizer-bar:nth-child(1) { height: 12px; animation-delay: 0s; }
        .now-playing-panel .equalizer-bar:nth-child(2) { height: 8px; animation-delay: 0.1s; }
        .now-playing-panel .equalizer-bar:nth-child(3) { height: 16px; animation-delay: 0.2s; }
        .now-playing-panel .equalizer-bar:nth-child(4) { height: 10px; animation-delay: 0.3s; }
        .now-playing-panel .equalizer-bar:nth-child(5) { height: 14px; animation-delay: 0.4s; }

        @keyframes equalizer {
            0%, 100% { transform: scaleY(1); }
            50% { transform: scaleY(0.3); }
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }

        /* Equalizer bars for Now Playing */
        .equalizer-bars {
            display: flex;
            align-items: center;
            space-x: 1px;
        }

        .equalizer-bar {
            width: 3px;
            height: 12px;
            background: rgba(0, 0, 0, 0.6);
            margin: 0 1px;
            border-radius: 2px;
            animation: equalizer 1.5s ease-in-out infinite;
        }

        .equalizer-bar:nth-child(1) { animation-delay: 0s; }
        .equalizer-bar:nth-child(2) { animation-delay: 0.1s; }
        .equalizer-bar:nth-child(3) { animation-delay: 0.2s; }
        .equalizer-bar:nth-child(4) { animation-delay: 0.3s; }
        .equalizer-bar:nth-child(5) { animation-delay: 0.4s; }

        .pulse-animation {
            animation: pulse 2s ease-in-out infinite;
        }

        /* Background overlay when Now Playing is active */
        .now-playing-active::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.1);
            z-index: 40;
            pointer-events: none;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .now-playing-active.show::before {
            opacity: 1;
        }

        /* Progress bar styling */
        #progress-container {
            transition: all 0.2s ease;
        }

        #progress-container:hover {
            transform: scaleY(1.2);
        }

        #now-playing-progress {
            background: linear-gradient(90deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6));
            transition: width 0.2s ease;
        }

        /* Hide bottom mobile panel when floating Now Playing is active */
        .now-playing-active .mobile-player-controls {
            transform: translateY(100%);
            opacity: 0;
            pointer-events: none;
            transition: all 0.3s ease;
        }

        /* Skip button styles */
        .now-playing-panel .skip-btn {
            transition: all 0.2s ease;
            border-radius: 8px;
            background: rgba(0, 0, 0, 0.08);
            border: 1px solid rgba(0, 0, 0, 0.15);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .now-playing-panel .skip-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }

        .now-playing-panel .skip-btn:hover {
            background: rgba(0, 0, 0, 0.12);
            transform: scale(1.05);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
        }

        .now-playing-panel .skip-btn:hover::before {
            left: 100%;
        }

        .now-playing-panel .skip-btn:active {
            transform: scale(0.95);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
        }

        .now-playing-panel .skip-btn svg {
            transition: transform 0.3s ease;
        }

        .now-playing-panel .skip-btn:active svg {
            transform: rotate(15deg);
        }

        .now-playing-panel .skip-btn:nth-child(1):active svg {
            transform: rotate(-15deg);
        }

        /* Play/Pause button styles */
        .now-playing-panel .play-pause-btn {
            transition: all 0.2s ease;
            border-radius: 50%;
            background: rgba(0, 0, 0, 0.15);
            border: 2px solid rgba(0, 0, 0, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
            width: 56px;
            height: 56px;
        }

        .now-playing-panel .play-pause-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            transition: left 0.5s;
        }

        .now-playing-panel .play-pause-btn:hover {
            background: rgba(0, 0, 0, 0.2);
            transform: scale(1.1);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2);
            border-color: rgba(0, 0, 0, 0.3);
        }

        .now-playing-panel .play-pause-btn:hover::before {
            left: 100%;
        }

        .now-playing-panel .play-pause-btn:active {
            transform: scale(0.95);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .now-playing-panel .play-pause-btn svg {
            transition: transform 0.2s ease;
        }

        .now-playing-panel .play-pause-btn:active svg {
            transform: scale(0.9);
        }

        /* Playing state animation */
        .now-playing-panel .play-pause-btn.playing {
            animation: playingPulse 2s ease-in-out infinite;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15), 0 0 0 4px rgba(251, 191, 36, 0.3);
        }

        @keyframes playingPulse {
            0%, 100% {
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15), 0 0 0 4px rgba(251, 191, 36, 0.3);
            }
            50% {
                box-shadow: 0 6px 12px rgba(0, 0, 0, 0.2), 0 0 0 8px rgba(251, 191, 36, 0.2);
            }
        }

        /* Mobile responsive adjustments for Now Playing panel */
        @media (max-width: 768px) {
            .now-playing-panel {
                top: 10px;
                width: calc(100% - 20px);
                max-width: none;
                transform: translateX(-50%) translateY(-120px);
            }

            .now-playing-panel.show {
                transform: translateX(-50%) translateY(0);
            }

            .now-playing-panel .p-6 {
                padding: 1rem;
            }

            .now-playing-panel h3 {
                font-size: 1.125rem;
            }

            .now-playing-panel .w-20 {
                width: 3.5rem;
                height: 3.5rem;
            }

            .now-playing-panel .skip-btn {
                padding: 0.375rem;
            }

            .now-playing-panel .skip-btn svg {
                width: 1.25rem;
                height: 1.25rem;
            }

            .equalizer-bar {
                width: 2px;
                height: 8px;
            }

            .now-playing-panel .play-pause-btn {
                width: 48px;
                height: 48px;
                padding: 0.625rem;
            }

            .now-playing-panel .play-pause-btn svg {
                width: 1.5rem;
                height: 1.5rem;
            }

            /* Mobile progress bar adjustments */
            #now-playing-current-time,
            #now-playing-total-time {
                font-size: 0.625rem;
                min-width: 30px;
            }

            /* Ensure mobile player is hidden when Now Playing is active */
            .now-playing-active .mobile-player-controls {
                display: none !important;
            }

            /* Mobile progress bar styling */
            #now-playing-current-time,
            #now-playing-duration {
                font-size: 0.625rem;
                font-weight: 600;
            }

            #progress-container {
                height: 6px;
            }

            #now-playing-progress {
                height: 6px;
            }
        }

        @media (max-width: 768px) {
            .desktop-only {
                display: none !important;
            }

            .mobile-only {
                display: block !important;
            }

            .mobile-flex {
                display: flex !important;
            }
        }

        @media (min-width: 769px) {
            .mobile-only {
                display: none !important;
            }

            .mobile-player-controls {
                position: relative;
                background: rgba(0, 0, 0, 0.85);
                border: 1px solid rgba(251, 191, 36, 0.2);
                border-radius: 12px;
            }
        }
    </style>
</head>
<body class="font-sans antialiased bg-black text-white">
    <div id="app" class="min-h-screen">
        <!-- Header -->
        <header class="music-player-gradient shadow-lg">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row justify-between items-center py-6 space-y-4 sm:space-y-0">
                    <div class="flex items-center space-x-3">
                        <div class="w-10 h-10 bg-yellow-400 bg-opacity-90 rounded-full flex items-center justify-center">
                            <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                        <h1 class="text-xl sm:text-2xl font-bold text-white">TEC Sunday School</h1>
                    </div>
                    <div class="flex items-center space-x-2 sm:space-x-4">
                        <div class="glass-effect px-2 sm:px-3 py-1 rounded-full text-xs sm:text-sm text-yellow-400 border border-yellow-400 border-opacity-30">
                            <span id="track-count">0</span> tracks
                        </div>
                        <button
                            id="sync-btn"
                            class="bg-yellow-400 hover:bg-yellow-500 text-black px-3 sm:px-6 py-2 rounded-lg transition-all duration-200 font-medium text-sm sm:text-base"
                        >
                            <svg class="w-4 h-4 inline mr-1 sm:mr-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 2a1 1 0 011 1v2.101a7.002 7.002 0 0111.601 2.566 1 1 0 11-1.885.666A5.002 5.002 0 005.999 7H9a1 1 0 010 2H4a1 1 0 01-1-1V3a1 1 0 011-1zm.008 9.057a1 1 0 011.276.61A5.002 5.002 0 0014.001 13H11a1 1 0 110-2h5a1 1 0 011 1v5a1 1 0 11-2 0v-2.101a7.002 7.002 0 01-11.601-2.566 1 1 0 01.61-1.276z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="hidden sm:inline">Sync Messages</span>
                            <span class="sm:hidden">Sync</span>
                        </button>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 sm:py-8 pb-32 sm:pb-8">
            <!-- Search and Filters -->
            <div class="mb-4 sm:mb-8 glass-effect rounded-xl p-4 sm:p-6 fade-in">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6">
                    <div class="relative sm:col-span-2 lg:col-span-1">
                        <label for="search" class="block text-sm font-medium text-yellow-400 mb-2">Search Music</label>
                        <div class="relative">
                            <input
                                type="text"
                                id="search"
                                placeholder="Search tracks, artists, albums..."
                                class="w-full pl-10 pr-4 py-3 bg-gray-900 bg-opacity-70 border border-yellow-400 border-opacity-30 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all duration-200"
                            >
                            <svg class="absolute left-3 top-3.5 w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M8 4a4 4 0 100 8 4 4 0 000-8zM2 8a6 6 0 1110.89 3.476l4.817 4.817a1 1 0 01-1.414 1.414l-4.816-4.816A6 6 0 012 8z" clip-rule="evenodd"></path>
                            </svg>
                        </div>
                    </div>
                    <div>
                        <label for="genre-filter" class="block text-sm font-medium text-yellow-400 mb-2">Genre</label>
                        <select
                            id="genre-filter"
                            class="w-full px-4 py-3 bg-gray-900 bg-opacity-70 border border-yellow-400 border-opacity-30 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all duration-200"
                        >
                            <option value="">All Genres</option>
                        </select>
                    </div>
                    <div>
                        <label for="sort-by" class="block text-sm font-medium text-yellow-400 mb-2">Sort By</label>
                        <select
                            id="sort-by"
                            class="w-full px-4 py-3 bg-gray-900 bg-opacity-70 border border-yellow-400 border-opacity-30 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-yellow-400 focus:border-yellow-400 transition-all duration-200"
                        >
                            <option value="title">Title A-Z</option>
                            <option value="artist">Artist A-Z</option>
                            <option value="album">Album A-Z</option>
                            <option value="year">Year</option>
                            <option value="duration">Duration</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Desktop Music Player Controls -->
            <div id="player-controls" class="desktop-only glass-effect rounded-xl p-6 mb-8 hidden fade-in sticky top-4 z-10">
                <div class="flex flex-col lg:flex-row items-center justify-between space-y-4 lg:space-y-0">
                    <div class="flex items-center space-x-6">
                        <button id="prev-btn" class="text-gray-400 hover:text-yellow-400 transition-all duration-200 hover:scale-110">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z"></path>
                            </svg>
                        </button>
                        <button id="play-pause-btn" class="bg-gradient-to-r from-yellow-400 to-yellow-500 hover:from-yellow-500 hover:to-yellow-600 text-black p-4 rounded-full transition-all duration-200 hover:scale-105 shadow-lg">
                            <svg id="play-icon" class="w-8 h-8" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                            </svg>
                            <svg id="pause-icon" class="w-8 h-8 hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <button id="next-btn" class="text-gray-400 hover:text-yellow-400 transition-all duration-200 hover:scale-110">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11.555 5.168A1 1 0 0010 6v2.798L4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4z"></path>
                            </svg>
                        </button>
                    </div>

                    <div class="flex-1 mx-4 lg:mx-8">
                        <div class="flex items-center justify-center space-x-4 mb-2">
                            <!-- Cover Art -->
                            <div class="relative flex-shrink-0">
                                <img id="current-track-cover" src="" alt="Cover art" class="w-16 h-16 rounded-lg object-cover shadow-lg hidden">
                                <div id="current-track-cover-placeholder" class="w-16 h-16 rounded-lg bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center shadow-lg">
                                    <svg class="w-8 h-8 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <!-- Track Info -->
                            <div class="text-center flex-1">
                                <div id="current-track-title" class="font-semibold text-yellow-400">No track selected</div>
                                <div id="current-track-artist" class="text-sm text-gray-400"></div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-2">
                            <span id="current-time" class="text-sm text-gray-400 min-w-[35px]">0:00</span>
                            <div class="flex-1 bg-gray-700 rounded-full h-2 progress-container">
                                <div id="progress-bar" class="bg-yellow-400 h-2 rounded-full transition-all duration-200" style="width: 0%"></div>
                            </div>
                            <span id="total-time" class="text-sm text-gray-400 min-w-[35px]">0:00</span>
                        </div>
                    </div>

                    <div class="flex items-center space-x-2">
                        <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M9.383 3.076A1 1 0 0110 4v12a1 1 0 01-1.707.707L4.586 13H2a1 1 0 01-1-1V8a1 1 0 011-1h2.586l3.707-3.707a1 1 0 011.09-.217zM15.657 6.343a1 1 0 011.414 0A9.972 9.972 0 0119 12a9.972 9.972 0 01-1.929 5.657 1 1 0 11-1.414-1.414A7.971 7.971 0 0017 12a7.971 7.971 0 00-1.343-4.243 1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                        <input
                            type="range"
                            id="volume-slider"
                            min="0"
                            max="100"
                            value="50"
                            class="w-20 h-2 volume-slider rounded-lg appearance-none cursor-pointer"
                        >
                    </div>
                </div>
            </div>

            <!-- Track List -->
            <div class="glass-effect rounded-xl overflow-hidden">
                <div class="px-4 sm:px-6 py-4 border-b border-yellow-400 border-opacity-20">
                    <h2 class="text-lg font-semibold text-yellow-400">Music Library</h2>
                </div>
                <div id="track-list" class="divide-y divide-yellow-400 divide-opacity-10">
                    <!-- Tracks will be loaded here -->
                </div>
                <div id="loading" class="p-8 text-center text-gray-400">
                    <div class="loading-spinner w-8 h-8 border-2 border-yellow-400 border-t-transparent rounded-full mx-auto mb-4"></div>
                    Loading tracks...
                </div>
                <div id="no-tracks" class="p-8 text-center text-gray-400 hidden">
                    <div class="text-yellow-400 text-6xl mb-4">🎵</div>
                    <p class="text-lg mb-2">No tracks found</p>
                    <p class="text-sm">Click "Sync Messages" to load tracks from Bunny.net storage.</p>
                </div>
            </div>

            <!-- Mobile Player Controls -->
            <div id="mobile-player-controls" class="mobile-only mobile-player-controls hidden">
                <div class="p-4">
                    <!-- Current Track Info -->
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center space-x-3 flex-1 min-w-0">
                            <!-- Mobile Cover Art -->
                            <div class="relative flex-shrink-0">
                                <img id="mobile-current-track-cover" src="" alt="Cover art" class="w-12 h-12 rounded-lg object-cover shadow-md hidden">
                                <div id="mobile-current-track-cover-placeholder" class="w-12 h-12 rounded-lg bg-gradient-to-br from-yellow-400 to-yellow-600 flex items-center justify-center shadow-md">
                                    <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" clip-rule="evenodd"></path>
                                    </svg>
                                </div>
                            </div>
                            <!-- Track Info -->
                            <div class="flex-1 min-w-0">
                                <div id="mobile-current-track-title" class="font-semibold text-yellow-400 truncate">No track selected</div>
                                <div id="mobile-current-track-artist" class="text-sm text-gray-400 truncate"></div>
                            </div>
                        </div>
                        <button id="mobile-minimize-btn" class="text-gray-400 hover:text-yellow-400 ml-4 flex-shrink-0">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Progress Bar -->
                    <div class="flex items-center space-x-2 mb-4">
                        <span id="mobile-current-time" class="text-xs text-gray-400 min-w-[35px]">0:00</span>
                        <div class="flex-1 bg-gray-700 rounded-full h-1 progress-container">
                            <div id="mobile-progress-bar" class="bg-yellow-400 h-1 rounded-full transition-all duration-200" style="width: 0%"></div>
                        </div>
                        <span id="mobile-total-time" class="text-xs text-gray-400 min-w-[35px]">0:00</span>
                    </div>

                    <!-- Control Buttons -->
                    <div class="flex items-center justify-center space-x-8">
                        <button id="mobile-prev-btn" class="text-gray-400 hover:text-yellow-400 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z"></path>
                            </svg>
                        </button>
                        <button id="mobile-play-pause-btn" class="bg-yellow-400 hover:bg-yellow-500 text-black p-3 rounded-full transition-all duration-200">
                            <svg id="mobile-play-icon" class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z" clip-rule="evenodd"></path>
                            </svg>
                            <svg id="mobile-pause-icon" class="w-6 h-6 hidden" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM7 8a1 1 0 012 0v4a1 1 0 11-2 0V8zm5-1a1 1 0 00-1 1v4a1 1 0 102 0V8a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                        <button id="mobile-next-btn" class="text-gray-400 hover:text-yellow-400 transition-colors">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11.555 5.168A1 1 0 0010 6v2.798L4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4z"></path>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Floating Now Playing Panel -->
        <div id="now-playing-panel" class="now-playing-panel hidden">
            <div class="p-6">
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-4">
                        <!-- Now Playing Cover Art -->
                        <div class="relative">
                            <img id="now-playing-cover" src="" alt="Now playing cover" class="w-20 h-20 rounded-xl object-cover shadow-lg pulse-animation hidden">
                            <div id="now-playing-cover-placeholder" class="w-20 h-20 rounded-xl bg-black bg-opacity-20 flex items-center justify-center shadow-lg pulse-animation">
                                <svg class="w-10 h-10 text-black" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" clip-rule="evenodd"></path>
                                </svg>
                            </div>
                        </div>

                        <!-- Track Info -->
                        <div class="flex-1">
                            <div class="flex items-center space-x-3 mb-2">
                                <span class="text-black text-sm font-medium uppercase tracking-wider">Now Playing</span>
                                <div class="equalizer-bars">
                                    <div class="equalizer-bar"></div>
                                    <div class="equalizer-bar"></div>
                                    <div class="equalizer-bar"></div>
                                    <div class="equalizer-bar"></div>
                                    <div class="equalizer-bar"></div>
                                </div>
                            </div>
                            <h3 id="now-playing-title" class="text-xl font-bold text-black mb-1">Track Title</h3>
                            <p id="now-playing-artist" class="text-black text-opacity-80 mb-2">Artist Name</p>

                            <!-- Progress Bar with Time Display -->
                            <div class="mb-2">
                                <div class="flex items-center justify-between text-xs text-black text-opacity-70 mb-1">
                                    <span id="now-playing-current-time">0:00</span>
                                    <span id="now-playing-duration">--:--</span>
                                </div>
                                <div class="w-full bg-black bg-opacity-20 rounded-full h-2 cursor-pointer" id="progress-container">
                                    <div id="now-playing-progress" class="bg-black bg-opacity-60 h-2 rounded-full transition-all duration-200" style="width: 0%"></div>
                                </div>
                            </div>

                            <p class="text-black text-opacity-60 text-xs">Use ← → or J L to skip ±15s • Space or K to play/pause • Panel stays visible</p>
                        </div>
                    </div>

                    <!-- Playback Controls -->
                    <div class="flex items-center space-x-2 mr-4">
                        <button id="now-playing-skip-backward" class="skip-btn text-black p-2" title="Skip backward 15 seconds">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <!-- Circular arrow counter-clockwise with better design -->
                                <path d="M12 4V1L8 5l4 4V6c3.31 0 6 2.69 6 6 0 1.01-.25 1.97-.7 2.8l1.46 1.46C19.54 15.03 20 13.57 20 12c0-4.42-3.58-8-8-8z"/>
                                <path d="M12 18c-3.31 0-6-2.69-6-6 0-1.01.25-1.97.7-2.8L5.24 7.74C4.46 8.97 4 10.43 4 12c0 4.42 3.58 8 8 8v3l4-4-4-4v3z"/>
                                <!-- 15 text -->
                                <text x="12" y="16" text-anchor="middle" font-size="5" font-weight="bold" fill="currentColor">15</text>
                            </svg>
                        </button>

                        <!-- Play/Pause Button -->
                        <button id="now-playing-play-pause" class="play-pause-btn text-black p-3" title="Play/Pause">
                            <svg id="now-playing-play-icon" class="w-8 h-8 hidden" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                            <svg id="now-playing-pause-icon" class="w-8 h-8" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                            </svg>
                        </button>

                        <button id="now-playing-skip-forward" class="skip-btn text-black p-2" title="Skip forward 15 seconds">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                                <!-- Circular arrow clockwise with better design -->
                                <path d="M12 4V1l4 4-4 4V6c-3.31 0-6 2.69-6 6 0 1.01.25 1.97.7 2.8l-1.46 1.46C4.46 15.03 4 13.57 4 12c0-4.42 3.58-8 8-8z"/>
                                <path d="M12 18c3.31 0 6-2.69 6-6 0-1.01-.25-1.97-.7-2.8l1.46-1.46C19.54 8.97 20 10.43 20 12c0 4.42-3.58 8-8 8v3l-4-4 4-4v3z"/>
                                <!-- 15 text -->
                                <text x="12" y="16" text-anchor="middle" font-size="5" font-weight="bold" fill="currentColor">15</text>
                            </svg>
                        </button>
                    </div>

                    <!-- Close Button -->
                    <button id="close-now-playing" class="text-black hover:text-black hover:bg-black hover:bg-opacity-10 p-2 rounded-lg transition-all duration-200" title="Hide Now Playing">
                        <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Audio Element -->
        <audio id="audio-player" preload="metadata"></audio>
    </div>

    <!-- Music Player JavaScript -->
    <script src="<?php echo e(asset('js/music-player.js')); ?>"></script>
</body>
</html>
<?php /**PATH /home/elder/Apps/audio_app/resources/views/music-player/index.blade.php ENDPATH**/ ?>