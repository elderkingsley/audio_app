<?php

namespace App\Console\Commands;

use App\Models\Track;
use App\Services\BunnyStorageService;
use Illuminate\Console\Command;

class SyncMusicFromBunny extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'music:sync {--directory= : Specific directory to sync} {--force : Force re-sync all files} {--overwrite-metadata : Overwrite existing metadata instead of preserving it}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync music files from Bunny.net storage to local database';

    /**
     * Execute the console command.
     */
    public function handle(BunnyStorageService $bunnyStorage)
    {
        $this->info('Starting music sync from Bunny.net storage...');

        $directory = $this->option('directory');
        $force = $this->option('force');

        try {
            // Get music files from Bunny.net
            $musicFiles = $bunnyStorage->getMusicFromDirectory($directory);

            if (empty($musicFiles)) {
                $this->warn('No music files found in the specified directory.');
                return Command::SUCCESS;
            }

            $this->info("Found " . count($musicFiles) . " music files to process.");

            $progressBar = $this->output->createProgressBar(count($musicFiles));
            $progressBar->start();

            $syncedCount = 0;
            $skippedCount = 0;
            $errorCount = 0;
            $errors = [];

            foreach ($musicFiles as $file) {
                try {
                    $filePath = $file['path'] . $file['name'];

                    // Check if track already exists and skip if not forcing
                    if (!$force && Track::where('file_path', $filePath)->exists()) {
                        $skippedCount++;
                    } else {
                        $this->createOrUpdateTrack($file, $this->option('overwrite-metadata'));
                        $syncedCount++;
                    }

                } catch (\Exception $e) {
                    $errorCount++;
                    $errors[] = "Failed to sync {$file['name']}: " . $e->getMessage();
                    $this->error("Error syncing {$file['name']}: " . $e->getMessage());
                }

                $progressBar->advance();
            }

            $progressBar->finish();
            $this->newLine(2);

            // Clear cache
            $bunnyStorage->clearCache($directory ?? '');

            // Display results
            $this->info("Sync completed!");
            $this->table(
                ['Metric', 'Count'],
                [
                    ['Total Files', count($musicFiles)],
                    ['Synced', $syncedCount],
                    ['Skipped', $skippedCount],
                    ['Errors', $errorCount],
                ]
            );

            if (!empty($errors)) {
                $this->warn('Errors encountered:');
                foreach ($errors as $error) {
                    $this->line("  - {$error}");
                }
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Sync failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Create or update a track from Bunny.net file data
     */
    private function createOrUpdateTrack(array $fileData, bool $overwriteMetadata = false): Track
    {
        // Enhanced metadata extraction from filename
        $fileName = pathinfo($fileData['name'], PATHINFO_FILENAME);

        // Try different filename patterns
        $metadata = $this->extractMetadataFromFilename($fileName);

        $trackData = [
            'title' => $metadata['title'] ?: $fileName,
            'artist' => $metadata['artist'],
            'album' => $metadata['album'],
            'year' => $metadata['year'],
            'file_path' => $fileData['path'] . $fileData['name'],
            'file_name' => $fileData['name'],
            'file_extension' => $fileData['extension'],
            'file_size' => $fileData['size'],
            'cdn_url' => $fileData['cdn_url'],
            'streaming_url' => $fileData['streaming_url'],
            'last_synced_at' => now(),
            'is_active' => true,
        ];

        // Check if track already exists
        $existingTrack = Track::where('file_path', $trackData['file_path'])->first();

        if ($existingTrack && !$overwriteMetadata) {
            // Preserve existing metadata, only update technical fields
            $updateData = [
                'file_size' => $trackData['file_size'],
                'cdn_url' => $trackData['cdn_url'],
                'streaming_url' => $trackData['streaming_url'],
                'last_synced_at' => $trackData['last_synced_at'],
                'is_active' => $trackData['is_active'],
            ];

            // Only update metadata fields if they are currently empty/null
            if (empty($existingTrack->title)) {
                $updateData['title'] = $trackData['title'];
            }
            if (empty($existingTrack->artist)) {
                $updateData['artist'] = $trackData['artist'];
            }
            if (empty($existingTrack->album)) {
                $updateData['album'] = $trackData['album'];
            }
            if (empty($existingTrack->year)) {
                $updateData['year'] = $trackData['year'];
            }

            $existingTrack->update($updateData);
            return $existingTrack;
        } else {
            // For new tracks or when overwriting is requested, use all data
            return Track::updateOrCreate(
                ['file_path' => $trackData['file_path']],
                $trackData
            );
        }
    }

    /**
     * Extract metadata from filename using various patterns
     */
    private function extractMetadataFromFilename(string $fileName): array
    {
        $metadata = [
            'title' => null,
            'artist' => null,
            'album' => null,
            'year' => null,
        ];

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
        // Pattern 3: Track Number - Artist - Title
        elseif (preg_match('/^\d+\s*[-.]?\s*(.+?)\s*-\s*(.+)$/', $fileName, $matches)) {
            $metadata['artist'] = trim($matches[1]);
            $metadata['title'] = trim($matches[2]);
        }
        // Pattern 4: Extract year if present
        if (preg_match('/\((\d{4})\)/', $fileName, $matches)) {
            $metadata['year'] = (int) $matches[1];
        }

        return $metadata;
    }
}
