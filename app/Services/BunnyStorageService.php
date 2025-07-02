<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class BunnyStorageService
{
    private Client $client;
    private string $storageZone;
    private string $storagePassword;
    private string $storageHostname;
    private string $cdnHostname;
    private bool $useSSL;

    public function __construct()
    {
        $this->storageZone = config('bunny.storage.zone_name');
        $this->storagePassword = config('bunny.storage.password');
        $this->storageHostname = config('bunny.storage.hostname');
        $this->cdnHostname = config('bunny.cdn.hostname');
        $this->useSSL = config('bunny.cdn.use_ssl');

        $this->client = new Client([
            'timeout' => config('bunny.api.timeout'),
            'headers' => [
                'AccessKey' => $this->storagePassword,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    /**
     * List all music files in the storage zone
     */
    public function listMusicFiles(string $directory = ''): array
    {
        $cacheKey = 'bunny_music_files_' . md5($directory);

        return Cache::remember($cacheKey, 60, function () use ($directory) {
            try {
                $path = $this->buildStoragePath($directory);
                Log::info('Bunny.net API Request', ['url' => $path]);

                $response = $this->client->get($path);
                $responseBody = $response->getBody()->getContents();
                $files = json_decode($responseBody, true);

                Log::info('Bunny.net API Response', [
                    'status' => $response->getStatusCode(),
                    'file_count' => is_array($files) ? count($files) : 0,
                    'first_file' => is_array($files) && !empty($files) ? $files[0] : null
                ]);

                if (!is_array($files)) {
                    Log::error('Invalid response from Bunny.net API', ['response' => $responseBody]);
                    return [];
                }

                return $this->filterMusicFiles($files);
            } catch (GuzzleException $e) {
                Log::error('Failed to list music files from Bunny.net', [
                    'error' => $e->getMessage(),
                    'directory' => $directory,
                    'url' => $this->buildStoragePath($directory),
                ]);
                return [];
            }
        });
    }

    /**
     * Get file information from Bunny.net storage
     */
    public function getFileInfo(string $filePath): ?array
    {
        try {
            $path = $this->buildStoragePath($filePath);
            $response = $this->client->get($path);
            $fileData = json_decode($response->getBody()->getContents(), true);

            if (is_array($fileData) && !empty($fileData)) {
                return $fileData[0] ?? null;
            }

            return $fileData;
        } catch (GuzzleException $e) {
            Log::error('Failed to get file info from Bunny.net', [
                'error' => $e->getMessage(),
                'file_path' => $filePath,
            ]);
            return null;
        }
    }

    /**
     * Get the CDN URL for a music file
     */
    public function getCdnUrl(string $filePath): string
    {
        $protocol = $this->useSSL ? 'https' : 'http';
        $cleanPath = ltrim($filePath, '/');

        // Remove storage zone name from path if it exists (Bunny.net API includes it)
        $storageZonePrefix = $this->storageZone . '/';
        if (strpos($cleanPath, $storageZonePrefix) === 0) {
            $cleanPath = substr($cleanPath, strlen($storageZonePrefix));
        }

        // URL encode the filename to handle spaces and special characters
        $encodedPath = rawurlencode($cleanPath);

        return "{$protocol}://{$this->cdnHostname}/{$encodedPath}";
    }

    /**
     * Get streaming URL for a music file
     */
    public function getStreamingUrl(string $filePath): string
    {
        return $this->getCdnUrl($filePath);
    }

    /**
     * Check if file exists in storage
     */
    public function fileExists(string $filePath): bool
    {
        $fileInfo = $this->getFileInfo($filePath);
        return $fileInfo !== null;
    }

    /**
     * Build the storage API path
     */
    private function buildStoragePath(string $path = ''): string
    {
        $baseUrl = "https://{$this->storageHostname}/{$this->storageZone}";
        $cleanPath = ltrim($path, '/');
        
        return $cleanPath ? "{$baseUrl}/{$cleanPath}/" : "{$baseUrl}/";
    }

    /**
     * Filter files to only include music files
     */
    private function filterMusicFiles(array $files): array
    {
        $allowedExtensions = config('bunny.music.allowed_extensions');
        $musicFiles = [];

        foreach ($files as $file) {
            if (!isset($file['IsDirectory']) || $file['IsDirectory']) {
                continue;
            }

            $extension = strtolower(pathinfo($file['ObjectName'], PATHINFO_EXTENSION));
            
            if (in_array($extension, $allowedExtensions)) {
                $filePath = isset($file['Path']) ? $file['Path'] : '';
                $fileName = $file['ObjectName'];

                // For CDN URLs, we only need the filename (not the full storage path)
                $cdnPath = $fileName;

                $musicFiles[] = [
                    'name' => $fileName,
                    'size' => $file['Length'] ?? 0,
                    'last_modified' => $file['LastChanged'] ?? '',
                    'path' => $filePath,
                    'extension' => $extension,
                    'cdn_url' => $this->getCdnUrl($cdnPath),
                    'streaming_url' => $this->getStreamingUrl($cdnPath),
                ];
            }
        }

        return $musicFiles;
    }

    /**
     * Get music files from a specific directory with caching
     */
    public function getMusicFromDirectory(string $directory = null): array
    {
        $musicDir = $directory ?? config('bunny.music.music_directory');
        return $this->listMusicFiles($musicDir);
    }

    /**
     * Clear the music files cache
     */
    public function clearCache(?string $directory = ''): void
    {
        $directory = $directory ?? '';
        $cacheKey = 'bunny_music_files_' . md5($directory);
        Cache::forget($cacheKey);
    }
}
