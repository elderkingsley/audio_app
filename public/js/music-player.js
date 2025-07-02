class MusicPlayer {
    constructor() {
        console.log('MusicPlayer constructor called');
        this.audio = document.getElementById('audio-player');
        this.tracks = [];
        this.currentTrackIndex = -1;
        this.isPlaying = false;
        this.currentFilters = {
            search: '',
            genre: '',
            sortBy: 'title',
            sortOrder: 'asc'
        };

        console.log('Initializing elements...');
        this.initializeElements();
        console.log('Binding events...');
        this.bindEvents();
        console.log('Loading tracks...');
        this.loadTracks();
        console.log('Loading genres...');
        this.loadGenres();
        console.log('MusicPlayer constructor completed');
    }

    initializeElements() {
        console.log('initializeElements called');
        // Desktop Control elements
        this.playPauseBtn = document.getElementById('play-pause-btn');
        this.prevBtn = document.getElementById('prev-btn');
        this.nextBtn = document.getElementById('next-btn');
        this.skipForwardBtn = document.getElementById('skip-forward-btn');
        this.skipBackwardBtn = document.getElementById('skip-backward-btn');
        this.downloadBtn = document.getElementById('download-btn');
        this.syncBtn = document.getElementById('sync-btn');

        // Check critical elements
        if (!this.syncBtn) console.error('sync-btn not found');
        if (!this.trackList) console.error('track-list not found');

        // Mobile Control elements
        this.mobilePlayPauseBtn = document.getElementById('mobile-play-pause-btn');
        this.mobilePrevBtn = document.getElementById('mobile-prev-btn');
        this.mobileNextBtn = document.getElementById('mobile-next-btn');
        this.mobileMinimizeBtn = document.getElementById('mobile-minimize-btn');

        // Display elements
        this.trackList = document.getElementById('track-list');
        this.currentTrackTitle = document.getElementById('current-track-title');
        this.currentTrackArtist = document.getElementById('current-track-artist');
        this.currentTime = document.getElementById('current-time');
        this.totalTime = document.getElementById('total-time');
        this.progressBar = document.getElementById('progress-bar');
        this.trackCount = document.getElementById('track-count');

        // Check critical elements
        if (!this.trackList) console.error('track-list not found');
        if (!this.trackCount) console.error('track-count not found');

        // Now Playing Panel elements
        this.nowPlayingPanel = document.getElementById('now-playing-panel');
        this.nowPlayingTitle = document.getElementById('now-playing-title');
        this.nowPlayingArtist = document.getElementById('now-playing-artist');
        this.nowPlayingCover = document.getElementById('now-playing-cover');
        this.nowPlayingCoverPlaceholder = document.getElementById('now-playing-cover-placeholder');
        this.closeNowPlayingBtn = document.getElementById('close-now-playing');
        this.nowPlayingSkipBackward = document.getElementById('now-playing-skip-backward');
        this.nowPlayingSkipForward = document.getElementById('now-playing-skip-forward');

        // Mobile Display elements
        this.mobileCurrentTrackTitle = document.getElementById('mobile-current-track-title');
        this.mobileCurrentTrackArtist = document.getElementById('mobile-current-track-artist');
        this.mobileCurrentTime = document.getElementById('mobile-current-time');
        this.mobileTotalTime = document.getElementById('mobile-total-time');
        this.mobileProgressBar = document.getElementById('mobile-progress-bar');

        // Control elements
        this.volumeSlider = document.getElementById('volume-slider');
        this.searchInput = document.getElementById('search');
        this.genreFilter = document.getElementById('genre-filter');
        this.sortBy = document.getElementById('sort-by');

        // Icons
        this.playIcon = document.getElementById('play-icon');
        this.pauseIcon = document.getElementById('pause-icon');
        this.mobilePlayIcon = document.getElementById('mobile-play-icon');
        this.mobilePauseIcon = document.getElementById('mobile-pause-icon');

        // Containers
        this.playerControls = document.getElementById('player-controls');
        this.mobilePlayerControls = document.getElementById('mobile-player-controls');
        this.loading = document.getElementById('loading');
        this.noTracks = document.getElementById('no-tracks');



        // Check if mobile
        this.isMobile = window.innerWidth <= 768;

        // Initialize Now Playing panel
        this.setupNowPlayingPanel();
    }

    setupNowPlayingPanel() {
        // Close button event listener
        if (this.closeNowPlayingBtn) {
            this.closeNowPlayingBtn.addEventListener('click', () => {
                this.hideNowPlayingPanel();
            });
        }

        // Skip backward button event listener
        if (this.nowPlayingSkipBackward) {
            this.nowPlayingSkipBackward.addEventListener('click', () => {
                this.skipBackward15();
            });
        }

        // Skip forward button event listener
        if (this.nowPlayingSkipForward) {
            this.nowPlayingSkipForward.addEventListener('click', () => {
                this.skipForward15();
            });
        }

        // Keyboard shortcuts for skip functionality
        document.addEventListener('keydown', (e) => {
            // Only activate when Now Playing panel is visible and no input is focused
            if (this.nowPlayingPanel && !this.nowPlayingPanel.classList.contains('hidden') &&
                !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {

                if (e.key === 'ArrowLeft' || e.key === 'j') {
                    e.preventDefault();
                    this.skipBackward15();
                } else if (e.key === 'ArrowRight' || e.key === 'l') {
                    e.preventDefault();
                    this.skipForward15();
                }
            }
        });
    }

    showNowPlayingPanel() {
        if (this.nowPlayingPanel) {
            this.nowPlayingPanel.classList.remove('hidden');
            // Small delay to ensure the element is rendered before animation
            setTimeout(() => {
                this.nowPlayingPanel.classList.add('show');
            }, 10);
        }
    }

    hideNowPlayingPanel() {
        if (this.nowPlayingPanel) {
            this.nowPlayingPanel.classList.remove('show');
            // Wait for animation to complete before hiding
            setTimeout(() => {
                this.nowPlayingPanel.classList.add('hidden');
            }, 500);
        }
    }

    updateNowPlayingPanel(track) {
        if (!this.nowPlayingPanel) return;

        // Update track info
        if (this.nowPlayingTitle) {
            this.nowPlayingTitle.textContent = track.title || 'Unknown Title';
        }
        if (this.nowPlayingArtist) {
            this.nowPlayingArtist.textContent = track.artist || 'Unknown Artist';
        }

        // Update cover art
        if (track.cover_art_url) {
            if (this.nowPlayingCover) {
                this.nowPlayingCover.src = track.cover_art_url;
                this.nowPlayingCover.classList.remove('hidden');
                this.nowPlayingCover.onerror = () => {
                    this.nowPlayingCover.classList.add('hidden');
                    if (this.nowPlayingCoverPlaceholder) {
                        this.nowPlayingCoverPlaceholder.style.display = 'flex';
                    }
                };
            }
            if (this.nowPlayingCoverPlaceholder) {
                this.nowPlayingCoverPlaceholder.style.display = 'none';
            }
        } else {
            if (this.nowPlayingCover) {
                this.nowPlayingCover.classList.add('hidden');
            }
            if (this.nowPlayingCoverPlaceholder) {
                this.nowPlayingCoverPlaceholder.style.display = 'flex';
            }
        }

        // Show the panel
        this.showNowPlayingPanel();
    }

    skipBackward15() {
        const audio = document.getElementById('audio-player');
        if (audio && !audio.paused) {
            const newTime = Math.max(0, audio.currentTime - 15);
            audio.currentTime = newTime;
            console.log(`Skipped backward 15 seconds to ${newTime.toFixed(1)}s`);

            // Show visual feedback
            this.showSkipFeedback('backward');
        }
    }

    skipForward15() {
        const audio = document.getElementById('audio-player');
        if (audio && !audio.paused) {
            const newTime = Math.min(audio.duration || audio.currentTime + 15, audio.currentTime + 15);
            audio.currentTime = newTime;
            console.log(`Skipped forward 15 seconds to ${newTime.toFixed(1)}s`);

            // Show visual feedback
            this.showSkipFeedback('forward');
        }
    }

    showSkipFeedback(direction) {
        // Create a temporary feedback element
        const feedback = document.createElement('div');
        feedback.className = 'fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gradient-to-r from-yellow-400 to-yellow-500 text-black px-6 py-3 rounded-xl font-bold text-lg shadow-lg z-50 transition-all duration-300 border-2 border-black border-opacity-20';

        // Add icon and text
        const icon = direction === 'forward' ? '⏭️' : '⏮️';
        const text = direction === 'forward' ? '+15s' : '-15s';
        feedback.innerHTML = `<div class="flex items-center space-x-2"><span>${icon}</span><span>${text}</span></div>`;
        feedback.style.opacity = '0';
        feedback.style.transform = 'translate(-50%, -50%) scale(0.8)';

        document.body.appendChild(feedback);

        // Animate in
        setTimeout(() => {
            feedback.style.opacity = '1';
            feedback.style.transform = 'translate(-50%, -50%) scale(1.1)';
        }, 10);

        // Scale back to normal
        setTimeout(() => {
            feedback.style.transform = 'translate(-50%, -50%) scale(1)';
        }, 150);

        // Animate out and remove
        setTimeout(() => {
            feedback.style.opacity = '0';
            feedback.style.transform = 'translate(-50%, -50%) scale(0.8)';
            setTimeout(() => {
                if (feedback.parentNode) {
                    feedback.parentNode.removeChild(feedback);
                }
            }, 300);
        }, 1200);
    }

    bindEvents() {
        // Desktop Player controls
        this.playPauseBtn?.addEventListener('click', () => this.togglePlayPause());
        this.prevBtn?.addEventListener('click', () => this.previousTrack());
        this.nextBtn?.addEventListener('click', () => this.nextTrack());
        this.skipForwardBtn?.addEventListener('click', () => this.skipForward());
        this.skipBackwardBtn?.addEventListener('click', () => this.skipBackward());
        this.downloadBtn?.addEventListener('click', () => this.downloadTrack());

        // Mobile Player controls
        this.mobilePlayPauseBtn?.addEventListener('click', () => this.togglePlayPause());
        this.mobilePrevBtn?.addEventListener('click', () => this.previousTrack());
        this.mobileNextBtn?.addEventListener('click', () => this.nextTrack());
        this.mobileMinimizeBtn?.addEventListener('click', () => this.minimizeMobilePlayer());

        // Sync button
        this.syncBtn.addEventListener('click', () => this.syncTracks());

        // Audio events
        this.audio.addEventListener('loadedmetadata', () => this.updateTotalTime());
        this.audio.addEventListener('timeupdate', () => this.updateProgress());
        this.audio.addEventListener('ended', () => this.nextTrack());
        this.audio.addEventListener('error', (e) => this.handleAudioError(e));

        // Volume control
        this.volumeSlider?.addEventListener('input', (e) => {
            this.audio.volume = e.target.value / 100;
        });

        // Search and filters
        this.searchInput.addEventListener('input', (e) => {
            this.currentFilters.search = e.target.value;
            this.debounceLoadTracks();
        });

        this.genreFilter.addEventListener('change', (e) => {
            this.currentFilters.genre = e.target.value;
            this.loadTracks();
        });

        this.sortBy.addEventListener('change', (e) => {
            this.currentFilters.sortBy = e.target.value;
            this.loadTracks();
        });

        // Progress bar click (desktop)
        this.progressBar?.parentElement.addEventListener('click', (e) => {
            const rect = e.currentTarget.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            this.audio.currentTime = percent * this.audio.duration;
        });

        // Progress bar click (mobile)
        this.mobileProgressBar?.parentElement.addEventListener('click', (e) => {
            const rect = e.currentTarget.getBoundingClientRect();
            const percent = (e.clientX - rect.left) / rect.width;
            this.audio.currentTime = percent * this.audio.duration;
        });

        // Window resize handler
        window.addEventListener('resize', () => {
            this.isMobile = window.innerWidth <= 768;
            this.updatePlayerVisibility();
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            // Only handle shortcuts when not typing in input fields
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'SELECT') return;

            switch(e.key) {
                case ' ':
                    e.preventDefault();
                    this.togglePlayPause();
                    break;
                case 'ArrowLeft':
                    e.preventDefault();
                    this.skipBackward();
                    break;
                case 'ArrowRight':
                    e.preventDefault();
                    this.skipForward();
                    break;
                case 'ArrowUp':
                    e.preventDefault();
                    this.previousTrack();
                    break;
                case 'ArrowDown':
                    e.preventDefault();
                    this.nextTrack();
                    break;
            }
        });
    }

    async loadTracks() {
        console.log('loadTracks called');
        this.showLoading(true);

        try {
            const params = new URLSearchParams(this.currentFilters);
            const url = `/api/music/tracks?${params}`;
            console.log('Fetching tracks from:', url);

            const response = await fetch(url);

            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }

            const data = await response.json();
            console.log('API response:', data);
            this.tracks = data.data || [];
            console.log('Tracks loaded:', this.tracks.length);

            this.renderTracks();
            this.updateTrackCount();

        } catch (error) {
            console.error('Failed to load tracks:', error);
            this.showError('Failed to load tracks: ' + error.message);
        } finally {
            this.showLoading(false);
        }
    }

    async loadGenres() {
        try {
            const response = await fetch('/api/music/genres');
            const genres = await response.json();
            
            this.genreFilter.innerHTML = '<option value="">All Genres</option>';
            genres.forEach(genre => {
                const option = document.createElement('option');
                option.value = genre;
                option.textContent = genre;
                this.genreFilter.appendChild(option);
            });
        } catch (error) {
            console.error('Failed to load genres:', error);
        }
    }

    renderTracks() {
        if (this.tracks.length === 0) {
            this.trackList.innerHTML = '';
            this.noTracks.classList.remove('hidden');
            return;
        }

        this.noTracks.classList.add('hidden');

        // Create track HTML
        let tracksHTML = '';
        for (let i = 0; i < this.tracks.length; i++) {
            const track = this.tracks[i];

            // Create cover art HTML
            const coverArtHTML = track.cover_art_url
                ? `<img src="${track.cover_art_url}" alt="Cover art for ${track.title || 'Unknown Title'}" class="w-12 h-12 rounded-lg object-cover shadow-md" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">`
                : '';

            const placeholderHTML = `
                <div class="w-12 h-12 rounded-lg cover-art-placeholder flex items-center justify-center shadow-md ${track.cover_art_url ? 'hidden' : ''}" style="display: ${track.cover_art_url ? 'none' : 'flex'};">
                    <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" clip-rule="evenodd"></path>
                    </svg>
                </div>
            `;

            tracksHTML += `
                <div class="track-item px-4 py-3 cursor-pointer hover:bg-gray-700 transition-colors border-b border-gray-600" data-index="${i}">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3 flex-1">
                            <div class="relative flex-shrink-0 cover-art-container">
                                ${coverArtHTML}
                                ${placeholderHTML}
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="font-medium text-white truncate">${track.title || 'Unknown Title'}</div>
                                <div class="text-sm text-gray-400 truncate">${track.artist || 'Unknown Artist'}</div>
                                <div class="flex items-center justify-between mt-1">
                                    <div class="text-xs text-yellow-400 truncate">${track.comment || 'No description'}</div>
                                    <div class="text-xs text-blue-400 ml-2 flex-shrink-0">${track.formatted_duration || '--:--'}</div>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4 text-sm text-gray-400 flex-shrink-0">
                            <span>${track.formatted_file_size || '—'}</span>
                            <button class="download-track-btn bg-yellow-400 hover:bg-yellow-500 text-black p-2 rounded-lg transition-all duration-200 hover:scale-105"
                                    data-track-index="${i}"
                                    title="Download ${track.title || 'track'}">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;
        }

        this.trackList.innerHTML = tracksHTML;

        // Bind track click events
        this.trackList.querySelectorAll('.track-item').forEach((item, index) => {
            item.addEventListener('click', (e) => {
                // Don't play track if download button was clicked
                if (!e.target.closest('.download-track-btn')) {
                    this.playTrack(index);
                }
            });
        });

        // Bind download button events
        this.trackList.querySelectorAll('.download-track-btn').forEach((btn) => {
            btn.addEventListener('click', (e) => {
                e.stopPropagation();
                const trackIndex = parseInt(btn.getAttribute('data-track-index'));
                this.downloadTrack(trackIndex);
            });
        });
    }

    playTrack(index) {
        if (index < 0 || index >= this.tracks.length) return;
        
        this.currentTrackIndex = index;
        const track = this.tracks[index];
        
        console.log('Loading track:', track.title, 'URL:', track.streaming_url);
        this.audio.src = track.streaming_url;
        this.audio.load();
        
        this.currentTrackTitle.textContent = track.title;
        this.currentTrackArtist.textContent = track.artist || 'Unknown Artist';

        // Update cover art
        this.updateCoverArt(track.cover_art_url);

        // Update mobile player info
        if (this.mobileCurrentTrackTitle) {
            this.mobileCurrentTrackTitle.textContent = track.title;
            this.mobileCurrentTrackArtist.textContent = track.artist || 'Unknown Artist';
        }

        this.updatePlayerVisibility();
        this.showMobilePlayer(); // Show mobile player if on mobile
    }

    updateCoverArt(coverArtUrl) {
        // Desktop cover art
        const currentTrackCover = document.getElementById('current-track-cover');
        const currentTrackCoverPlaceholder = document.getElementById('current-track-cover-placeholder');

        // Mobile cover art
        const mobileCurrentTrackCover = document.getElementById('mobile-current-track-cover');
        const mobileCurrentTrackCoverPlaceholder = document.getElementById('mobile-current-track-cover-placeholder');

        if (coverArtUrl) {
            // Show cover art, hide placeholder
            if (currentTrackCover) {
                currentTrackCover.src = coverArtUrl;
                currentTrackCover.classList.remove('hidden');
                currentTrackCover.onerror = () => {
                    currentTrackCover.classList.add('hidden');
                    if (currentTrackCoverPlaceholder) currentTrackCoverPlaceholder.style.display = 'flex';
                };
            }
            if (currentTrackCoverPlaceholder) currentTrackCoverPlaceholder.style.display = 'none';

            if (mobileCurrentTrackCover) {
                mobileCurrentTrackCover.src = coverArtUrl;
                mobileCurrentTrackCover.classList.remove('hidden');
                mobileCurrentTrackCover.onerror = () => {
                    mobileCurrentTrackCover.classList.add('hidden');
                    if (mobileCurrentTrackCoverPlaceholder) mobileCurrentTrackCoverPlaceholder.style.display = 'flex';
                };
            }
            if (mobileCurrentTrackCoverPlaceholder) mobileCurrentTrackCoverPlaceholder.style.display = 'none';
        } else {
            // Show placeholder, hide cover art
            if (currentTrackCover) currentTrackCover.classList.add('hidden');
            if (currentTrackCoverPlaceholder) currentTrackCoverPlaceholder.style.display = 'flex';

            if (mobileCurrentTrackCover) mobileCurrentTrackCover.classList.add('hidden');
            if (mobileCurrentTrackCoverPlaceholder) mobileCurrentTrackCoverPlaceholder.style.display = 'flex';
        }
        this.renderTracks(); // Re-render to highlight current track

        this.audio.play().then(() => {
            console.log('Track started playing successfully');
            this.isPlaying = true;
            this.updatePlayPauseButton();
        }).catch(error => {
            console.error('Failed to play track:', error);
            console.error('Audio src:', this.audio.src);
            console.error('Audio readyState:', this.audio.readyState);
            console.error('Audio networkState:', this.audio.networkState);
            this.showError('Failed to play track: ' + error.message);
        });
    }

    togglePlayPause() {
        if (this.currentTrackIndex === -1) return;
        
        if (this.isPlaying) {
            this.audio.pause();
            this.isPlaying = false;
        } else {
            this.audio.play().then(() => {
                this.isPlaying = true;
            }).catch(error => {
                console.error('Failed to play:', error);
            });
        }
        
        this.updatePlayPauseButton();
    }

    previousTrack() {
        if (this.currentTrackIndex > 0) {
            this.playTrack(this.currentTrackIndex - 1);
        }
    }

    nextTrack() {
        if (this.currentTrackIndex < this.tracks.length - 1) {
            this.playTrack(this.currentTrackIndex + 1);
        }
    }

    /**
     * Skip forward 15 seconds
     */
    skipForward() {
        if (this.audio.duration) {
            this.audio.currentTime = Math.min(
                this.audio.currentTime + 15,
                this.audio.duration
            );
        }
    }

    /**
     * Skip backward 15 seconds
     */
    skipBackward() {
        if (this.audio.duration) {
            this.audio.currentTime = Math.max(
                this.audio.currentTime - 15,
                0
            );
        }
    }

    /**
     * Download current track
     */
    downloadTrack(trackIndex = null) {
        const index = trackIndex !== null ? trackIndex : this.currentTrackIndex;
        if (index >= 0 && index < this.tracks.length) {
            const track = this.tracks[index];
            const downloadUrl = `/api/music/download/${track.id}`;

            // Create a temporary link and click it to trigger download
            const link = document.createElement('a');
            link.href = downloadUrl;
            link.download = '';
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            this.showSuccess(`Downloading: ${track.title}`);
        }
    }

    updatePlayPauseButton() {
        // Desktop buttons
        if (this.playIcon && this.pauseIcon) {
            if (this.isPlaying) {
                this.playIcon.classList.add('hidden');
                this.pauseIcon.classList.remove('hidden');
            } else {
                this.playIcon.classList.remove('hidden');
                this.pauseIcon.classList.add('hidden');
            }
        }

        // Mobile buttons
        if (this.mobilePlayIcon && this.mobilePauseIcon) {
            if (this.isPlaying) {
                this.mobilePlayIcon.classList.add('hidden');
                this.mobilePauseIcon.classList.remove('hidden');
            } else {
                this.mobilePlayIcon.classList.remove('hidden');
                this.mobilePauseIcon.classList.add('hidden');
            }
        }
    }

    updateProgress() {
        if (this.audio.duration) {
            const percent = (this.audio.currentTime / this.audio.duration) * 100;

            // Update desktop progress
            if (this.progressBar) {
                this.progressBar.style.width = `${percent}%`;
            }
            if (this.currentTime) {
                this.currentTime.textContent = this.formatTime(this.audio.currentTime);
            }

            // Update mobile progress
            if (this.mobileProgressBar) {
                this.mobileProgressBar.style.width = `${percent}%`;
            }
            if (this.mobileCurrentTime) {
                this.mobileCurrentTime.textContent = this.formatTime(this.audio.currentTime);
            }
        }
    }

    updateTotalTime() {
        const duration = this.formatTime(this.audio.duration);
        if (this.totalTime) {
            this.totalTime.textContent = duration;
        }
        if (this.mobileTotalTime) {
            this.mobileTotalTime.textContent = duration;
        }
    }

    formatTime(seconds) {
        if (isNaN(seconds)) return '0:00';
        const mins = Math.floor(seconds / 60);
        const secs = Math.floor(seconds % 60);
        return `${mins}:${secs.toString().padStart(2, '0')}`;
    }

    updateTrackCount() {
        this.trackCount.textContent = this.tracks.length;
    }

    showLoading(show) {
        if (this.loading) {
            this.loading.classList.toggle('hidden', !show);
        }
    }

    showError(message) {
        // Create a toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-red-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 fade-in';
        toast.innerHTML = `
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"></path>
                </svg>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        // Remove toast after 5 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 5000);
    }

    showSuccess(message) {
        // Create a success toast notification
        const toast = document.createElement('div');
        toast.className = 'fixed top-4 right-4 bg-green-600 text-white px-6 py-3 rounded-lg shadow-lg z-50 fade-in';
        toast.innerHTML = `
            <div class="flex items-center space-x-2">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                </svg>
                <span>${message}</span>
            </div>
        `;

        document.body.appendChild(toast);

        // Remove toast after 3 seconds
        setTimeout(() => {
            toast.style.opacity = '0';
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.parentNode.removeChild(toast);
                }
            }, 300);
        }, 3000);
    }

    handleAudioError(e) {
        console.error('Audio error:', e);
        this.showError('Failed to load audio file');
        this.isPlaying = false;
        this.updatePlayPauseButton();
    }

    debounceLoadTracks() {
        clearTimeout(this.searchTimeout);
        this.searchTimeout = setTimeout(() => this.loadTracks(), 300);
    }

    async syncTracks() {
        this.syncBtn.disabled = true;
        this.syncBtn.textContent = 'Syncing...';
        
        try {
            const response = await fetch('/api/music/sync', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                },
            });
            
            const result = await response.json();
            
            if (result.success) {
                this.showSuccess(`Successfully synced ${result.synced_count} tracks!`);
                this.loadTracks();
            } else {
                this.showError(result.message || 'Sync failed');
            }
        } catch (error) {
            console.error('Sync error:', error);
            this.showError('Failed to sync tracks');
        } finally {
            this.syncBtn.disabled = false;
            this.syncBtn.textContent = 'Sync Messages';
        }
    }

    /**
     * Update player visibility based on screen size and current track
     */
    updatePlayerVisibility() {
        if (this.currentTrackIndex === -1) return;

        if (this.isMobile) {
            // Show mobile player, hide desktop player
            if (this.playerControls) {
                this.playerControls.classList.add('hidden');
            }
            if (this.mobilePlayerControls) {
                this.mobilePlayerControls.classList.remove('hidden');
            }
        } else {
            // Show desktop player, hide mobile player
            if (this.playerControls) {
                this.playerControls.classList.remove('hidden');
            }
            if (this.mobilePlayerControls) {
                this.mobilePlayerControls.classList.add('hidden');
            }
        }
    }

    /**
     * Minimize mobile player (hide it)
     */
    minimizeMobilePlayer() {
        if (this.mobilePlayerControls) {
            this.mobilePlayerControls.classList.add('hidden');
        }
    }

    /**
     * Show mobile player when track is selected on mobile
     */
    showMobilePlayer() {
        if (this.isMobile && this.mobilePlayerControls) {
            this.mobilePlayerControls.classList.remove('hidden');
        }
    }
}




