<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Music Player</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-black text-white min-h-screen">
    <!-- Header -->
    <header class="bg-gradient-to-r from-black via-gray-900 to-yellow-600 p-4 sm:p-6">
        <div class="max-w-6xl mx-auto flex flex-col sm:flex-row justify-between items-center space-y-3 sm:space-y-0">
            <h1 class="text-xl sm:text-2xl font-bold text-yellow-400">🎵 Music Player</h1>
            <div class="flex items-center space-x-2 sm:space-x-4">
                <span class="text-yellow-400 text-sm sm:text-base"><span id="track-count">0</span> tracks</span>
                <button id="sync-btn" class="bg-yellow-500 text-black px-3 py-2 sm:px-4 sm:py-2 rounded-lg font-medium hover:bg-yellow-400 text-sm sm:text-base">
                    Sync Music
                </button>
            </div>
        </div>
    </header>

    <!-- Now Playing Bar (Fixed at top when playing) -->
    <div id="now-playing-bar" class="fixed top-0 left-0 right-0 bg-gradient-to-r from-yellow-600 via-yellow-500 to-yellow-400 text-black p-3 sm:p-4 shadow-lg z-50 transform -translate-y-full transition-transform duration-300 hidden">
        <div class="max-w-6xl mx-auto flex items-center justify-between">
            <div class="flex items-center space-x-3 flex-1 min-w-0">
                <div class="flex items-center space-x-2">
                    <button id="mini-play-pause" class="bg-black bg-opacity-20 hover:bg-opacity-30 text-black p-2 rounded-full transition-all">
                        <svg id="mini-play-icon" class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <svg id="mini-pause-icon" class="w-4 h-4 hidden" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                        </svg>
                    </button>
                </div>
                <div class="flex-1 min-w-0">
                    <div id="mini-track-title" class="font-semibold text-sm sm:text-base truncate">Track Title</div>
                    <div class="flex items-center space-x-2">
                        <div id="mini-track-artist" class="text-xs sm:text-sm opacity-80 truncate">Artist Name</div>
                        <div class="text-xs opacity-60 flex-shrink-0">
                            <span id="mini-current-time">0:00</span> / <span id="mini-total-time">0:00</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="flex items-center space-x-2">
                <button id="mini-skip-backward" class="bg-black bg-opacity-20 hover:bg-opacity-30 text-black p-2 rounded-full transition-all" title="Skip back 15s">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M11.99 5V1l-5 5 5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6h-2c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/>
                        <text x="12" y="16" text-anchor="middle" font-size="6" fill="currentColor">15</text>
                    </svg>
                </button>
                <button id="mini-skip-forward" class="bg-black bg-opacity-20 hover:bg-opacity-30 text-black p-2 rounded-full transition-all" title="Skip forward 15s">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 5V1l5 5-5 5V7c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6h2c0 4.42-3.58 8-8 8s-8-3.58-8-8 3.58-8 8-8z"/>
                        <text x="12" y="16" text-anchor="middle" font-size="6" fill="currentColor">15</text>
                    </svg>
                </button>
                <button id="close-mini-player" class="bg-black bg-opacity-20 hover:bg-opacity-30 text-black p-2 rounded-full transition-all ml-2" title="Hide player">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"/>
                    </svg>
                </button>
            </div>
        </div>
        <!-- Mini Progress Bar -->
        <div class="max-w-6xl mx-auto mt-2">
            <div class="bg-black bg-opacity-20 rounded-full h-1 cursor-pointer" id="mini-progress-container">
                <div id="mini-progress-bar" class="bg-black bg-opacity-40 h-1 rounded-full transition-all duration-200" style="width: 0%"></div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="max-w-6xl mx-auto p-4 sm:p-6" id="main-content">
        <!-- Search and Filters -->
        <div class="bg-gray-800 rounded-lg p-3 sm:p-4 mb-4 sm:mb-6">
            <!-- Search Bar -->
            <div class="mb-4">
                <input type="text" id="search" placeholder="Search tracks, artists, albums..."
                       class="w-full bg-gray-700 text-white px-3 sm:px-4 py-2 rounded border border-yellow-500 focus:outline-none focus:border-yellow-400 text-sm sm:text-base">
            </div>

            <!-- Filter Toggle Button -->
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-yellow-400 font-medium text-sm sm:text-base">Filters</h3>
                <button id="toggle-filters" class="text-yellow-400 hover:text-yellow-300 text-sm">
                    <span id="filter-toggle-text">Show Filters</span>
                    <span id="filter-toggle-icon">▼</span>
                </button>
            </div>

            <!-- Filters Panel -->
            <div id="filters-panel" class="hidden space-y-3">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    <!-- Artist Filter -->
                    <div>
                        <label class="block text-gray-400 text-xs mb-1">Artist</label>
                        <select id="filter-artist" class="w-full bg-gray-700 text-white px-3 py-2 rounded border border-gray-600 focus:border-yellow-400 text-sm">
                            <option value="">All Artists</option>
                        </select>
                    </div>

                    <!-- Album Filter -->
                    <div>
                        <label class="block text-gray-400 text-xs mb-1">Album</label>
                        <select id="filter-album" class="w-full bg-gray-700 text-white px-3 py-2 rounded border border-gray-600 focus:border-yellow-400 text-sm">
                            <option value="">All Albums</option>
                        </select>
                    </div>

                    <!-- Genre Filter -->
                    <div>
                        <label class="block text-gray-400 text-xs mb-1">Genre</label>
                        <select id="filter-genre" class="w-full bg-gray-700 text-white px-3 py-2 rounded border border-gray-600 focus:border-yellow-400 text-sm">
                            <option value="">All Genres</option>
                        </select>
                    </div>

                    <!-- Year Filter -->
                    <div>
                        <label class="block text-gray-400 text-xs mb-1">Year</label>
                        <select id="filter-year" class="w-full bg-gray-700 text-white px-3 py-2 rounded border border-gray-600 focus:border-yellow-400 text-sm">
                            <option value="">All Years</option>
                        </select>
                    </div>

                    <!-- Duration Filter -->
                    <div>
                        <label class="block text-gray-400 text-xs mb-1">Duration</label>
                        <select id="filter-duration" class="w-full bg-gray-700 text-white px-3 py-2 rounded border border-gray-600 focus:border-yellow-400 text-sm">
                            <option value="">Any Duration</option>
                            <option value="short">Short (< 2 min)</option>
                            <option value="medium">Medium (2-5 min)</option>
                            <option value="long">Long (> 5 min)</option>
                        </select>
                    </div>

                    <!-- Clear Filters -->
                    <div class="flex items-end">
                        <button id="clear-filters" class="w-full bg-gray-600 hover:bg-gray-500 text-white px-3 py-2 rounded text-sm transition-colors">
                            Clear All
                        </button>
                    </div>
                </div>

                <!-- Active Filters Display -->
                <div id="active-filters" class="hidden">
                    <div class="flex flex-wrap gap-2 pt-2 border-t border-gray-600">
                        <span class="text-gray-400 text-xs">Active filters:</span>
                        <div id="active-filters-list" class="flex flex-wrap gap-1"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Music Library -->
        <div class="bg-gray-800 rounded-lg">
            <div class="p-3 sm:p-4 border-b border-gray-700">
                <h2 class="text-lg sm:text-xl font-semibold text-yellow-400">Music Library</h2>
            </div>
            <div id="track-list" class="min-h-[200px]">
                <div class="p-4 text-center text-gray-400">Loading tracks...</div>
            </div>
        </div>

        <!-- Full Audio Player (shows when track is playing) -->
        <div id="audio-container" class="mt-4 sm:mt-6 hidden">
            <div class="bg-gray-800 rounded-lg p-3 sm:p-4">
                <div id="now-playing" class="text-yellow-400 mb-3 sm:mb-4 text-center font-medium text-sm sm:text-base px-2">Now Playing: </div>

                <!-- Custom Controls -->
                <div class="flex items-center justify-center space-x-3 sm:space-x-4 mb-3 sm:mb-4">
                    <button id="skip-backward" class="bg-gray-700 hover:bg-gray-600 text-yellow-400 p-2 sm:p-3 rounded-full transition-all duration-200 hover:scale-105 touch-manipulation" title="Skip back 15 seconds">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M11.99 5V1l-5 5 5 5V7c3.31 0 6 2.69 6 6s-2.69 6-6 6-6-2.69-6-6h-2c0 4.42 3.58 8 8 8s8-3.58 8-8-3.58-8-8-8z"/>
                            <text x="12" y="16" text-anchor="middle" font-size="7" fill="currentColor">15</text>
                        </svg>
                    </button>

                    <button id="play-pause" class="bg-yellow-500 hover:bg-yellow-400 text-black p-3 sm:p-4 rounded-full transition-all duration-200 hover:scale-105 shadow-lg touch-manipulation">
                        <svg id="play-icon" class="w-6 h-6 sm:w-8 sm:h-8" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <svg id="pause-icon" class="w-6 h-6 sm:w-8 sm:h-8 hidden" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
                        </svg>
                    </button>

                    <button id="skip-forward" class="bg-gray-700 hover:bg-gray-600 text-yellow-400 p-2 sm:p-3 rounded-full transition-all duration-200 hover:scale-105 touch-manipulation" title="Skip forward 15 seconds">
                        <svg class="w-5 h-5 sm:w-6 sm:h-6" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 5V1l5 5-5 5V7c-3.31 0-6 2.69-6 6s2.69 6 6 6 6-2.69 6-6h2c0 4.42-3.58 8-8 8s-8-3.58-8-8 3.58-8 8-8z"/>
                            <text x="12" y="16" text-anchor="middle" font-size="7" fill="currentColor">15</text>
                        </svg>
                    </button>
                </div>

                <!-- Progress Bar -->
                <div class="mb-3 sm:mb-4">
                    <div class="flex items-center space-x-2 text-xs sm:text-sm text-gray-400 mb-1">
                        <span id="current-time" class="min-w-[35px] text-center">0:00</span>
                        <div class="flex-1 bg-gray-700 rounded-full h-3 sm:h-2 cursor-pointer touch-manipulation" id="progress-container">
                            <div id="progress-bar" class="bg-yellow-500 h-3 sm:h-2 rounded-full transition-all duration-200" style="width: 0%"></div>
                        </div>
                        <span id="total-time" class="min-w-[35px] text-center">0:00</span>
                    </div>
                </div>

                <!-- Native Audio Controls (hidden but functional) -->
                <audio id="audio-player" class="w-full opacity-30 sm:opacity-50" controls></audio>
            </div>
        </div>
    </main>

    <script>
        // Global variables
        let allTracks = [];
        let filteredTracks = [];

        // Load tracks immediately
        loadTracks();

        function loadTracks() {
            fetch('/api/music/tracks')
                .then(response => response.json())
                .then(data => {
                    allTracks = data.data || [];
                    filteredTracks = [...allTracks];

                    displayTracks(filteredTracks);
                    document.getElementById('track-count').textContent = allTracks.length;

                    // Populate filter options
                    populateFilters();
                })
                .catch(error => {
                    console.error('Error:', error);
                    document.getElementById('track-list').innerHTML =
                        '<div class="p-4 text-red-400">Error loading tracks: ' + error.message + '</div>';
                });
        }

        function displayTracks(tracks) {
            const trackList = document.getElementById('track-list');
            
            if (tracks.length === 0) {
                trackList.innerHTML = '<div class="p-4 text-center text-gray-400">No tracks found. Click "Sync Music" to load tracks.</div>';
                return;
            }

            let html = '';
            tracks.forEach((track, index) => {
                html += `
                    <div class="track-row border-b border-gray-700 transition-colors touch-manipulation">
                        <div class="p-3 sm:p-4 hover:bg-gray-700 cursor-pointer"
                             onclick="playTrack('${track.streaming_url}', '${escapeHtml(track.title)}', '${escapeHtml(track.artist || 'Unknown Artist')}')">

                            <!-- Main Track Info -->
                            <div class="flex justify-between items-start mb-2">
                                <div class="flex-1 min-w-0 pr-3">
                                    <div class="text-white font-semibold text-base sm:text-lg truncate">${escapeHtml(track.title || 'Unknown Title')}</div>
                                    <div class="text-yellow-400 text-sm sm:text-base truncate font-medium">${escapeHtml(track.artist || 'Unknown Artist')}</div>
                                </div>
                                <div class="flex items-center space-x-2 flex-shrink-0">
                                    <span class="text-gray-400 text-xs sm:text-sm hidden sm:block">${track.formatted_file_size || ''}</span>
                                    <button onclick="event.stopPropagation(); downloadTrack(${track.id}, '${escapeHtml(track.title)}')"
                                            class="bg-yellow-500 hover:bg-yellow-400 text-black p-2 rounded touch-manipulation transition-colors" title="Download">
                                        ⬇️
                                    </button>
                                </div>
                            </div>

                            <!-- Metadata Row -->
                            <div class="space-y-1 sm:space-y-0 sm:grid sm:grid-cols-2 lg:grid-cols-4 gap-2 sm:gap-4 text-xs sm:text-sm text-gray-400">
                                <div class="flex items-center space-x-1">
                                    <span class="text-gray-500 flex-shrink-0">Album:</span>
                                    <span class="truncate">${escapeHtml(track.album || 'Unknown')}</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="text-gray-500 flex-shrink-0">Date:</span>
                                    <span class="truncate font-medium">${track.formatted_date || 'Unknown'}</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="text-gray-500 flex-shrink-0">Duration:</span>
                                    <span class="flex-shrink-0">${track.formatted_duration || 'Unknown'}</span>
                                </div>
                                <div class="flex items-center space-x-1">
                                    <span class="text-gray-500 flex-shrink-0">Genre:</span>
                                    <span class="truncate">${escapeHtml(track.genre || 'Unknown')}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
            });
            
            trackList.innerHTML = html;
        }

        function playTrack(url, title, artist, trackData = null) {
            const audioContainer = document.getElementById('audio-container');
            const audioPlayer = document.getElementById('audio-player');
            const nowPlaying = document.getElementById('now-playing');
            const nowPlayingBar = document.getElementById('now-playing-bar');
            const miniTrackTitle = document.getElementById('mini-track-title');
            const miniTrackArtist = document.getElementById('mini-track-artist');
            const mainContent = document.getElementById('main-content');

            audioPlayer.src = url;

            // Enhanced now playing display
            let nowPlayingText = 'Now Playing: ' + title;
            if (artist && artist !== 'Unknown Artist') {
                nowPlayingText += ' by ' + artist;
            }

            nowPlaying.textContent = nowPlayingText;
            miniTrackTitle.textContent = title;
            miniTrackArtist.textContent = artist;

            // Show both players
            audioContainer.classList.remove('hidden');
            nowPlayingBar.classList.remove('hidden');

            // Show mini player with animation
            setTimeout(() => {
                nowPlayingBar.classList.remove('-translate-y-full');
                // Add top padding to main content to account for mini player
                mainContent.style.paddingTop = '80px';
            }, 100);

            audioPlayer.play().then(() => {
                updatePlayPauseButton(true);
                updateMiniPlayPauseButton(true);
            }).catch(error => {
                console.error('Playback error:', error);
                alert('Failed to play: ' + title);
            });
        }

        function updatePlayPauseButton(isPlaying) {
            const playIcon = document.getElementById('play-icon');
            const pauseIcon = document.getElementById('pause-icon');

            if (isPlaying) {
                playIcon.classList.add('hidden');
                pauseIcon.classList.remove('hidden');
            } else {
                playIcon.classList.remove('hidden');
                pauseIcon.classList.add('hidden');
            }
        }

        function updateMiniPlayPauseButton(isPlaying) {
            const miniPlayIcon = document.getElementById('mini-play-icon');
            const miniPauseIcon = document.getElementById('mini-pause-icon');

            if (isPlaying) {
                miniPlayIcon.classList.add('hidden');
                miniPauseIcon.classList.remove('hidden');
            } else {
                miniPlayIcon.classList.remove('hidden');
                miniPauseIcon.classList.add('hidden');
            }
        }

        function hideMiniPlayer() {
            const nowPlayingBar = document.getElementById('now-playing-bar');
            const mainContent = document.getElementById('main-content');

            nowPlayingBar.classList.add('-translate-y-full');
            mainContent.style.paddingTop = '';

            setTimeout(() => {
                nowPlayingBar.classList.add('hidden');
            }, 300);
        }

        function formatTime(seconds) {
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return mins + ':' + (secs < 10 ? '0' : '') + secs;
        }

        // Filter and Search Functions
        function populateFilters() {
            const artists = [...new Set(allTracks.map(track => track.artist).filter(Boolean))].sort();
            const albums = [...new Set(allTracks.map(track => track.album).filter(Boolean))].sort();
            const genres = [...new Set(allTracks.map(track => track.genre).filter(Boolean))].sort();
            const years = [...new Set(allTracks.map(track => track.year).filter(Boolean))].sort((a, b) => b - a);

            populateSelect('filter-artist', artists);
            populateSelect('filter-album', albums);
            populateSelect('filter-genre', genres);
            populateSelect('filter-year', years);
        }

        function populateSelect(selectId, options) {
            const select = document.getElementById(selectId);
            const currentValue = select.value;

            // Clear existing options except the first one
            while (select.children.length > 1) {
                select.removeChild(select.lastChild);
            }

            // Add new options
            options.forEach(option => {
                const optionElement = document.createElement('option');
                optionElement.value = option;
                optionElement.textContent = option;
                select.appendChild(optionElement);
            });

            // Restore previous selection if it still exists
            if (options.includes(currentValue)) {
                select.value = currentValue;
            }
        }

        function applyFilters() {
            const searchTerm = document.getElementById('search').value.toLowerCase();
            const artistFilter = document.getElementById('filter-artist').value;
            const albumFilter = document.getElementById('filter-album').value;
            const genreFilter = document.getElementById('filter-genre').value;
            const yearFilter = document.getElementById('filter-year').value;
            const durationFilter = document.getElementById('filter-duration').value;

            filteredTracks = allTracks.filter(track => {
                // Search filter
                const searchMatch = !searchTerm ||
                    (track.title && track.title.toLowerCase().includes(searchTerm)) ||
                    (track.artist && track.artist.toLowerCase().includes(searchTerm)) ||
                    (track.album && track.album.toLowerCase().includes(searchTerm)) ||
                    (track.genre && track.genre.toLowerCase().includes(searchTerm));

                // Metadata filters
                const artistMatch = !artistFilter || track.artist === artistFilter;
                const albumMatch = !albumFilter || track.album === albumFilter;
                const genreMatch = !genreFilter || track.genre === genreFilter;
                const yearMatch = !yearFilter || track.year == yearFilter;

                // Duration filter
                let durationMatch = true;
                if (durationFilter && track.duration) {
                    const duration = track.duration;
                    switch (durationFilter) {
                        case 'short':
                            durationMatch = duration < 120; // < 2 minutes
                            break;
                        case 'medium':
                            durationMatch = duration >= 120 && duration <= 300; // 2-5 minutes
                            break;
                        case 'long':
                            durationMatch = duration > 300; // > 5 minutes
                            break;
                    }
                }

                return searchMatch && artistMatch && albumMatch && genreMatch && yearMatch && durationMatch;
            });

            displayTracks(filteredTracks);
            updateActiveFilters();

            // Update track count
            const trackCount = document.getElementById('track-count');
            if (trackCount) {
                trackCount.textContent = filteredTracks.length + (filteredTracks.length !== allTracks.length ? ` of ${allTracks.length}` : '');
            }
        }

        function updateActiveFilters() {
            const activeFilters = [];
            const searchTerm = document.getElementById('search').value;
            const artistFilter = document.getElementById('filter-artist').value;
            const albumFilter = document.getElementById('filter-album').value;
            const genreFilter = document.getElementById('filter-genre').value;
            const yearFilter = document.getElementById('filter-year').value;
            const durationFilter = document.getElementById('filter-duration').value;

            if (searchTerm) activeFilters.push({ type: 'search', value: searchTerm, label: `Search: "${searchTerm}"` });
            if (artistFilter) activeFilters.push({ type: 'artist', value: artistFilter, label: `Artist: ${artistFilter}` });
            if (albumFilter) activeFilters.push({ type: 'album', value: albumFilter, label: `Album: ${albumFilter}` });
            if (genreFilter) activeFilters.push({ type: 'genre', value: genreFilter, label: `Genre: ${genreFilter}` });
            if (yearFilter) activeFilters.push({ type: 'year', value: yearFilter, label: `Year: ${yearFilter}` });
            if (durationFilter) activeFilters.push({ type: 'duration', value: durationFilter, label: `Duration: ${durationFilter}` });

            const activeFiltersContainer = document.getElementById('active-filters');
            const activeFiltersList = document.getElementById('active-filters-list');

            if (activeFilters.length > 0) {
                activeFiltersContainer.classList.remove('hidden');
                activeFiltersList.innerHTML = activeFilters.map(filter => `
                    <span class="inline-flex items-center px-2 py-1 rounded-full text-xs bg-yellow-500 text-black">
                        ${filter.label}
                        <button onclick="removeFilter('${filter.type}', '${filter.value}')" class="ml-1 hover:text-gray-700">×</button>
                    </span>
                `).join('');
            } else {
                activeFiltersContainer.classList.add('hidden');
            }
        }

        function removeFilter(type, value) {
            switch (type) {
                case 'search':
                    document.getElementById('search').value = '';
                    break;
                case 'artist':
                    document.getElementById('filter-artist').value = '';
                    break;
                case 'album':
                    document.getElementById('filter-album').value = '';
                    break;
                case 'genre':
                    document.getElementById('filter-genre').value = '';
                    break;
                case 'year':
                    document.getElementById('filter-year').value = '';
                    break;
                case 'duration':
                    document.getElementById('filter-duration').value = '';
                    break;
            }
            applyFilters();
        }

        function clearAllFilters() {
            document.getElementById('search').value = '';
            document.getElementById('filter-artist').value = '';
            document.getElementById('filter-album').value = '';
            document.getElementById('filter-genre').value = '';
            document.getElementById('filter-year').value = '';
            document.getElementById('filter-duration').value = '';
            applyFilters();
        }

        function downloadTrack(trackId, title) {
            const link = document.createElement('a');
            link.href = '/api/music/download/' + trackId;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            
            // Show notification
            showNotification('Downloading: ' + title);
        }

        function showNotification(message) {
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-yellow-500 text-black px-4 py-2 rounded-lg shadow-lg z-50';
            notification.textContent = message;
            document.body.appendChild(notification);
            
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }

        function escapeHtml(text) {
            if (!text) return '';
            const div = document.createElement('div');
            div.textContent = text;
            return div.innerHTML;
        }





        // Audio Player Controls
        const audioPlayer = document.getElementById('audio-player');
        const playPauseBtn = document.getElementById('play-pause');
        const skipBackwardBtn = document.getElementById('skip-backward');
        const skipForwardBtn = document.getElementById('skip-forward');
        const progressContainer = document.getElementById('progress-container');
        const progressBar = document.getElementById('progress-bar');
        const currentTimeSpan = document.getElementById('current-time');
        const totalTimeSpan = document.getElementById('total-time');

        // Mini Player Controls
        const miniPlayPauseBtn = document.getElementById('mini-play-pause');
        const miniSkipBackwardBtn = document.getElementById('mini-skip-backward');
        const miniSkipForwardBtn = document.getElementById('mini-skip-forward');
        const miniProgressContainer = document.getElementById('mini-progress-container');
        const miniProgressBar = document.getElementById('mini-progress-bar');
        const miniCurrentTimeSpan = document.getElementById('mini-current-time');
        const miniTotalTimeSpan = document.getElementById('mini-total-time');
        const closeMiniPlayerBtn = document.getElementById('close-mini-player');

        // Play/Pause button
        playPauseBtn.addEventListener('click', function() {
            if (audioPlayer.paused) {
                audioPlayer.play();
            } else {
                audioPlayer.pause();
            }
        });

        // Skip backward 15 seconds
        skipBackwardBtn.addEventListener('click', function() {
            audioPlayer.currentTime = Math.max(0, audioPlayer.currentTime - 15);
        });

        // Skip forward 15 seconds
        skipForwardBtn.addEventListener('click', function() {
            audioPlayer.currentTime = Math.min(audioPlayer.duration, audioPlayer.currentTime + 15);
        });

        // Progress bar click
        progressContainer.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            audioPlayer.currentTime = percent * audioPlayer.duration;
        });

        // Mini Player Controls
        miniPlayPauseBtn.addEventListener('click', function() {
            playPauseBtn.click(); // Reuse main play/pause logic
        });

        miniSkipBackwardBtn.addEventListener('click', function() {
            skipBackwardBtn.click(); // Reuse main skip backward logic
        });

        miniSkipForwardBtn.addEventListener('click', function() {
            skipForwardBtn.click(); // Reuse main skip forward logic
        });

        // Mini progress bar click
        miniProgressContainer.addEventListener('click', function(e) {
            const rect = this.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            audioPlayer.currentTime = percent * audioPlayer.duration;
        });

        // Close mini player
        closeMiniPlayerBtn.addEventListener('click', function() {
            hideMiniPlayer();
        });

        // Audio events
        audioPlayer.addEventListener('play', () => {
            updatePlayPauseButton(true);
            updateMiniPlayPauseButton(true);
        });

        audioPlayer.addEventListener('pause', () => {
            updatePlayPauseButton(false);
            updateMiniPlayPauseButton(false);
        });

        audioPlayer.addEventListener('ended', () => {
            updatePlayPauseButton(false);
            updateMiniPlayPauseButton(false);
        });

        audioPlayer.addEventListener('timeupdate', function() {
            if (audioPlayer.duration) {
                const percent = (audioPlayer.currentTime / audioPlayer.duration) * 100;
                const currentTimeFormatted = formatTime(audioPlayer.currentTime);

                // Update main player
                progressBar.style.width = percent + '%';
                currentTimeSpan.textContent = currentTimeFormatted;

                // Update mini player
                miniProgressBar.style.width = percent + '%';
                miniCurrentTimeSpan.textContent = currentTimeFormatted;
            }
        });

        audioPlayer.addEventListener('loadedmetadata', function() {
            const totalTimeFormatted = formatTime(audioPlayer.duration);

            // Update main player
            totalTimeSpan.textContent = totalTimeFormatted;

            // Update mini player
            miniTotalTimeSpan.textContent = totalTimeFormatted;
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT') return; // Don't interfere with search input

            switch(e.code) {
                case 'Space':
                    e.preventDefault();
                    playPauseBtn.click();
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    skipBackwardBtn.click();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    skipForwardBtn.click();
                    break;
            }
        });

        // Sync button
        document.getElementById('sync-btn').addEventListener('click', function() {
            const btn = this;
            btn.disabled = true;
            btn.textContent = 'Syncing...';

            fetch('/api/music/sync', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification('Synced ' + data.synced_count + ' tracks!');
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Sync failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                showNotification('Sync failed: ' + error.message);
            })
            .finally(() => {
                btn.disabled = false;
                btn.textContent = 'Sync Music';
            });
        });

        // Filter and Search Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle filters panel
            document.getElementById('toggle-filters').addEventListener('click', function() {
                const panel = document.getElementById('filters-panel');
                const toggleText = document.getElementById('filter-toggle-text');
                const toggleIcon = document.getElementById('filter-toggle-icon');

                if (panel.classList.contains('hidden')) {
                    panel.classList.remove('hidden');
                    toggleText.textContent = 'Hide Filters';
                    toggleIcon.textContent = '▲';
                } else {
                    panel.classList.add('hidden');
                    toggleText.textContent = 'Show Filters';
                    toggleIcon.textContent = '▼';
                }
            });

            // Search input
            document.getElementById('search').addEventListener('input', function() {
                applyFilters();
            });

            // Filter dropdowns
            document.getElementById('filter-artist').addEventListener('change', applyFilters);
            document.getElementById('filter-album').addEventListener('change', applyFilters);
            document.getElementById('filter-genre').addEventListener('change', applyFilters);
            document.getElementById('filter-year').addEventListener('change', applyFilters);
            document.getElementById('filter-duration').addEventListener('change', applyFilters);

            // Clear filters button
            document.getElementById('clear-filters').addEventListener('click', clearAllFilters);
        });
    </script>
</body>
</html>
<?php /**PATH /home/elder/Apps/audio_app/resources/views/music-working.blade.php ENDPATH**/ ?>