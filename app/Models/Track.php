<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Track extends Model
{
    protected $fillable = [
        'title',
        'artist',
        'album',
        'genre',
        'comment',
        'year',
        'duration',
        'file_path',
        'file_name',
        'file_extension',
        'file_size',
        'cdn_url',
        'streaming_url',
        'cover_art_url',
        'metadata',
        'last_synced_at',
        'is_active',
    ];

    protected $casts = [
        'metadata' => 'array',
        'last_synced_at' => 'datetime',
        'is_active' => 'boolean',
        'year' => 'integer',
        'duration' => 'integer',
        'file_size' => 'integer',
        'release_date' => 'date',
    ];

    protected $appends = [
        'formatted_duration',
        'formatted_file_size',
    ];

    /**
     * Get the formatted duration in minutes:seconds format
     */
    protected function formattedDuration(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->duration ? gmdate('i:s', $this->duration) : null,
        );
    }

    /**
     * Get the formatted file size in human readable format
     */
    protected function formattedFileSize(): Attribute
    {
        return Attribute::make(
            get: function () {
                if (!$this->file_size) return null;

                $bytes = $this->file_size;
                $units = ['B', 'KB', 'MB', 'GB'];

                for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
                    $bytes /= 1024;
                }

                return round($bytes, 2) . ' ' . $units[$i];
            }
        );
    }

    /**
     * Get the formatted date
     */
    protected function formattedDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Priority 1: Use release_date if available
                if ($this->release_date) {
                    return $this->release_date->format('jS F, Y');
                }

                // Priority 2: If we have a specific year, create a date from it
                if ($this->year) {
                    try {
                        // Create a date from the year (assuming January 1st)
                        $date = \Carbon\Carbon::createFromDate($this->year, 1, 1);
                        return $date->format('jS F, Y');
                    } catch (\Exception $e) {
                        return (string) $this->year;
                    }
                }

                // Priority 3: If we have created_at date, format it
                if ($this->created_at) {
                    return $this->created_at->format('jS F, Y');
                }

                return null;
            },
        );
    }

    /**
     * Get the short formatted date (for compact display)
     */
    protected function shortFormattedDate(): Attribute
    {
        return Attribute::make(
            get: function () {
                // Priority 1: Use release_date if available
                if ($this->release_date) {
                    return $this->release_date->format('M Y'); // e.g., "Mar 2024"
                }

                // Priority 2: If we have a specific year, just return the year
                if ($this->year) {
                    return (string) $this->year;
                }

                // Priority 3: If we have created_at date, format it as short date
                if ($this->created_at) {
                    return $this->created_at->format('M Y'); // e.g., "Jul 2025"
                }

                return null;
            },
        );
    }

    /**
     * Scope to get only active tracks
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope to search tracks by title or artist
     */
    public function scopeSearch($query, $search)
    {
        return $query->where(function ($q) use ($search) {
            $q->where('title', 'like', "%{$search}%")
              ->orWhere('artist', 'like', "%{$search}%")
              ->orWhere('album', 'like', "%{$search}%");
        });
    }

    /**
     * Scope to filter by genre
     */
    public function scopeByGenre($query, $genre)
    {
        return $query->where('genre', $genre);
    }

    /**
     * Get all unique genres
     */
    public static function getGenres()
    {
        return static::whereNotNull('genre')
            ->distinct()
            ->pluck('genre')
            ->sort()
            ->values();
    }

    /**
     * Get all unique artists
     */
    public static function getArtists()
    {
        return static::whereNotNull('artist')
            ->distinct()
            ->pluck('artist')
            ->sort()
            ->values();
    }
}