// Simple Now Playing panel functions
function showNowPlayingPanel() {
    const panel = document.getElementById('now-playing-panel');
    if (panel) {
        // Add background overlay class to body
        document.body.classList.add('now-playing-active');

        panel.classList.remove('hidden');
        setTimeout(() => {
            panel.classList.add('show');
            document.body.classList.add('show');
        }, 10);
    }
}

function hideNowPlayingPanel() {
    const panel = document.getElementById('now-playing-panel');
    if (panel) {
        // Remove background overlay classes
        document.body.classList.remove('show');
        panel.classList.remove('show');

        setTimeout(() => {
            panel.classList.add('hidden');
            document.body.classList.remove('now-playing-active');
        }, 600);
    }

    // Reset mobile panels to default state
    resetMobilePanels();
}

function resetMobilePanels() {
    // Reset desktop mobile panel
    const desktopMobileTitle = document.getElementById('current-track-title');
    const desktopMobileArtist = document.getElementById('current-track-artist');
    const desktopMobileCover = document.getElementById('current-track-cover');
    const desktopMobilePlaceholder = document.getElementById('current-track-cover-placeholder');

    if (desktopMobileTitle) desktopMobileTitle.textContent = 'No track selected';
    if (desktopMobileArtist) desktopMobileArtist.textContent = '';
    if (desktopMobileCover) desktopMobileCover.classList.add('hidden');
    if (desktopMobilePlaceholder) desktopMobilePlaceholder.style.display = 'flex';

    // Reset bottom mobile panel
    const mobileTitle = document.getElementById('mobile-current-track-title');
    const mobileArtist = document.getElementById('mobile-current-track-artist');
    const mobileCover = document.getElementById('mobile-current-track-cover');
    const mobilePlaceholder = document.getElementById('mobile-current-track-cover-placeholder');

    if (mobileTitle) mobileTitle.textContent = 'No track selected';
    if (mobileArtist) mobileArtist.textContent = '';
    if (mobileCover) mobileCover.classList.add('hidden');
    if (mobilePlaceholder) mobilePlaceholder.style.display = 'flex';

    // Reset Now Playing progress
    resetNowPlayingProgress();
}

