# 🎵 Laravel Music Player with Bunny.net Storage

A modern, responsive music player application built with Laravel that connects to Bunny.net storage via API. Features a clean interface with no user authentication required.

## ✨ Features

- **🎧 Modern Music Player Interface** - Clean, responsive design with play/pause, next/previous controls
- **☁️ Bunny.net Integration** - Direct streaming from Bunny.net CDN storage
- **🔍 Search & Filter** - Search tracks by title, artist, or album with genre filtering
- **📱 Mobile-First Design** - Optimized for mobile with dedicated mobile player controls
- **🎨 Black & Yellow Theme** - Sleek black background with vibrant yellow accents
- **💫 Glass Morphism UI** - Modern glass effects and smooth animations
- **🔄 Auto-Sync** - Sync music files from Bunny.net storage to local database
- **🚫 No Authentication** - Public access music player (as requested)
- **📱 Mobile Player** - Sticky bottom player for mobile devices
- **🎵 Touch-Friendly** - Large touch targets and mobile-optimized interactions

## 🛠️ Technology Stack

- **Backend**: Laravel 12 with PHP 8.2+
- **Frontend**: Tailwind CSS 4, Vanilla JavaScript
- **Database**: SQLite (default) or MySQL/PostgreSQL
- **Storage**: Bunny.net CDN
- **HTTP Client**: Guzzle (included with Laravel)

## 📋 Prerequisites

- PHP 8.2 or higher
- Composer
- Node.js and npm (for asset compilation)
- Bunny.net account with storage zone configured

## 🚀 Installation

1. **Clone and setup the project** (already done in your case):
   ```bash
   cd Apps/audio_app
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**:
   ```bash
   npm install
   ```

4. **Configure environment variables**:
   Edit `.env` file and add your Bunny.net credentials:
   ```env
   BUNNY_STORAGE_ZONE_NAME=your-storage-zone-name
   BUNNY_STORAGE_PASSWORD=your-storage-password
   BUNNY_STORAGE_HOSTNAME=storage.bunnycdn.com
   BUNNY_CDN_HOSTNAME=your-pull-zone.b-cdn.net
   BUNNY_MUSIC_DIRECTORY=music/
   ```

5. **Run database migrations**:
   ```bash
   php artisan migrate
   ```

6. **Build frontend assets**:
   ```bash
   npm run build
   ```

7. **Start the development server**:
   ```bash
   php artisan serve
   ```

## 🎵 Usage

### Initial Setup

1. **Upload music files** to your Bunny.net storage zone in the configured music directory
2. **Visit the application** in your browser (http://localhost:8000)
3. **Click "Sync Music"** to import tracks from Bunny.net storage
4. **Start listening** to your music!

### Supported Audio Formats

- MP3
- WAV
- FLAC
- AAC
- M4A
- OGG

### Command Line Sync

You can also sync music files using the Artisan command:

```bash
# Sync all music files
php artisan music:sync

# Sync from specific directory
php artisan music:sync --directory=albums/

# Force re-sync all files
php artisan music:sync --force
```

## 🔧 Configuration

### Bunny.net Setup

1. Create a storage zone in your Bunny.net account
2. Create a pull zone connected to your storage zone
3. Upload your music files to the storage zone
4. Update the `.env` file with your credentials

### File Organization

For best results, organize your music files with descriptive names:
- `Artist - Song Title.mp3`
- `Artist - Album - Song Title.mp3`
- `01 - Artist - Song Title.mp3`

The application will automatically extract metadata from filenames.

## 🎨 Customization

### Styling

The application uses Tailwind CSS with custom components. You can modify styles in:
- `resources/css/app.css` - Custom CSS and component styles
- `resources/views/music-player/index.blade.php` - Main template

### Player Functionality

Enhance the music player by modifying:
- `public/js/music-player.js` - Frontend JavaScript functionality
- `app/Http/Controllers/MusicPlayerController.php` - Backend API endpoints

## 📡 API Endpoints

- `GET /api/music/tracks` - Get all tracks with search/filter support
- `GET /api/music/tracks/{id}` - Get specific track details
- `GET /api/music/genres` - Get available genres
- `GET /api/music/artists` - Get available artists
- `GET /api/music/stats` - Get library statistics
- `GET /api/music/stream/{id}` - Stream track (redirects to CDN)
- `POST /api/music/sync` - Sync tracks from Bunny.net

## 🔒 Security Notes

Since this application has no authentication (as requested), consider:
- Implementing rate limiting for API endpoints
- Restricting access by IP if needed
- Using HTTPS in production
- Securing your Bunny.net credentials

## 🐛 Troubleshooting

### Common Issues

1. **Tracks not loading**: Check Bunny.net credentials in `.env`
2. **Audio not playing**: Verify CDN URLs and CORS settings
3. **Sync failing**: Ensure storage zone permissions are correct
4. **Styling issues**: Run `npm run build` to compile assets

### Logs

Check Laravel logs for detailed error information:
```bash
tail -f storage/logs/laravel.log
```

## 📄 License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

## 🤝 Contributing

Feel free to submit issues and enhancement requests!

---

**Enjoy your music! 🎶**
