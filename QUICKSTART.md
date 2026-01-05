# Quick Start Guide - NeonVox PHP/MySQL

## 5-Minute Installation

### 1. Upload Files
```
Upload all files to your web server directory (e.g., /public_html/neonvox)
```

### 2. Run Installer
```
Visit: http://your-domain.com/install.php
```

### 3. Complete 3-Step Wizard

**Step 1: System Check**
- Installer automatically verifies PHP version (7.4+)
- Checks for required extensions (mysqli, json, mbstring)
- Verifies directory permissions
- Click "Continue" if all checks pass ✓

**Step 2: Database Setup**
```
Database Host: localhost
Database User: your_mysql_username
Database Password: your_mysql_password
Database Name: neonvox (or create new)
Site URL: http://your-domain.com
Admin Username: admin (or choose)
Admin Password: Choose a strong password
```
Click "Install NeonVox"

**Step 3: Complete!**
- Installation finished ✓
- Click "Visit Site" to see homepage
- Click "Admin Panel" to access dashboard

### 4. First Steps After Installation

**Log in to Admin:**
```
URL: http://your-domain.com/admin/login.php
Username: admin (or what you chose)
Password: (what you set during installation)
```

**Add Your First Song:**

Option A - Upload File:
1. Click "Upload File" button
2. Enter title and artist
3. Choose file (MP4, MP3, MIDI, or KAR)
4. Optionally add lyrics
5. Click "Upload Song"

Option B - YouTube Search:
1. Click "YouTube Search" button
2. Enter your YouTube Data API v3 key
3. Search for karaoke videos
4. Click "Add" on desired video

**Test Your Site:**
1. Visit homepage
2. Click on a song card
3. Player opens - start singing!

## Common Questions

### What if installation fails?

**"Database connection failed"**
- Verify MySQL credentials are correct
- Check if MySQL server is running
- Ensure database exists or installer has CREATE permission

**"Configuration file not found"**
- Make sure config.php was created during installation
- Check directory permissions (755)

**"Requirements not met"**
- Upgrade PHP to 7.4 or higher
- Install missing extensions via your hosting control panel
- Enable required PHP extensions

### How do I delete the installer?

For security, delete install.php after successful installation:
```bash
rm install.php
```

Or via FTP/file manager: Delete the install.php file

### What are the default credentials?

Set during installation. Defaults:
- URL: http://your-domain.com/admin/login.php
- Username: admin (unless you changed it)
- Password: What you entered during installation

### How do I change my admin password?

Currently, you'll need to:
1. Access MySQL/phpMyAdmin
2. Find the `admins` table
3. Update the password field with a new bcrypt hash

Or create a new admin via MySQL:
```sql
INSERT INTO admins (username, password, created_at)
VALUES ('newuser', '$2y$10$NEW_BCRYPT_HASH', NOW());
```

### Where do uploaded files go?

- Location: `uploads/` directory (auto-created)
- Max file size: 100MB (configurable in config.php)
- Supported formats: MP4, MP3, WebM, MID, MIDI, KAR, WAV

### How do I get a YouTube API key?

1. Go to Google Cloud Console
2. Create a new project
3. Enable YouTube Data API v3
4. Create credentials (API Key)
5. Add to admin dashboard when searching

### Can I use this on shared hosting?

Yes! Requirements are minimal:
- PHP 7.4+
- MySQL database
- Most shared hosting supports this

### How do I make the site secure?

1. ✓ Delete install.php after installation
2. ✓ Use HTTPS (SSL certificate)
3. ✓ Use strong admin password
4. ✓ Change JWT_SECRET in config.php
5. ✓ Keep PHP and MySQL updated
6. ✓ Regular database backups

### What if I need to reinstall?

1. Drop existing MySQL database
2. Delete config.php
3. Delete uploads directory contents
4. Run install.php again
5. Re-configure with same or new settings

## File Structure Overview

```
neonvox/
├── admin/           # Admin panel (login.php, dashboard.php)
├── api/             # PHP API endpoints
├── uploads/         # Uploaded files (auto-created)
├── config.php       # Created by installer - DO NOT EDIT
├── index.php        # Homepage
├── player.php       # Karaoke player
└── install.php      # Installation wizard (DELETE after use)
```

## Responsive Device Support

✅ **Smartphones** - 320px to 640px
- Touch-optimized
- Stacked layouts
- Bottom navigation

✅ **Tablets** - 641px to 1024px
- Two-column grids
- Enhanced spacing
- Larger touch targets

✅ **Desktops** - 1025px to 1919px
- Three-column grids
- Hover effects
- Full features

✅ **Smart TVs** - 1920px+
- Four-five columns
- Extra-large controls
- TV-optimized navigation

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+
- Opera 76+
- Mobile browsers (iOS Safari, Chrome Mobile)

## Getting Help

**Documentation:**
- `README_PHP.md` - Complete documentation
- `MIGRATION_GUIDE.md` - Converting from React/Python
- `PROJECT_SUMMARY.md` - Technical overview

**Troubleshooting:**
1. Check browser console (F12) for JavaScript errors
2. Check server error logs
3. Verify config.php exists and has correct permissions
4. Test database connection with phpMyAdmin

**Common Issues:**

| Issue | Solution |
|-------|----------|
| White screen | Check error logs, enable PHP errors |
| Can't upload files | Check uploads directory permissions (755) |
| Login fails | Clear browser cache, verify credentials |
| API not working | Check .htaccess, verify mod_rewrite enabled |
| YouTube fails | Verify API key is valid, check quota |

## Performance Tips

1. **Enable caching** - .htaccess includes cache headers
2. **Use CDN** - Host static assets on CDN in production
3. **Optimize images** - Compress thumbnails before upload
4. **Database indexes** - Schema includes optimal indexes
5. **Gzip compression** - Enabled by .htaccess

## Next Steps

After successful installation:

1. ✓ Add songs to library
2. ✓ Test player functionality
3. ✓ Verify admin panel works
4. ✓ Set up regular database backups
5. ✓ Consider SSL certificate
6. ✓ Monitor upload directory size
7. ✓ Delete install.php for security

## Configuration Options

Edit `config.php` (after installation) to customize:

```php
// Database
define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASS', 'password');
define('DB_NAME', 'neonvox');

// Site
define('SITE_URL', 'https://your-domain.com');
define('SITE_NAME', 'NeonVox');
define('JWT_SECRET', 'random-secret-key');

// Uploads
define('UPLOAD_DIR', __DIR__ . '/uploads/');
define('MAX_UPLOAD_SIZE', 100 * 1024 * 1024); // 100MB
```

## Maintenance

**Weekly:**
- Check upload directory size
- Monitor database size
- Review recent uploads

**Monthly:**
- Database backup
- Check for PHP/MySQL updates
- Review security logs

**Yearly:**
- Update JWT_SECRET
- Review and rotate passwords
- Full system audit

## Uninstallation

To completely remove NeonVox:

1. Delete all PHP files and directories
2. Delete uploads directory
3. Drop MySQL database
4. Delete MySQL user (if created specifically for this)

## License

This project is provided as-is for educational and commercial use.

---

**Installation Time:** 5-10 minutes
**Difficulty:** Beginner-friendly
**Requirements:** PHP 7.4+, MySQL, standard web hosting

**Need Help?** Refer to full documentation in `README_PHP.md`