function resetNowPlayingProgress() {
    const durationEl = document.getElementById('now-playing-duration');
    const currentTimeEl = document.getElementById('now-playing-current-time');
    const progressBar = document.getElementById('now-playing-progress');

    if (durationEl) durationEl.textContent = '--:--';
    if (currentTimeEl) currentTimeEl.textContent = '0:00';
    if (progressBar) progressBar.style.width = '0%';
}

function updateNowPlayingPanel(track) {
    const panel = document.getElementById('now-playing-panel');
    if (!panel) return;

    // Update Now Playing panel
    const titleEl = document.getElementById('now-playing-title');
    const artistEl = document.getElementById('now-playing-artist');
    const durationEl = document.getElementById('now-playing-duration');
    const coverEl = document.getElementById('now-playing-cover');
    const placeholderEl = document.getElementById('now-playing-cover-placeholder');

    if (titleEl) titleEl.textContent = track.title || 'Unknown Title';
    if (artistEl) artistEl.textContent = track.artist || 'Unknown Artist';
    if (durationEl) durationEl.textContent = track.duration || '--:--';

    // Update cover art
    if (track.cover_art_url) {
        if (coverEl) {
            coverEl.src = track.cover_art_url;
            coverEl.classList.remove('hidden');
            coverEl.onerror = () => {
                coverEl.classList.add('hidden');
                if (placeholderEl) placeholderEl.style.display = 'flex';
            };
        }
        if (placeholderEl) placeholderEl.style.display = 'none';
    } else {
        if (coverEl) coverEl.classList.add('hidden');
        if (placeholderEl) placeholderEl.style.display = 'flex';
    }

    // Update mobile panels as well
    updateMobilePanels(track);

    // Show the panel
    showNowPlayingPanel();
}

