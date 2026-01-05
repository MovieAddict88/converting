# NeonVox Karaoke Platform - PHP/MySQL Version

A modern, responsive karaoke platform built with pure PHP and MySQL, featuring auto-installation and a powerful admin dashboard.

## Features

- 🎤 **Karaoke Player**: Support for YouTube videos, MP4, MP3, MIDI, and KAR files
- 🎨 **Modern Design**: Neon-themed dark UI with glassmorphism effects
- 📱 **Fully Responsive**: Optimized for smartphones, tablets, desktops, and smart TVs
- 🔒 **Admin Dashboard**: Secure content management with authentication
- 📊 **Real-time Scoring**: Track performance with accuracy metrics
- 🎵 **YouTube Integration**: Search and add karaoke videos from YouTube
- 📤 **File Uploads**: Upload and manage your own karaoke files
- 🏆 **Leaderboard**: Compete with other singers
- 🚀 **Auto-Installation**: One-click setup with installer wizard

## Responsive Design

The platform is fully responsive across all devices:

- **Smartphones** (320px - 640px): Optimized touch controls, stacked layouts
- **Tablets** (641px - 1024px): Two-column grids, improved touch targets
- **Desktops** (1025px - 1919px): Three-column grids, hover effects
- **Smart TVs** (1920px+): Large touch targets, simplified navigation, optimized viewing

**Responsive Technologies Used:**
- CSS `clamp()` for fluid typography
- CSS Grid with `minmax()` for responsive layouts
- CSS Flexbox for flexible components
- Media queries for breakpoint-based adjustments
- Viewport-relative units (vw, vh) for scaling

## Requirements

- PHP 7.4 or higher
- MySQL 5.7 or higher / MariaDB 10.2 or higher
- Apache web server with mod_rewrite enabled
- Required PHP extensions:
  - mysqli
  - json
  - mbstring

## Installation

### Quick Install

1. **Upload files** to your web server
2. **Visit the installer**: `http://your-domain.com/install.php`
3. **Follow the wizard**:
   - Step 1: Verify system requirements
   - Step 2: Enter database and admin credentials
   - Step 3: Complete installation

### Manual Installation

If you prefer manual setup:

1. **Create MySQL database**
   ```sql
   CREATE DATABASE neonvox CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```

2. **Import schema**
   ```bash
   mysql -u username -p database_name < schema.sql
   ```

3. **Configure**
   Copy `config.example.php` to `config.php` and update database credentials:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'your_db_user');
   define('DB_PASS', 'your_db_password');
   define('DB_NAME', 'neonvox');
   ```

4. **Set permissions**
   ```bash
   chmod 755 uploads/
   chmod 644 config.php
   ```

5. **Delete installer** (for security)
   ```bash
   rm install.php
   ```

## Directory Structure

```
neonvox/
├── admin/
│   ├── login.php          # Admin login page
│   └── dashboard.php      # Admin dashboard
├── api/
│   ├── index.php          # API router
│   ├── auth.php           # Authentication endpoints
│   ├── songs.php          # Songs CRUD endpoints
│   ├── scores.php         # Scores endpoints
│   ├── youtube.php        # YouTube search endpoint
│   └── upload.php         # File upload handler
├── uploads/               # User-uploaded files (auto-created)
├── config.php             # Configuration file (created by installer)
├── config.example.php     # Configuration template
├── schema.sql             # Database schema
├── index.php              # Homepage
├── player.php             # Karaoke player
├── install.php            # Installation wizard
├── .htaccess              # Apache configuration
└── README_PHP.md          # This file
```

## Default Credentials

After installation:

- **Admin URL**: `http://your-domain.com/admin/login.php`
- **Username**: As specified during installation (default: `admin`)
- **Password**: As specified during installation

## API Endpoints

### Authentication
- `POST /api/auth/login` - Admin login
- `POST /api/auth/verify` - Verify token

### Songs
- `GET /api/songs` - List all songs (optional `?search=query`)
- `GET /api/songs/:id` - Get single song
- `POST /api/songs` - Create song (auth required)
- `DELETE /api/songs/:id` - Delete song (auth required)
- `POST /api/upload` - Upload song file (auth required)

### Scores
- `POST /api/scores` - Submit score
- `GET /api/scores/:songId` - Get scores for song
- `GET /api/scores/leaderboard` - Get global leaderboard

### YouTube
- `GET /api/youtube/search` - Search YouTube videos

## Configuration

Edit `config.php` to customize:

```php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASS', 'password');
define('DB_NAME', 'database_name');

// Site Configuration
define('SITE_URL', 'https://your-domain.com');
define('SITE_NAME', 'NeonVox');
define('JWT_SECRET', 'your-secret-key');

// Upload Configuration
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 100 * 1024 * 1024); // 100MB
```

## Usage

### For Visitors

1. Browse songs on the homepage
2. Search by title or artist
3. Click on a song to open the player
4. Sing along and get scored!

### For Admins

1. Log in to admin dashboard
2. Upload karaoke files (MP4, MP3, MIDI, KAR)
3. Search and add YouTube karaoke videos
4. Manage your song library
5. Delete unwanted songs

## Supported File Types

- **YouTube**: Direct integration via API
- **MP4**: Video files
- **MP3/WAV**: Audio files
- **MIDI/KAR**: MIDI karaoke files

## Browser Compatibility

- Chrome/Edge 90+
- Firefox 88+
- Safari 14+
- Opera 76+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Security Recommendations

1. **Change default admin password** immediately after installation
2. **Delete install.php** after setup
3. **Use HTTPS** in production
4. **Keep PHP updated** to the latest version
5. **Restrict file uploads** to trusted users
6. **Regularly backup** your database
7. **Set appropriate file permissions**
8. **Use strong JWT_SECRET** in config.php

## Troubleshooting

### Installation fails

- Check PHP version (`php -v`)
- Verify MySQL connection
- Ensure file permissions are correct
- Check error logs for details

### File uploads not working

- Verify `uploads/` directory exists and is writable
- Check `MAX_UPLOAD_SIZE` in config.php
- Verify PHP `upload_max_filesize` and `post_max_size` in php.ini
- Check server disk space

### API not responding

- Verify `.htaccess` is being processed
- Check mod_rewrite is enabled in Apache
- Verify config.php exists and has correct permissions
- Check error logs

### YouTube search not working

- Ensure you have a valid YouTube Data API v3 key
- Check API quota limits
- Verify network connectivity to youtube.com

## Performance Optimization

- Enable gzip compression (included in .htaccess)
- Set appropriate cache headers for static assets
- Use CDN for images/videos in production
- Optimize database queries with indexes (included in schema)
- Minify CSS/JS for production

## License

This project is provided as-is for educational and commercial use.

## Support

For issues, questions, or contributions, please refer to your project management system.

## Credits

Design inspired by modern neon/cyberpunk aesthetics with responsive-first development principles.
