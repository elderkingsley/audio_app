<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Track Metadata Management - TEC Sunday School</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-white">
    <div class="container mx-auto px-4 py-8">
        <div class="mb-8">
            <h1 class="text-3xl font-bold text-yellow-400 mb-2">Track Metadata Management</h1>
            <p class="text-gray-400">Edit metadata for all tracks in your library</p>
        </div>

        <!-- Bulk Actions -->
        <div class="bg-gray-800 rounded-lg p-6 mb-8">
            <h2 class="text-xl font-semibold mb-4">Bulk Actions</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-2">Set Artist for All</label>
                    <input type="text" id="bulk-artist" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2" placeholder="TEC Sunday School">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Set Album for All</label>
                    <input type="text" id="bulk-album" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2" placeholder="Westminster Shorter Catechism">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-2">Set Genre for All</label>
                    <input type="text" id="bulk-genre" class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2" placeholder="Religious Education">
                </div>
                <div class="flex items-end">
                    <button onclick="applyBulkChanges()" class="bg-yellow-400 hover:bg-yellow-500 text-black px-6 py-2 rounded font-medium">
                        Apply to All Tracks
                    </button>
                </div>
            </div>
        </div>

        <!-- Individual Track Editing -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-xl font-semibold mb-4">Individual Track Metadata</h2>
            <div id="tracks-container">
                <!-- Tracks will be loaded here -->
            </div>
        </div>
    </div>

    <script>
        // Load tracks on page load
        document.addEventListener('DOMContentLoaded', loadTracks);

        async function loadTracks() {
            try {
                const response = await fetch('/api/music/tracks');
                const data = await response.json();
                displayTracks(data.data);
            } catch (error) {
                console.error('Failed to load tracks:', error);
            }
        }

        function displayTracks(tracks) {
            const container = document.getElementById('tracks-container');
            container.innerHTML = tracks.map(track => `
                <div class="border border-gray-700 rounded-lg p-4 mb-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium mb-1">Title</label>
                            <input type="text" value="${track.title || ''}" 
                                   onchange="updateTrack(${track.id}, 'title', this.value)"
                                   class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Artist</label>
                            <input type="text" value="${track.artist || ''}" 
                                   onchange="updateTrack(${track.id}, 'artist', this.value)"
                                   class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Album</label>
                            <input type="text" value="${track.album || ''}" 
                                   onchange="updateTrack(${track.id}, 'album', this.value)"
                                   class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-1">Genre</label>
                            <input type="text" value="${track.genre || ''}" 
                                   onchange="updateTrack(${track.id}, 'genre', this.value)"
                                   class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium mb-1">Comment</label>
                            <input type="text" value="${track.comment || ''}" 
                                   onchange="updateTrack(${track.id}, 'comment', this.value)"
                                   class="w-full bg-gray-700 border border-gray-600 rounded px-3 py-2">
                        </div>
                    </div>
                    <div class="mt-2 text-sm text-gray-400">
                        File: ${track.file_name} • Duration: ${track.formatted_duration}
                    </div>
                </div>
            `).join('');
        }

        async function updateTrack(trackId, field, value) {
            try {
                const response = await fetch(`/api/music/tracks/${trackId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ [field]: value })
                });

                if (response.ok) {
                    console.log(`Updated ${field} for track ${trackId}`);
                } else {
                    console.error('Failed to update track');
                }
            } catch (error) {
                console.error('Error updating track:', error);
            }
        }

        async function applyBulkChanges() {
            const artist = document.getElementById('bulk-artist').value;
            const album = document.getElementById('bulk-album').value;
            const genre = document.getElementById('bulk-genre').value;

            if (!artist && !album && !genre) {
                alert('Please enter at least one field to update');
                return;
            }

            try {
                const response = await fetch('/api/music/tracks/bulk-update', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ artist, album, genre })
                });

                if (response.ok) {
                    alert('Bulk update completed successfully!');
                    loadTracks(); // Reload tracks
                } else {
                    alert('Failed to perform bulk update');
                }
            } catch (error) {
                console.error('Error performing bulk update:', error);
                alert('Error performing bulk update');
            }
        }
    </script>
</body>
</html>