function updateMobilePanels(track) {
    // Update desktop mobile panel
    const desktopMobileTitle = document.getElementById('current-track-title');
    const desktopMobileArtist = document.getElementById('current-track-artist');
    const desktopMobileCover = document.getElementById('current-track-cover');
    const desktopMobilePlaceholder = document.getElementById('current-track-cover-placeholder');

    if (desktopMobileTitle) desktopMobileTitle.textContent = track.title || 'Unknown Title';
    if (desktopMobileArtist) desktopMobileArtist.textContent = track.artist || 'Unknown Artist';

    // Update bottom mobile panel
    const mobileTitle = document.getElementById('mobile-current-track-title');
    const mobileArtist = document.getElementById('mobile-current-track-artist');
    const mobileCover = document.getElementById('mobile-current-track-cover');
    const mobilePlaceholder = document.getElementById('mobile-current-track-cover-placeholder');

    if (mobileTitle) mobileTitle.textContent = track.title || 'Unknown Title';
    if (mobileArtist) mobileArtist.textContent = track.artist || 'Unknown Artist';

    // Update cover art for both mobile panels
    if (track.cover_art_url) {
        // Desktop mobile panel
        if (desktopMobileCover) {
            desktopMobileCover.src = track.cover_art_url;
            desktopMobileCover.classList.remove('hidden');
        }
        if (desktopMobilePlaceholder) desktopMobilePlaceholder.style.display = 'none';

        // Bottom mobile panel
        if (mobileCover) {
            mobileCover.src = track.cover_art_url;
            mobileCover.classList.remove('hidden');
        }
        if (mobilePlaceholder) mobilePlaceholder.style.display = 'none';
    } else {
        // Desktop mobile panel
        if (desktopMobileCover) desktopMobileCover.classList.add('hidden');
        if (desktopMobilePlaceholder) desktopMobilePlaceholder.style.display = 'flex';

        // Bottom mobile panel
        if (mobileCover) mobileCover.classList.add('hidden');
        if (mobilePlaceholder) mobilePlaceholder.style.display = 'flex';
    }
}

