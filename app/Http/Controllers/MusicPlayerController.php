<?php

namespace App\Http\Controllers;

use App\Models\Track;
use App\Services\BunnyStorageService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Log;

class MusicPlayerController extends Controller
{
    public function __construct(
        private BunnyStorageService $bunnyStorage
    ) {}

    /**
     * Display the main music player interface
     */
    public function index()
    {
        return view('music-working');
    }

    /**
     * Get all tracks from database
     */
    public function getTracks(Request $request): JsonResponse
    {
        $query = Track::active();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $query->search($request->search);
        }

        // Filter by genre
        if ($request->has('genre') && $request->genre) {
            $query->byGenre($request->genre);
        }

        // Sorting
        $sortBy = $request->get('sort_by', 'title');
        $sortOrder = $request->get('sort_order', 'asc');

        $allowedSorts = ['title', 'artist', 'album', 'year', 'duration', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder);
        }

        $tracks = $query->paginate($request->get('per_page', 50));

        return response()->json($tracks);
    }

    /**
     * Get a specific track by ID
     */
    public function getTrack(Track $track): JsonResponse
    {
        return response()->json($track);
    }

    /**
     * Update track metadata
     */
    public function updateTrack(Request $request, Track $track): JsonResponse
    {
        $validated = $request->validate([
            'title' => 'sometimes|string|max:255',
            'artist' => 'sometimes|nullable|string|max:255',
            'album' => 'sometimes|nullable|string|max:255',
            'genre' => 'sometimes|nullable|string|max:255',
            'comment' => 'sometimes|nullable|string',
        ]);

        $track->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Track updated successfully',
            'track' => $track
        ]);
    }

    /**
     * Bulk update track metadata
     */
    public function bulkUpdateTracks(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'artist' => 'sometimes|nullable|string|max:255',
            'album' => 'sometimes|nullable|string|max:255',
            'genre' => 'sometimes|nullable|string|max:255',
        ]);

        $updateData = array_filter($validated, function($value) {
            return $value !== null && $value !== '';
        });

        if (empty($updateData)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid fields to update'
            ], 400);
        }

        $updatedCount = Track::active()->update($updateData);

        return response()->json([
            'success' => true,
            'message' => "Updated {$updatedCount} tracks successfully",
            'updated_count' => $updatedCount
        ]);
    }

    /**
     * Show metadata management interface
     */
    public function showMetadataManager()
    {
        return view('admin.metadata');
    }

    /**
     * Health check endpoint for monitoring
     */
    public function healthCheck(): JsonResponse
    {
        $checks = [
            'app' => true,
            'database' => false,
            'storage' => false,
            'bunny_cdn' => false,
        ];

        try {
            // Database check
            Track::count();
            $checks['database'] = true;
        } catch (\Exception $e) {
            Log::error('Health check - Database failed: ' . $e->getMessage());
        }

        try {
            // Storage check
            $checks['storage'] = is_writable(storage_path('logs'));
        } catch (\Exception $e) {
            Log::error('Health check - Storage failed: ' . $e->getMessage());
        }

        try {
            // Bunny CDN check
            $testUrl = "https://{$this->bunnyCdnHostname}/";
            $context = stream_context_create([
                'http' => [
                    'method' => 'HEAD',
                    'timeout' => 5
                ]
            ]);
            $headers = get_headers($testUrl, false, $context);
            $checks['bunny_cdn'] = $headers && strpos($headers[0], '200') !== false;
        } catch (\Exception $e) {
            Log::error('Health check - Bunny CDN failed: ' . $e->getMessage());
        }

        $allHealthy = array_reduce($checks, function($carry, $check) {
            return $carry && $check;
        }, true);

        return response()->json([
            'status' => $allHealthy ? 'healthy' : 'unhealthy',
            'timestamp' => now()->toISOString(),
            'checks' => $checks,
            'version' => '1.0.0'
        ], $allHealthy ? 200 : 503);
    }

    /**
     * Sync tracks from Bunny.net storage
     */
    public function syncTracks(): JsonResponse
    {
        try {
            // Clear cache first to ensure we get the latest files from Bunny.net
            $this->bunnyStorage->clearCache();

            $musicFiles = $this->bunnyStorage->getMusicFromDirectory();
            $syncedCount = 0;
            $deactivatedCount = 0;
            $errors = [];

            // Get list of current file names from Bunny.net
            $currentFileNames = array_column($musicFiles, 'name');

            // Sync existing files
            foreach ($musicFiles as $file) {
                try {
                    $this->createOrUpdateTrack($file);
                    $syncedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Failed to sync {$file['name']}: " . $e->getMessage();
                    Log::error('Track sync error', [
                        'file' => $file['name'],
                        'error' => $e->getMessage()
                    ]);
                }
            }

            // Mark missing files as inactive
            $missingTracks = Track::where('is_active', true)
                ->whereNotIn('file_name', $currentFileNames)
                ->get();

            foreach ($missingTracks as $track) {
                $track->update(['is_active' => false]);
                $deactivatedCount++;
                Log::info('Deactivated missing track', ['file' => $track->file_name]);
            }

            // Clear cache after sync
            $this->bunnyStorage->clearCache();

            return response()->json([
                'success' => true,
                'synced_count' => $syncedCount,
                'deactivated_count' => $deactivatedCount,
                'total_files' => count($musicFiles),
                'errors' => $errors,
                'message' => "Synced {$syncedCount} tracks, deactivated {$deactivatedCount} missing files"
            ]);

        } catch (\Exception $e) {
            Log::error('Sync tracks error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to sync tracks: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available genres
     */
    public function getGenres(): JsonResponse
    {
        return response()->json(Track::getGenres());
    }

    /**
     * Get available artists
     */
    public function getArtists(): JsonResponse
    {
        return response()->json(Track::getArtists());
    }

    /**
     * Update track duration from client-side audio metadata
     */
    public function updateDuration(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'streaming_url' => 'required|string',
                'duration' => 'required|integer|min:1'
            ]);

            $track = Track::where('streaming_url', $request->streaming_url)->first();

            if (!$track) {
                return response()->json([
                    'success' => false,
                    'message' => 'Track not found'
                ], 404);
            }

            // Only update if the new duration is significantly different and reasonable
            $newDuration = $request->duration;
            if ($newDuration > 10 && ($track->duration === null || abs($track->duration - $newDuration) > 10)) {
                $track->update(['duration' => $newDuration]);

                Log::info('Updated track duration', [
                    'track' => $track->title,
                    'old_duration' => $track->duration,
                    'new_duration' => $newDuration
                ]);
            }

            return response()->json([
                'success' => true,
                'duration' => $newDuration
            ]);

        } catch (\Exception $e) {
            Log::error('Update duration error', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to update duration'
            ], 500);
        }
    }

    /**
     * Stream a track (proxy to Bunny.net CDN)
     */
    public function streamTrack(Track $track)
    {
        // For security, we could add rate limiting here
        return redirect($track->streaming_url);
    }

    /**
     * Download a track from Bunny.net CDN
     */
    public function downloadTrack(Track $track)
    {
        // Create a filename for download
        $filename = $this->sanitizeFilename($track->artist ?
            $track->artist . ' - ' . $track->title :
            $track->title
        ) . '.' . $track->file_extension;

        // Return a redirect with download headers
        return redirect()->away($track->cdn_url, 302, [
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            'Content-Type' => 'application/octet-stream'
        ]);
    }

    /**
     * Get download URL for a track
     */
    public function getDownloadUrl(Track $track): JsonResponse
    {
        return response()->json([
            'download_url' => route('api.download', $track),
            'filename' => $this->sanitizeFilename($track->artist ?
                $track->artist . ' - ' . $track->title :
                $track->title
            ) . '.' . $track->file_extension,
            'size' => $track->formatted_file_size,
        ]);
    }

    /**
     * Sanitize filename for download
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove or replace invalid characters
        $filename = preg_replace('/[^\w\s\-\.]/', '', $filename);
        $filename = preg_replace('/\s+/', ' ', $filename);
        return trim($filename);
    }



    /**
     * Get track statistics
     */
    public function getStats(): JsonResponse
    {
        $stats = [
            'total_tracks' => Track::active()->count(),
            'total_artists' => Track::active()->distinct('artist')->count('artist'),
            'total_genres' => Track::active()->whereNotNull('genre')->distinct('genre')->count('genre'),
            'total_duration' => Track::active()->sum('duration'),
            'total_size' => Track::active()->sum('file_size'),
        ];

        // Format total duration
        $stats['formatted_duration'] = gmdate('H:i:s', $stats['total_duration']);

        // Format total size
        $bytes = $stats['total_size'];
        $units = ['B', 'KB', 'MB', 'GB'];
        for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
            $bytes /= 1024;
        }
        $stats['formatted_size'] = round($bytes, 2) . ' ' . $units[$i];

        return response()->json($stats);
    }

    /**
     * Create or update a track from Bunny.net file data
     */
    private function createOrUpdateTrack(array $fileData): Track
    {
        // Extract metadata from filename (basic implementation)
        $fileName = pathinfo($fileData['name'], PATHINFO_FILENAME);
        $metadata = $this->extractMetadataFromFile($fileData);

        $trackData = [
            'title' => $metadata['title'] ?: $fileName,
            'artist' => $metadata['artist'] ?? null,
            'album' => $metadata['album'] ?? null,
            'genre' => $metadata['genre'] ?? null,
            'comment' => $metadata['comment'] ?? null,
            'year' => $metadata['year'] ?? null,
            'release_date' => $metadata['date'] ?? null,
            'duration' => $metadata['duration'] ?? null,
            'cover_art_url' => $metadata['cover_art_url'] ?? null,
            'file_path' => $fileData['path'] . $fileData['name'],
            'file_name' => $fileData['name'],
            'file_extension' => $fileData['extension'],
            'file_size' => $fileData['size'],
            'cdn_url' => $fileData['cdn_url'],
            'streaming_url' => $fileData['streaming_url'],
            'metadata' => $metadata['raw_metadata'],
            'last_synced_at' => now(),
            'is_active' => true,
        ];

        // Check if track already exists
        $existingTrack = Track::where('file_path', $trackData['file_path'])->first();

        if ($existingTrack) {
            // For existing tracks, preserve user-edited metadata and only update technical fields
            $updateData = [
                'duration' => $trackData['duration'],
                'file_size' => $trackData['file_size'],
                'cdn_url' => $trackData['cdn_url'],
                'streaming_url' => $trackData['streaming_url'],
                'metadata' => $trackData['metadata'],
                'last_synced_at' => $trackData['last_synced_at'],
                'is_active' => $trackData['is_active'],
            ];

            // Only update metadata fields if they are currently empty/null
            // This preserves user-edited metadata while allowing new metadata to be added
            if (empty($existingTrack->title)) {
                $updateData['title'] = $trackData['title'];
            }
            if (empty($existingTrack->artist)) {
                $updateData['artist'] = $trackData['artist'];
            }
            if (empty($existingTrack->album)) {
                $updateData['album'] = $trackData['album'];
            }
            if (empty($existingTrack->genre)) {
                $updateData['genre'] = $trackData['genre'];
            }
            if (empty($existingTrack->comment)) {
                $updateData['comment'] = $trackData['comment'];
            }

            $existingTrack->update($updateData);
            return $existingTrack;
        } else {
            // For new tracks, create with all metadata
            return Track::create($trackData);
        }
    }

    /**
     * Extract metadata from audio file
     */
    private function extractMetadataFromFile(array $fileData): array
    {
        $metadata = [
            'title' => null,
            'artist' => null,
            'album' => null,
            'genre' => null,
            'comment' => null,
            'year' => null,
            'date' => null,
            'duration' => null,
            'cover_art_url' => null,
            'raw_metadata' => null,
        ];

        try {
            // Try to download and analyze the file for metadata
            $streamingUrl = $fileData['streaming_url'];

            // Create a temporary file to analyze
            $tempFile = tempnam(sys_get_temp_dir(), 'audio_metadata_');

            // Download a larger portion of the file for better metadata extraction
            $context = stream_context_create([
                'http' => [
                    'method' => 'GET',
                    'header' => "Range: bytes=0-5242880\r\n", // First 5MB for better duration detection
                    'timeout' => 30, // Increased timeout
                ]
            ]);

            $fileContent = file_get_contents($streamingUrl, false, $context);

            // If partial download failed, try without range header for smaller files
            if ($fileContent === false) {
                $contextFull = stream_context_create([
                    'http' => [
                        'method' => 'GET',
                        'timeout' => 60,
                    ]
                ]);
                $fileContent = file_get_contents($streamingUrl, false, $contextFull);
            }

            if ($fileContent !== false) {
                file_put_contents($tempFile, $fileContent);

                // Use getID3 to extract metadata
                $getID3 = new \getID3;
                $getID3->option_md5_data = true;
                $getID3->option_md5_data_source = true;
                $getID3->encoding = 'UTF-8';
                $fileInfo = $getID3->analyze($tempFile);

                if (isset($fileInfo['tags'])) {
                    $tags = $fileInfo['tags'];

                    // Extract common metadata from various tag formats
                    $metadata['title'] = $this->getFirstTag($tags, ['title', 'TIT2']);
                    $metadata['artist'] = $this->getFirstTag($tags, ['artist', 'TPE1']);
                    $metadata['album'] = $this->getFirstTag($tags, ['album', 'TALB']);
                    $metadata['genre'] = $this->getFirstTag($tags, ['genre', 'TCON']);

                    // Extract comment information
                    $commentString = $this->getFirstTag($tags, ['comment', 'COMM', 'description', 'DESC']);
                    $metadata['comment'] = $commentString;
                    $metadata['date'] = null; // Keep date field for compatibility but set to null
                    $metadata['year'] = null;
                }

                // Extract album art/cover art
                if (isset($fileInfo['comments']['picture'])) {
                    $metadata['cover_art_url'] = $this->extractAndUploadAlbumArt($fileInfo['comments']['picture'][0], $fileData['name']);
                } elseif (isset($fileInfo['id3v2']['APIC'])) {
                    $metadata['cover_art_url'] = $this->extractAndUploadAlbumArt($fileInfo['id3v2']['APIC'][0], $fileData['name']);
                } else {
                    // Use the default TEC Sunday School cover art
                    $metadata['cover_art_url'] = 'https://tec-cathecism.b-cdn.net/sunday_scool_image.png';
                }

                // Extract duration with multiple fallbacks
                $duration = null;

                // Try different duration sources
                if (isset($fileInfo['playtime_seconds']) && $fileInfo['playtime_seconds'] > 0) {
                    $duration = (int) round($fileInfo['playtime_seconds']);
                } elseif (isset($fileInfo['audio']['playtime_seconds']) && $fileInfo['audio']['playtime_seconds'] > 0) {
                    $duration = (int) round($fileInfo['audio']['playtime_seconds']);
                } elseif (isset($fileInfo['playtime_string'])) {
                    // Try to parse playtime_string (format: "MM:SS" or "HH:MM:SS")
                    $duration = $this->parseTimeString($fileInfo['playtime_string']);
                }

                // Only set duration if we got a reasonable value (more than 10 seconds)
                if ($duration && $duration > 10) {
                    $metadata['duration'] = $duration;
                } else {
                    Log::warning('Could not extract valid duration', [
                        'file' => $fileData['name'],
                        'playtime_seconds' => $fileInfo['playtime_seconds'] ?? 'not set',
                        'audio_playtime' => $fileInfo['audio']['playtime_seconds'] ?? 'not set',
                        'playtime_string' => $fileInfo['playtime_string'] ?? 'not set',
                        'calculated_duration' => $duration
                    ]);
                }

                // Store raw metadata for debugging
                $metadata['raw_metadata'] = [
                    'format' => $fileInfo['fileformat'] ?? null,
                    'bitrate' => $fileInfo['bitrate'] ?? null,
                    'sample_rate' => $fileInfo['audio']['sample_rate'] ?? null,
                    'channels' => $fileInfo['audio']['channels'] ?? null,
                ];

                // Clean up
                unlink($tempFile);
            }

        } catch (\Exception $e) {
            Log::warning('Failed to extract metadata from audio file', [
                'file' => $fileData['name'],
                'error' => $e->getMessage()
            ]);
        }

        // Always try filename parsing as fallback for missing metadata
        $filenameMetadata = $this->extractMetadataFromFilename($fileData['name']);

        // Use filename metadata for any missing fields
        foreach ($filenameMetadata as $key => $value) {
            if (empty($metadata[$key]) && !empty($value)) {
                $metadata[$key] = $value;
            }
        }

        return $metadata;
    }

    /**
     * Parse time string (MM:SS or HH:MM:SS) to seconds
     */
    private function parseTimeString(string $timeString): ?int
    {
        if (preg_match('/^(\d+):(\d+):(\d+)$/', $timeString, $matches)) {
            // HH:MM:SS format
            return (int)$matches[1] * 3600 + (int)$matches[2] * 60 + (int)$matches[3];
        } elseif (preg_match('/^(\d+):(\d+)$/', $timeString, $matches)) {
            // MM:SS format
            return (int)$matches[1] * 60 + (int)$matches[2];
        }

        return null;
    }

    /**
     * Extract album art from audio file and upload to Bunny.net
     */
    private function extractAndUploadAlbumArt(array $pictureData, string $fileName): ?string
    {
        try {
            if (!isset($pictureData['data']) || empty($pictureData['data'])) {
                return null;
            }

            // Get image data and mime type
            $imageData = $pictureData['data'];
            $mimeType = $pictureData['image_mime'] ?? 'image/jpeg';

            // Determine file extension from mime type
            $extension = match($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                default => 'jpg'
            };

            // Create a unique filename for the album art
            $artFileName = 'covers/' . pathinfo($fileName, PATHINFO_FILENAME) . '_cover.' . $extension;

            // Upload to Bunny.net storage
            $uploadUrl = "https://storage.bunnycdn.com/{$this->bunnyStorageZone}/{$artFileName}";

            $context = stream_context_create([
                'http' => [
                    'method' => 'PUT',
                    'header' => [
                        "AccessKey: {$this->bunnyAccessKey}",
                        "Content-Type: {$mimeType}",
                        'Content-Length: ' . strlen($imageData)
                    ],
                    'content' => $imageData,
                    'timeout' => 30
                ]
            ]);

            $result = file_get_contents($uploadUrl, false, $context);

            if ($result !== false) {
                // Return the CDN URL for the uploaded cover art
                return "https://{$this->bunnyCdnHostname}/{$artFileName}";
            }

        } catch (\Exception $e) {
            Log::warning('Failed to extract/upload album art', [
                'file' => $fileName,
                'error' => $e->getMessage()
            ]);
        }

        return null;
    }

    /**
     * Generate a default album art with the app's theme
     */
    private function generateDefaultAlbumArt(string $title): string
    {
        // Create a simple SVG album art with the app's black and yellow theme
        $cleanTitle = htmlspecialchars(substr($title, 0, 20), ENT_QUOTES, 'UTF-8');

        $svg = '<?xml version="1.0" encoding="UTF-8"?>
<svg width="300" height="300" viewBox="0 0 300 300" xmlns="http://www.w3.org/2000/svg">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:#000000;stop-opacity:1" />
      <stop offset="50%" style="stop-color:#1a1a1a;stop-opacity:1" />
      <stop offset="100%" style="stop-color:#fbbf24;stop-opacity:1" />
    </linearGradient>
  </defs>
  <rect width="300" height="300" fill="url(#bg)"/>
  <circle cx="150" cy="120" r="40" fill="#fbbf24" opacity="0.9"/>
  <path d="M130 100 L130 140 L170 120 Z" fill="#000000"/>
  <text x="150" y="180" text-anchor="middle" fill="#fbbf24" font-family="Arial, sans-serif" font-size="14" font-weight="bold">TEC Sunday School</text>
  <text x="150" y="200" text-anchor="middle" fill="#ffffff" font-family="Arial, sans-serif" font-size="12">' . $cleanTitle . '</text>
</svg>';

        // Return a data URL for the SVG
        return 'data:image/svg+xml;base64,' . base64_encode($svg);
    }

    /**
     * Get the first available tag value from multiple possible tag names
     */
    private function getFirstTag(array $tags, array $tagNames): ?string
    {
        foreach ($tags as $tagFormat => $tagData) {
            foreach ($tagNames as $tagName) {
                if (isset($tagData[$tagName][0])) {
                    return trim($tagData[$tagName][0]);
                }
            }
        }
        return null;
    }

    /**
     * Extract metadata from filename as fallback
     */
    private function extractMetadataFromFilename(string $fileName): array
    {
        $fileName = pathinfo($fileName, PATHINFO_FILENAME);
        $metadata = [
            'title' => null,
            'artist' => null,
            'album' => null,
            'genre' => null,
            'comment' => null,
            'year' => null,
            'date' => null,
            'duration' => null,
            'cover_art_url' => null,
        ];

        // Enhanced pattern matching for various filename formats

        // Pattern 1: Artist - Title
        if (preg_match('/^(.+?)\s*-\s*(.+)$/', $fileName, $matches)) {
            $metadata['artist'] = trim($matches[1]);
            $metadata['title'] = trim($matches[2]);
        }
        // Pattern 2: Artist - Album - Title
        elseif (preg_match('/^(.+?)\s*-\s*(.+?)\s*-\s*(.+)$/', $fileName, $matches)) {
            $metadata['artist'] = trim($matches[1]);
            $metadata['album'] = trim($matches[2]);
            $metadata['title'] = trim($matches[3]);
        }
        // Pattern 3: Track Number - Title (e.g., "01 - Song Title")
        elseif (preg_match('/^\d+\s*[-._]\s*(.+)$/', $fileName, $matches)) {
            $metadata['title'] = trim($matches[1]);
        }
        // Pattern 4: Just use filename as title if no patterns match
        else {
            $metadata['title'] = $fileName;
        }

        // Extract year from filename
        if (preg_match('/\b(19|20)\d{2}\b/', $fileName, $matches)) {
            $metadata['year'] = (int) $matches[0];
        }

        // Infer genre from filename keywords
        $genreKeywords = [
            'sermon' => ['sermon', 'preaching', 'message', 'teaching'],
            'worship' => ['worship', 'praise', 'hymn', 'song'],
            'gospel' => ['gospel', 'christian', 'church'],
            'prayer' => ['prayer', 'pray'],
            'study' => ['study', 'bible', 'scripture'],
        ];

        $lowerFileName = strtolower($fileName);
        foreach ($genreKeywords as $genre => $keywords) {
            foreach ($keywords as $keyword) {
                if (strpos($lowerFileName, $keyword) !== false) {
                    $metadata['genre'] = ucfirst($genre);
                    break 2;
                }
            }
        }

        // Infer artist from common patterns
        $artistPatterns = [
            'pastor' => 'Pastor',
            'rev' => 'Reverend',
            'dr' => 'Dr.',
            'elder' => 'Elder',
            'minister' => 'Minister',
        ];

        foreach ($artistPatterns as $pattern => $prefix) {
            if (preg_match('/\b' . $pattern . '\s+(\w+)/i', $fileName, $matches)) {
                $metadata['artist'] = $prefix . ' ' . $matches[1];
                break;
            }
        }

        // Infer album from series patterns
        if (preg_match('/(.+?)\s*(?:part|episode|session)\s*\d+/i', $fileName, $matches)) {
            $metadata['album'] = trim($matches[1]) . ' Series';
        }

        return $metadata;
    }

    /**
     * Parse date string from audio metadata
     */
    private function parseAudioDate(?string $dateString): ?\Carbon\Carbon
    {
        if (!$dateString) {
            return null;
        }

        try {
            // Try various date formats commonly found in audio metadata
            $formats = [
                'Y-m-d',           // 2024-03-15
                'Y/m/d',           // 2024/03/15
                'Y-m-d H:i:s',     // 2024-03-15 10:30:00
                'Y',               // 2024
                'd/m/Y',           // 15/03/2024
                'm/d/Y',           // 03/15/2024
                'Y-m',             // 2024-03
                'M d, Y',          // Mar 15, 2024
                'F d, Y',          // March 15, 2024
            ];

            foreach ($formats as $format) {
                try {
                    $date = \Carbon\Carbon::createFromFormat($format, trim($dateString));
                    if ($date) {
                        return $date;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }

            // Try Carbon's flexible parsing as last resort
            return \Carbon\Carbon::parse($dateString);

        } catch (\Exception $e) {
            Log::warning('Failed to parse audio date', [
                'date_string' => $dateString,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
}
