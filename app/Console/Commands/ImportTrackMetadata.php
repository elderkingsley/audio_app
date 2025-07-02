<?php

namespace App\Console\Commands;

use App\Models\Track;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class ImportTrackMetadata extends Command
{
    protected $signature = 'tracks:import-metadata {file}';
    protected $description = 'Import track metadata from CSV file';

    public function handle()
    {
        $filePath = $this->argument('file');
        
        if (!file_exists($filePath)) {
            $this->error("File not found: {$filePath}");
            return 1;
        }

        $csv = array_map('str_getcsv', file($filePath));
        $header = array_shift($csv);
        
        $this->info('CSV Header: ' . implode(', ', $header));
        $this->info('Processing ' . count($csv) . ' rows...');
        
        $updated = 0;
        $notFound = 0;
        
        foreach ($csv as $row) {
            $data = array_combine($header, $row);
            
            // Find track by title or filename
            $track = Track::where('title', $data['title'])
                         ->orWhere('file_name', $data['title'] . '.mp3')
                         ->first();
            
            if ($track) {
                $track->update([
                    'artist' => $data['artist'] ?? $track->artist,
                    'album' => $data['album'] ?? $track->album,
                    'genre' => $data['genre'] ?? $track->genre,
                    'comment' => $data['comment'] ?? $track->comment,
                ]);
                
                $this->line("✅ Updated: {$track->title}");
                $updated++;
            } else {
                $this->line("❌ Not found: {$data['title']}");
                $notFound++;
            }
        }
        
        $this->info("\n📊 Import Summary:");
        $this->info("✅ Updated: {$updated}");
        $this->info("❌ Not found: {$notFound}");
        
        return 0;
    }
}