// Simple play/pause function
function togglePlayPause() {
    const audio = document.getElementById('audio-player');

    if (!audio || !audio.src) {
        showNotification('No track loaded');
        return;
    }

    if (audio.paused) {
        audio.play().then(() => {
            updatePlayPauseButton();
            showPlayPauseFeedback('play');
        }).catch(error => {
            console.error('Failed to play:', error);
            showNotification('Failed to resume playback');
        });
    } else {
        audio.pause();
        updatePlayPauseButton();
        showPlayPauseFeedback('pause');
    }
}

// Update play/pause button state
function updatePlayPauseButton() {
    const audio = document.getElementById('audio-player');
    const playIcon = document.getElementById('now-playing-play-icon');
    const pauseIcon = document.getElementById('now-playing-pause-icon');
    const playPauseBtn = document.getElementById('now-playing-play-pause');

    if (!audio || !playIcon || !pauseIcon || !playPauseBtn) return;

    if (audio.paused) {
        playIcon.classList.remove('hidden');
        pauseIcon.classList.add('hidden');
        playPauseBtn.classList.remove('playing');
    } else {
        playIcon.classList.add('hidden');
        pauseIcon.classList.remove('hidden');
        playPauseBtn.classList.add('playing');
    }
}

// Simple skip functions
function skipBackward15() {
    const audio = document.getElementById('audio-player');
    if (audio && !audio.paused) {
        const newTime = Math.max(0, audio.currentTime - 15);
        audio.currentTime = newTime;
        showSkipFeedback('backward');
    }
}

