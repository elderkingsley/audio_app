<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>Music Player</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body { 
            background: #000000; 
            color: #ffffff; 
        }
        .yellow-accent { 
            color: #fbbf24; 
        }
        .yellow-bg { 
            background: #fbbf24; 
            color: #000000; 
        }
        .glass-effect {
            background: rgba(0, 0, 0, 0.8);
            border: 1px solid rgba(251, 191, 36, 0.3);
            backdrop-filter: blur(10px);
        }
    </style>
</head>
<body class="min-h-screen">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <header class="mb-8">
            <div class="flex items-center justify-between">
                <h1 class="text-3xl font-bold yellow-accent">🎵 Music Player</h1>
                <button id="sync-btn" class="yellow-bg px-6 py-2 rounded-lg font-medium">
                    Sync Music
                </button>
            </div>
        </header>

        <!-- Search -->
        <div class="glass-effect rounded-lg p-6 mb-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <input 
                    type="text" 
                    id="search" 
                    placeholder="Search music..."
                    class="bg-gray-800 border border-yellow-500 rounded px-4 py-2 text-white"
                >
                <select id="genre-filter" class="bg-gray-800 border border-yellow-500 rounded px-4 py-2 text-white">
                    <option value="">All Genres</option>
                </select>
                <select id="sort-by" class="bg-gray-800 border border-yellow-500 rounded px-4 py-2 text-white">
                    <option value="title">Title</option>
                    <option value="artist">Artist</option>
                </select>
            </div>
        </div>

        <!-- Player Controls -->
        <div id="player-controls" class="glass-effect rounded-lg p-6 mb-8 hidden">
            <div class="flex flex-col space-y-4">
                <!-- Main Controls -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2 sm:space-x-4">
                        <button id="prev-btn" class="text-yellow-500 hover:text-yellow-400 p-2 rounded-full hover:bg-yellow-500 hover:bg-opacity-20 transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M8.445 14.832A1 1 0 0010 14v-2.798l5.445 3.63A1 1 0 0017 14V6a1 1 0 00-1.555-.832L10 8.798V6a1 1 0 00-1.555-.832l-6 4a1 1 0 000 1.664l6 4z"></path>
                            </svg>
                        </button>

                        <button id="skip-backward-btn" class="text-yellow-500 hover:text-yellow-400 p-2 rounded-full hover:bg-yellow-500 hover:bg-opacity-20 transition-all" title="Skip back 15s">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs absolute -bottom-1 left-1/2 transform -translate-x-1/2">15s</span>
                        </button>

                        <button id="play-pause-btn" class="yellow-bg px-6 py-3 rounded-full font-bold text-lg hover:bg-yellow-600 transition-all shadow-lg">
                            <span id="play-icon">▶️</span>
                            <span id="pause-icon" class="hidden">⏸️</span>
                        </button>

                        <button id="skip-forward-btn" class="text-yellow-500 hover:text-yellow-400 p-2 rounded-full hover:bg-yellow-500 hover:bg-opacity-20 transition-all relative" title="Skip forward 15s">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-8.293l-3-3a1 1 0 00-1.414 1.414L10.586 9H7a1 1 0 100 2h3.586l-1.293 1.293a1 1 0 101.414 1.414l3-3a1 1 0 000-1.414z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-xs absolute -bottom-1 left-1/2 transform -translate-x-1/2">15s</span>
                        </button>

                        <button id="next-btn" class="text-yellow-500 hover:text-yellow-400 p-2 rounded-full hover:bg-yellow-500 hover:bg-opacity-20 transition-all">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M11.555 5.168A1 1 0 0010 6v2.798L4.555 5.168A1 1 0 003 6v8a1 1 0 001.555.832L10 11.202V14a1 1 0 001.555.832l6-4a1 1 0 000-1.664l-6-4z"></path>
                            </svg>
                        </button>

                        <button id="download-btn" class="text-yellow-500 hover:text-yellow-400 p-2 rounded-full hover:bg-yellow-500 hover:bg-opacity-20 transition-all" title="Download current track">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                            </svg>
                        </button>
                    </div>
                <div class="flex-1 mx-8">
                    <div class="text-center mb-2">
                        <div id="current-track-title" class="font-semibold yellow-accent">No track selected</div>
                        <div id="current-track-artist" class="text-sm text-gray-400"></div>
                    </div>
                    <div class="flex items-center space-x-2">
                        <span id="current-time" class="text-sm">0:00</span>
                        <div class="flex-1 bg-gray-700 rounded-full h-2">
                            <div id="progress-bar" class="bg-yellow-500 h-2 rounded-full" style="width: 0%"></div>
                        </div>
                        <span id="total-time" class="text-sm">0:00</span>
                    </div>
                </div>
                <input type="range" id="volume-slider" min="0" max="100" value="50" class="w-20">
            </div>
        </div>

        <!-- Track List -->
        <div class="glass-effect rounded-lg">
            <div class="p-4 border-b border-yellow-500 border-opacity-30">
                <h2 class="text-xl font-semibold yellow-accent">Music Library</h2>
                <p class="text-sm text-gray-400">
                    <span id="track-count">0</span> tracks
                </p>
            </div>
            <div id="track-list">
                <!-- Tracks will be loaded here by JavaScript -->
            </div>
            <div id="loading" class="p-8 text-center text-gray-400">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-yellow-500 mb-4"></div>
                <p>Loading tracks...</p>
            </div>
            <div id="no-tracks" class="p-8 text-center text-gray-400 hidden">
                <div class="text-yellow-500 text-6xl mb-4">🎵</div>
                <p class="text-lg mb-2">No tracks found</p>
                <p class="text-sm">Click "Sync Music" to load tracks from Bunny.net storage.</p>
            </div>
        </div>
    </div>

    <!-- Audio Element -->
    <audio id="audio-player" preload="metadata"></audio>

    <!-- JavaScript -->
    <script>
        // Load tracks when page loads
        fetch('/api/music/tracks')
            .then(response => response.json())
            .then(data => {
                const tracks = data.data || [];
                const trackList = document.getElementById('track-list');
                const trackCount = document.getElementById('track-count');
                const loading = document.getElementById('loading');

                // Hide loading
                if (loading) loading.classList.add('hidden');

                // Update track count
                if (trackCount) trackCount.textContent = tracks.length;

                if (tracks.length === 0) {
                    trackList.innerHTML = '<div class="p-4 text-white">No tracks found. Click "Sync Music" to load tracks.</div>';
                    return;
                }

                // Create track HTML
                let html = '';
                tracks.forEach(track => {
                    html += '<div class="track-item px-4 py-3 border-b border-gray-600 hover:bg-gray-700 cursor-pointer transition-colors" onclick="playTrack(\'' + track.streaming_url + '\', \'' + track.title + '\', \'' + (track.artist || 'Unknown Artist') + '\')">';
                    html += '<div class="flex items-center justify-between">';
                    html += '<div class="flex-1">';
                    html += '<div class="text-white font-medium">' + (track.title || 'Unknown Title') + '</div>';
                    html += '<div class="text-gray-400 text-sm">' + (track.artist || 'Unknown Artist') + '</div>';
                    html += '</div>';
                    html += '<div class="flex items-center space-x-4 text-sm text-gray-400">';
                    html += '<span>' + (track.formatted_file_size || '—') + '</span>';
                    html += '<button class="text-yellow-500 hover:text-yellow-400 p-1" onclick="event.stopPropagation(); downloadTrack(' + track.id + ', \'' + track.title + '\')" title="Download">⬇️</button>';
                    html += '</div>';
                    html += '</div>';
                    html += '</div>';
                });

                trackList.innerHTML = html;
            })
            .catch(error => {
                console.error('Error loading tracks:', error);
                document.getElementById('track-list').innerHTML = '<div class="p-4 text-red-500">Error loading tracks: ' + error.message + '</div>';
            });

        // Play track function
        function playTrack(url, title, artist) {
            let audio = document.getElementById('audio-player');
            if (!audio) {
                audio = document.createElement('audio');
                audio.id = 'audio-player';
                audio.controls = true;
                audio.className = 'w-full mt-4';
                document.querySelector('.glass-effect').appendChild(audio);
            }

            audio.src = url;
            audio.load();
            audio.play().then(() => {
                showNotification('Now playing: ' + title + ' by ' + artist);
            }).catch(error => {
                showNotification('Failed to play: ' + title);
            });
        }

        // Download track function
        function downloadTrack(trackId, title) {
            const link = document.createElement('a');
            link.href = '/api/music/download/' + trackId;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
            showNotification('Downloading: ' + title);
        }

        // Show notification function
        function showNotification(message) {
            const existing = document.getElementById('notification');
            if (existing) existing.remove();

            const notification = document.createElement('div');
            notification.id = 'notification';
            notification.className = 'fixed top-4 right-4 bg-yellow-500 text-black px-4 py-2 rounded-lg shadow-lg z-50';
            notification.textContent = message;
            document.body.appendChild(notification);

            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 3000);
        }

        // Sync button functionality
        document.addEventListener('DOMContentLoaded', function() {
            const syncBtn = document.getElementById('sync-btn');
            if (syncBtn) {
                syncBtn.addEventListener('click', function() {
                    syncBtn.disabled = true;
                    syncBtn.textContent = 'Syncing...';

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
                            showNotification('Synced ' + data.synced_count + ' tracks successfully!');
                            setTimeout(() => location.reload(), 1000);
                        } else {
                            showNotification('Sync failed: ' + (data.message || 'Unknown error'));
                        }
                    })
                    .catch(error => {
                        showNotification('Sync failed: ' + error.message);
                    })
                    .finally(() => {
                        syncBtn.disabled = false;
                        syncBtn.textContent = 'Sync Music';
                    });
                });
            }
        });
    </script>
</body>
</html>
<?php /**PATH /home/elder/Apps/audio_app/resources/views/music-player/simple.blade.php ENDPATH**/ ?>