function skipForward15() {
    const audio = document.getElementById('audio-player');
    if (audio && !audio.paused) {
        const newTime = Math.min(audio.duration || audio.currentTime + 15, audio.currentTime + 15);
        audio.currentTime = newTime;
        showSkipFeedback('forward');
    }
}

function showSkipFeedback(direction) {
    const feedback = document.createElement('div');
    feedback.className = 'fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gradient-to-r from-yellow-400 to-yellow-500 text-black px-6 py-3 rounded-xl font-bold text-lg shadow-lg z-50 transition-all duration-300 border-2 border-black border-opacity-20';

    const icon = direction === 'forward' ? '⏭️' : '⏮️';
    const text = direction === 'forward' ? '+15s' : '-15s';
    feedback.innerHTML = `<div class="flex items-center space-x-2"><span>${icon}</span><span>${text}</span></div>`;
    feedback.style.opacity = '0';
    feedback.style.transform = 'translate(-50%, -50%) scale(0.8)';

    document.body.appendChild(feedback);

    setTimeout(() => {
        feedback.style.opacity = '1';
        feedback.style.transform = 'translate(-50%, -50%) scale(1.1)';
    }, 10);

    setTimeout(() => {
        feedback.style.transform = 'translate(-50%, -50%) scale(1)';
    }, 150);

    setTimeout(() => {
        feedback.style.opacity = '0';
        feedback.style.transform = 'translate(-50%, -50%) scale(0.8)';
        setTimeout(() => {
            if (feedback.parentNode) {
                feedback.parentNode.removeChild(feedback);
            }
        }, 300);
    }, 1200);
}

function showPlayPauseFeedback(action) {
    const feedback = document.createElement('div');
    feedback.className = 'fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gradient-to-r from-yellow-400 to-yellow-500 text-black px-6 py-3 rounded-xl font-bold text-lg shadow-lg z-50 transition-all duration-300 border-2 border-black border-opacity-20';

    const icon = action === 'play' ? '▶️' : '⏸️';
    const text = action === 'play' ? 'Playing' : 'Paused';
    feedback.innerHTML = `<div class="flex items-center space-x-2"><span>${icon}</span><span>${text}</span></div>`;
    feedback.style.opacity = '0';
    feedback.style.transform = 'translate(-50%, -50%) scale(0.8)';

    document.body.appendChild(feedback);

    setTimeout(() => {
        feedback.style.opacity = '1';
        feedback.style.transform = 'translate(-50%, -50%) scale(1.1)';
    }, 10);

    setTimeout(() => {
        feedback.style.transform = 'translate(-50%, -50%) scale(1)';
    }, 150);

    setTimeout(() => {
        feedback.style.opacity = '0';
        feedback.style.transform = 'translate(-50%, -50%) scale(0.8)';
        setTimeout(() => {
            if (feedback.parentNode) {
                feedback.parentNode.removeChild(feedback);
            }
        }, 300);
    }, 800);
}

function showSeekFeedback(timeText) {
    const feedback = document.createElement('div');
    feedback.className = 'fixed top-1/2 left-1/2 transform -translate-x-1/2 -translate-y-1/2 bg-gradient-to-r from-yellow-400 to-yellow-500 text-black px-6 py-3 rounded-xl font-bold text-lg shadow-lg z-50 transition-all duration-300 border-2 border-black border-opacity-20';

    feedback.innerHTML = `<div class="flex items-center space-x-2"><span>🎯</span><span>${timeText}</span></div>`;
    feedback.style.opacity = '0';
    feedback.style.transform = 'translate(-50%, -50%) scale(0.8)';

    document.body.appendChild(feedback);

    setTimeout(() => {
        feedback.style.opacity = '1';
        feedback.style.transform = 'translate(-50%, -50%) scale(1.1)';
    }, 10);

    setTimeout(() => {
        feedback.style.transform = 'translate(-50%, -50%) scale(1)';
    }, 150);

    setTimeout(() => {
        feedback.style.opacity = '0';
        feedback.style.transform = 'translate(-50%, -50%) scale(0.8)';
        setTimeout(() => {
            if (feedback.parentNode) {
                feedback.parentNode.removeChild(feedback);
            }
        }, 300);
    }, 800);
}

// Format seconds to MM:SS
function formatTime(seconds) {
    if (!seconds || isNaN(seconds)) return '--:--';
    const minutes = Math.floor(seconds / 60);
    const remainingSeconds = Math.floor(seconds % 60);
    return `${minutes}:${remainingSeconds.toString().padStart(2, '0')}`;
}

// Update track duration in database
function updateTrackDuration(streamingUrl, duration) {
    fetch('/api/music/update-duration', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            streaming_url: streamingUrl,
            duration: Math.round(duration)
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            console.log('Duration updated successfully:', formatTime(duration));
        }
    })
    .catch(error => {
        console.error('Failed to update duration:', error);
    });
}

// Test audio functionality
function testAudio() {
    console.log('🧪 Testing audio functionality...');
    const audio = document.getElementById('audio-player');
    if (audio) {
        console.log('✅ Audio element found');
        console.log('🔧 Audio element properties:', {
            src: audio.src,
            readyState: audio.readyState,
            networkState: audio.networkState,
            error: audio.error,
            paused: audio.paused,
            currentTime: audio.currentTime,
            duration: audio.duration
        });

        // Test with a sample track
        fetch('/api/music/tracks')
            .then(response => response.json())
            .then(data => {
                if (data.data && data.data.length > 0) {
                    const track = data.data[0];
                    console.log('🎵 Testing with track:', track.title);
                    console.log('🔗 URL:', track.streaming_url);

                    audio.src = track.streaming_url;
                    audio.load();

                    return audio.play();
                } else {
                    throw new Error('No tracks available for testing');
                }
            })
            .then(() => {
                console.log('✅ Test playback successful!');
            })
            .catch(error => {
                console.error('❌ Test playback failed:', error);
            });
    } else {
        console.log('❌ Audio element not found');
    }
}

// Diagnostic function
function diagnoseAudio() {
    console.log('🔍 Audio Diagnostics:');

    // Check audio element
    const audio = document.getElementById('audio-player');
    console.log('Audio element:', audio ? '✅ Found' : '❌ Not found');

    // Check track items
    const trackItems = document.querySelectorAll('.track-item');
    console.log('Track items:', trackItems.length, 'found');

    // Check if tracks have click handlers
    if (trackItems.length > 0) {
        const firstTrack = trackItems[0];
        console.log('First track data:', {
            url: firstTrack.dataset.trackUrl,
            title: firstTrack.dataset.trackTitle,
            hasOnclick: firstTrack.onclick !== null
        });
    }

    // Check browser audio support
    if (audio) {
        console.log('Audio formats supported:', {
            mp3: audio.canPlayType('audio/mpeg'),
            wav: audio.canPlayType('audio/wav'),
            ogg: audio.canPlayType('audio/ogg')
        });
    }

    return {
        audioElement: !!audio,
        trackCount: trackItems.length,
        audioSupport: audio ? audio.canPlayType('audio/mpeg') : 'No audio element'
    };
}

// Simple play function
function playTrack(url, title, artist, coverArtUrl = null, duration = null) {
    console.log('🎵 playTrack called with:', {
        url: url,
        title: title,
        artist: artist,
        coverArtUrl: coverArtUrl,
        duration: duration
    });

    // Get or create audio element
    let audio = document.getElementById('audio-player');
    if (!audio) {
        console.log('❌ Audio element not found, creating new one');
        audio = document.createElement('audio');
        audio.id = 'audio-player';
        audio.controls = true;
        audio.style.width = '100%';
        document.body.appendChild(audio);
    } else {
        console.log('✅ Found existing audio element');
    }

    // Set source and play
    console.log('🔗 Setting audio source to:', url);
    audio.src = url;
    audio.load();
    console.log('📥 Audio loaded, attempting to play...');

    // Add event listener to update duration when metadata is loaded
    audio.addEventListener('loadedmetadata', () => {
        const actualDuration = audio.duration;
        if (actualDuration && !isNaN(actualDuration) && actualDuration > 10) {
            const durationEl = document.getElementById('now-playing-duration');
            if (durationEl) {
                durationEl.textContent = formatTime(actualDuration);
            }

            // Update the database with the correct duration if it's significantly different
            const storedDuration = duration || 0;
            if (Math.abs(actualDuration - storedDuration) > 10) {
                updateTrackDuration(url, actualDuration);
            }
        }
    }, { once: true });

    // Add event listener to update progress bar and current time
    audio.addEventListener('timeupdate', () => {
        const currentTime = audio.currentTime;
        const duration = audio.duration;

        if (duration && !isNaN(duration) && !isNaN(currentTime)) {
            const progress = (currentTime / duration) * 100;
            const progressBar = document.getElementById('now-playing-progress');
            const currentTimeEl = document.getElementById('now-playing-current-time');

            if (progressBar) {
                progressBar.style.width = `${Math.min(progress, 100)}%`;
            }
            if (currentTimeEl) {
                currentTimeEl.textContent = formatTime(currentTime);
            }
        }
    });

    // Add event listeners for audio events
    audio.addEventListener('ended', () => {
        hideNowPlayingPanel();
    });

    audio.addEventListener('pause', () => {
        // Optionally hide the panel when paused (uncomment if desired)
        // hideNowPlayingPanel();
    });

    // Try to play the audio
    const playPromise = audio.play();

    if (playPromise !== undefined) {
        playPromise.then(() => {
            console.log('✅ Successfully playing:', title);

            // Update Now Playing panel
            const track = {
                title: title,
                artist: artist,
                cover_art_url: coverArtUrl,
                duration: duration ? formatTime(duration) : '--:--'
            };

            // Update Now Playing panel directly
            updateNowPlayingPanel(track);

            // Update play/pause button state
            updatePlayPauseButton();

            // Show a simple notification
            showNotification(`Now playing: ${title} by ${artist}`);
        }).catch(error => {
            console.error('❌ Failed to play audio:', error);
            console.error('❌ Error details:', {
                name: error.name,
                message: error.message,
                code: error.code
            });

            // Handle specific error types
            if (error.name === 'NotAllowedError') {
                showNotification(`Playback blocked - Please click play button to start audio`);
                console.log('🔒 Autoplay blocked - user interaction required');
            } else if (error.name === 'NotSupportedError') {
                showNotification(`Audio format not supported`);
            } else {
                showNotification(`Failed to play: ${title} - ${error.message}`);
            }
        });
    } else {
        console.log('⚠️ Play method did not return a promise');
    }
}

// Simple download function
function downloadTrack(trackId, title) {
    console.log('Downloading:', title);
    const link = document.createElement('a');
    link.href = `/api/music/download/${trackId}`;
    link.download = '';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showNotification(`Downloading: ${title}`);
}

// Simple notification function
function showNotification(message) {
    // Remove existing notification
    const existing = document.getElementById('notification');
    if (existing) existing.remove();

    // Create notification
    const notification = document.createElement('div');
    notification.id = 'notification';
    notification.className = 'fixed top-4 right-4 bg-yellow-500 text-black px-4 py-2 rounded-lg shadow-lg z-50';
    notification.textContent = message;
    document.body.appendChild(notification);

    // Remove after 3 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.parentNode.removeChild(notification);
        }
    }, 3000);
}

// Simple track loading for testing
document.addEventListener('DOMContentLoaded', () => {
    console.log('🚀 DOM loaded, initializing music player...');

    // Check if audio element exists
    const audioElement = document.getElementById('audio-player');
    if (audioElement) {
        console.log('✅ Audio element found');
        // Add global error handler
        audioElement.addEventListener('error', function(e) {
            console.error('🔥 Global audio error:', e);
            if (audioElement.error) {
                console.error('🔥 Audio error details:', {
                    code: audioElement.error.code,
                    message: audioElement.error.message
                });
            }
        });
    } else {
        console.error('❌ Audio element not found!');
    }

    // Add test functions to global scope for debugging
    window.testAudio = testAudio;
    window.diagnoseAudio = diagnoseAudio;
    console.log('🧪 Debug functions available: window.testAudio(), window.diagnoseAudio()');

    const trackList = document.getElementById('track-list');
    const loading = document.getElementById('loading');
    const noTracks = document.getElementById('no-tracks');
    const trackCount = document.getElementById('track-count');

    if (!trackList) {
        console.error('track-list element not found');
        return;
    }

    console.log('Elements found, fetching tracks...');

    fetch('/api/music/tracks')
        .then(response => {
            console.log('API response status:', response.status);
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('API data:', data);
            const tracks = data.data || [];
            console.log('Track count:', tracks.length);

            if (loading) loading.classList.add('hidden');

            if (tracks.length === 0) {
                if (noTracks) noTracks.classList.remove('hidden');
                return;
            }

            if (noTracks) noTracks.classList.add('hidden');

            let html = '';
            tracks.forEach((track, index) => {
                html += `
                    <div class="track-item px-4 py-3 cursor-pointer hover:bg-gray-700 transition-colors border-b border-gray-600"
                         data-track-url="${track.streaming_url}"
                         data-track-title="${track.title}"
                         data-track-artist="${track.artist || 'Unknown Artist'}"
                         data-track-cover="${track.cover_art_url || ''}"
                         data-track-duration="${track.duration || 'null'}"
                         onclick="playTrack('${track.streaming_url}', '${track.title}', '${track.artist || 'Unknown Artist'}', '${track.cover_art_url || ''}', ${track.duration || 'null'})">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center space-x-3 flex-1">
                                <div class="relative flex-shrink-0 cover-art-container">
                                    ${track.cover_art_url ?
                                        `<img src="${track.cover_art_url}" alt="Cover art" class="w-12 h-12 rounded-lg object-cover shadow-md">` :
                                        `<div class="w-12 h-12 rounded-lg flex items-center justify-center shadow-md" style="background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);">
                                            <svg class="w-6 h-6 text-black" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M18 3a1 1 0 00-1.196-.98l-10 2A1 1 0 006 5v9.114A4.369 4.369 0 005 14c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V7.82l8-1.6v5.894A4.37 4.37 0 0015 12c-1.657 0-3 .895-3 2s1.343 2 3 2 3-.895 3-2V3z" clip-rule="evenodd"></path>
                                            </svg>
                                        </div>`
                                    }
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="font-medium text-white truncate">${track.title || 'Unknown Title'}</div>
                                    <div class="text-sm text-gray-400 truncate">${track.artist || 'Unknown Artist'}</div>
                                    <div class="flex items-center justify-between mt-1">
                                        <div class="text-xs text-yellow-400 truncate">${track.comment || 'No description'}</div>
                                        <div class="text-xs text-blue-400 ml-2 flex-shrink-0">${track.formatted_duration || '--:--'}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center space-x-4 text-sm text-gray-400 flex-shrink-0">
                                <span>${track.formatted_file_size || '—'}</span>
                                <button class="bg-yellow-400 hover:bg-yellow-500 text-black p-2 rounded-lg transition-all duration-200 hover:scale-105"
                                        onclick="event.stopPropagation(); downloadTrack('${track.id}', '${track.title}')"
                                        title="Download ${track.title}">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M3 17a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm3.293-7.707a1 1 0 011.414 0L9 10.586V3a1 1 0 112 0v7.586l1.293-1.293a1 1 0 111.414 1.414l-3 3a1 1 0 01-1.414 0l-3-3a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                `;
            });

            trackList.innerHTML = html;

            // Add click event listeners as backup
            const trackItems = trackList.querySelectorAll('.track-item');
            trackItems.forEach(item => {
                item.addEventListener('click', function(e) {
                    // Prevent double-firing if onclick also works
                    if (e.detail > 1) return;

                    const url = this.dataset.trackUrl;
                    const title = this.dataset.trackTitle;
                    const artist = this.dataset.trackArtist;
                    const cover = this.dataset.trackCover;
                    const duration = this.dataset.trackDuration;

                    console.log('🖱️ Track clicked via event listener:', title);

                    if (url && title) {
                        playTrack(url, title, artist, cover, duration === 'null' ? null : duration);
                    }
                });
            });

            if (trackCount) {
                trackCount.textContent = tracks.length;
            }

            console.log('Tracks rendered successfully, added', trackItems.length, 'click listeners');
        })
        .catch(error => {
            console.error('Failed to load tracks:', error);
            if (loading) loading.classList.add('hidden');
            if (noTracks) noTracks.classList.remove('hidden');
        });

    // Add sync button functionality
    const syncBtn = document.getElementById('sync-btn');
    if (syncBtn) {
        syncBtn.addEventListener('click', () => {
            syncBtn.disabled = true;
            syncBtn.textContent = 'Syncing...';

            fetch('/api/music/sync', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    showNotification(`Successfully synced ${data.synced_count} tracks!`);
                    // Reload the page to show new tracks
                    setTimeout(() => location.reload(), 1000);
                } else {
                    showNotification('Sync failed: ' + (data.message || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Sync error:', error);
                showNotification('Sync failed: ' + error.message);
            })
            .finally(() => {
                syncBtn.disabled = false;
                syncBtn.textContent = 'Sync Messages';
            });
        });
    }

    // Setup Now Playing panel controls
    const closeNowPlayingBtn = document.getElementById('close-now-playing');
    const skipBackwardBtn = document.getElementById('now-playing-skip-backward');
    const skipForwardBtn = document.getElementById('now-playing-skip-forward');
    const playPauseBtn = document.getElementById('now-playing-play-pause');
    const progressContainer = document.getElementById('progress-container');

    if (closeNowPlayingBtn) {
        closeNowPlayingBtn.addEventListener('click', hideNowPlayingPanel);
    }

    if (skipBackwardBtn) {
        skipBackwardBtn.addEventListener('click', skipBackward15);
    }

    if (skipForwardBtn) {
        skipForwardBtn.addEventListener('click', skipForward15);
    }

    if (playPauseBtn) {
        playPauseBtn.addEventListener('click', togglePlayPause);
    }

    // Add click functionality to progress bar for seeking
    if (progressContainer) {
        progressContainer.addEventListener('click', (e) => {
            const audio = document.getElementById('audio-player');
            if (audio && audio.duration) {
                const rect = progressContainer.getBoundingClientRect();
                const clickX = e.clientX - rect.left;
                const width = rect.width;
                const percentage = clickX / width;
                const newTime = percentage * audio.duration;

                audio.currentTime = Math.max(0, Math.min(newTime, audio.duration));
                showSeekFeedback(formatTime(newTime));
            }
        });
    }

    // Setup audio event listeners for play/pause button state
    const audio = document.getElementById('audio-player');
    if (audio) {
        audio.addEventListener('play', updatePlayPauseButton);
        audio.addEventListener('pause', updatePlayPauseButton);
        audio.addEventListener('ended', () => {
            updatePlayPauseButton();
            hideNowPlayingPanel();
        });
    }

    // Keyboard shortcuts for playback control
    document.addEventListener('keydown', (e) => {
        const nowPlayingPanel = document.getElementById('now-playing-panel');
        if (nowPlayingPanel && !nowPlayingPanel.classList.contains('hidden') &&
            !['INPUT', 'TEXTAREA'].includes(document.activeElement.tagName)) {

            if (e.key === 'ArrowLeft' || e.key === 'j') {
                e.preventDefault();
                skipBackward15();
            } else if (e.key === 'ArrowRight' || e.key === 'l') {
                e.preventDefault();
                skipForward15();
            } else if (e.key === ' ' || e.key === 'k') {
                e.preventDefault();
                togglePlayPause();
            }
        }
    });
});
