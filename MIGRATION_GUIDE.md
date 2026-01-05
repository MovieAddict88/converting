# Migration Guide: React/Python → PHP/MySQL

This document explains the conversion from the original React/FastAPI/MongoDB stack to the new PHP/MySQL implementation.

## Architecture Changes

### Original Stack
- **Frontend**: React 19 with Vite/CRA
- **Backend**: Python FastAPI
- **Database**: MongoDB
- **Authentication**: JWT with bcrypt
- **File Storage**: Local filesystem

### New Stack
- **Frontend**: Pure PHP (server-side rendered)
- **Backend**: PHP with custom API
- **Database**: MySQL
- **Authentication**: JWT with bcrypt (same logic, PHP implementation)
- **File Storage**: Local filesystem

## Component Mapping

### Frontend Pages

| React Component | PHP Equivalent | Notes |
|---------------|----------------|-------|
| `src/App.js` | `index.php` | Main router logic |
| `src/pages/Home.js` | `index.php` | Homepage with song browsing |
| `src/pages/AdminLogin.js` | `admin/login.php` | Admin authentication |
| `src/pages/AdminDashboard.js` | `admin/dashboard.php` | Content management |
| `src/pages/Player.js` | `player.php` | Karaoke player |

### Backend Endpoints

| FastAPI Route | PHP Route | Implementation |
|---------------|-----------|----------------|
| `POST /api/auth/login` | `POST /api/auth/login` | Same JWT logic |
| `POST /api/songs` | `POST /api/songs` | MySQL INSERT |
| `GET /api/songs` | `GET /api/songs` | MySQL SELECT |
| `DELETE /api/songs/:id` | `DELETE /api/songs/:id` | MySQL DELETE |
| `POST /api/scores` | `POST /api/scores` | MySQL INSERT |
| `GET /api/scores/:songId` | `GET /api/scores/:songId` | MySQL SELECT |
| `GET /api/leaderboard` | `GET /api/scores/leaderboard` | MySQL SELECT |
| `POST /api/songs/upload` | `POST /api/upload` | Same file handling |
| `GET /api/youtube/search` | `GET /api/youtube/search` | Same YouTube API |

### Database Schema

#### MongoDB Collections → MySQL Tables

**MongoDB `admins` → MySQL `admins`**
```javascript
// MongoDB
{
  username: string,
  password: string (bcrypt hash),
  created_at: Date,
  updated_at: Date
}
```

```sql
-- MySQL
CREATE TABLE admins (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) UNIQUE,
  password VARCHAR(255),
  created_at TIMESTAMP,
  updated_at TIMESTAMP
)
```

**MongoDB `songs` → MySQL `songs`**
```javascript
// MongoDB
{
  id: string (UUID),
  title: string,
  artist: string,
  source_type: enum,
  source_url: string (optional),
  file_path: string (optional),
  thumbnail: string (optional),
  duration: number (optional),
  lyrics: string (optional),
  created_at: Date
}
```

```sql
-- MySQL
CREATE TABLE songs (
  id VARCHAR(36) PRIMARY KEY,
  title VARCHAR(255),
  artist VARCHAR(255),
  source_type ENUM(...),
  source_url TEXT,
  file_path VARCHAR(500),
  thumbnail VARCHAR(500),
  duration INT UNSIGNED,
  lyrics TEXT,
  plays_count INT UNSIGNED DEFAULT 0,
  created_at TIMESTAMP,
  updated_at TIMESTAMP,
  INDEX idx_title (title),
  FULLTEXT INDEX ft_search (title, artist)
)
```

**MongoDB `scores` → MySQL `scores`**
```javascript
// MongoDB
{
  id: string (UUID),
  song_id: string,
  player_name: string,
  score: number,
  accuracy: number,
  created_at: Date
}
```

```sql
-- MySQL
CREATE TABLE scores (
  id VARCHAR(36) PRIMARY KEY,
  song_id VARCHAR(36),
  player_name VARCHAR(100),
  score INT UNSIGNED,
  accuracy DECIMAL(5,2),
  perfect_hits INT UNSIGNED,
  good_hits INT UNSIGNED,
  miss_hits INT UNSIGNED,
  created_at TIMESTAMP,
  FOREIGN KEY (song_id) REFERENCES songs(id) ON DELETE CASCADE,
  INDEX idx_score (score)
)
```

## Features Preserved

✅ **Admin Authentication** - JWT-based login system
✅ **Song Management** - CRUD operations for songs
✅ **File Uploads** - Support for MP4, MP3, MIDI, KAR files
✅ **YouTube Integration** - Search and add videos
✅ **Scoring System** - Real-time score tracking
✅ **Leaderboard** - Global and per-song rankings
✅ **Search Functionality** - Full-text search for songs
✅ **Responsive Design** - Mobile to Smart TV support

## New Features Added

🚀 **Auto-Installation Script** - `install.php` wizard
🚀 **Standalone Admin Dashboard** - Pure PHP implementation
🚀 **Enhanced Responsive Design** - Better mobile/tablet/TV support
🚀 **CSS Clamp() Typography** - Fluid font sizing
🚀 **Improved Performance** - Server-side rendering
🚀 **SEO Friendly** - Server-rendered pages
🚀 **Simpler Deployment** - No Node.js required

## Responsive Design Improvements

### Breakpoints
- **Mobile**: 320px - 640px (Stacked layouts)
- **Tablet**: 641px - 1024px (Two-column grids)
- **Desktop**: 1025px - 1919px (Three-column grids)
- **Smart TV**: 1920px+ (Four-five column grids, larger touch targets)

### Technologies Used
- `clamp()` for fluid typography across all sizes
- CSS Grid with `minmax()` for adaptive layouts
- Viewport units (`vw`, `vh`) for scaling
- Media queries for breakpoint-specific adjustments
- Touch-friendly controls (44px+ minimum touch targets)

## Data Migration

If you have existing data in MongoDB:

### 1. Export from MongoDB
```bash
mongodump --db your_db_name --out ./mongo_backup
```

### 2. Convert to MySQL format
Use the migration script (custom Python script would be needed)

### 3. Import to MySQL
```bash
mysql -u username -p database_name < converted_data.sql
```

### 4. Migrate uploaded files
```bash
# Copy from Python backend uploads to PHP uploads
cp -r backend/uploads/* uploads/
```

## Deployment Comparison

### Original Stack Requirements
```
- Node.js 18+
- Python 3.9+
- MongoDB 4.4+
- PM2/process manager for Node and Python
- Two separate servers (or proxy setup)
```

### New Stack Requirements
```
- PHP 7.4+
- MySQL 5.7+
- Apache/Nginx with PHP
- Single server deployment
```

## Configuration Changes

### Environment Variables
**Before (Python)**
```bash
MONGO_URL=mongodb://localhost:27017/neonvox
DB_NAME=neonvox
JWT_SECRET=your-secret
CORS_ORIGINS=*
```

**After (PHP)**
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'username');
define('DB_PASS', 'password');
define('DB_NAME', 'neonvox');
define('JWT_SECRET', 'your-secret');
define('SITE_URL', 'http://your-domain.com');
```

## Performance Improvements

1. **Server-Side Rendering**: No client-side bundle
2. **MySQL Caching**: Built-in query cache
3. **Fewer HTTP Requests**: No separate API calls for initial load
4. **Simpler Stack**: Less memory overhead
5. **Better Caching**: Browser can cache HTML responses

## Security Considerations

### Preserved
- JWT authentication
- Password hashing with bcrypt
- Input sanitization
- SQL injection prevention (prepared statements)

### Enhanced
- `.htaccess` security rules
- File type restrictions
- Protected config files
- No client-side secrets exposed

## Breaking Changes

1. **No Node.js Build Process** - Direct PHP files
2. **Database Changed** - MongoDB → MySQL (requires migration)
3. **API Response Format** - Minor differences in datetime formatting
4. **File Structure** - New directory organization

## Rollback Procedure

If you need to revert to the original stack:

1. **Restore MongoDB database**:
   ```bash
   mongorestore --db your_db_name ./mongo_backup/your_db_name
   ```

2. **Restore uploaded files**:
   ```bash
   cp -r uploads/* backend/uploads/
   ```

3. **Reinstall dependencies**:
   ```bash
   cd frontend && npm install
   cd ../backend && pip install -r requirements.txt
   ```

4. **Start services**:
   ```bash
   # Start MongoDB
   # Start Python backend
   cd backend && python server.py
   # Start React frontend
   cd frontend && npm start
   ```

## Testing Checklist

- [ ] Admin login works
- [ ] Songs display correctly
- [ ] Search functionality works
- [ ] File uploads work (MP4, MP3, MIDI, KAR)
- [ ] YouTube search works
- [ ] Player loads and plays media
- [ ] Scoring system works
- [ ] Score submission works
- [ ] Leaderboard displays correctly
- [ ] Responsive design on mobile
- [ ] Responsive design on tablet
- [ ] Responsive design on desktop
- [ ] Responsive design on TV (1920px+)

## Support

For migration issues or questions, refer to:
- README_PHP.md for PHP/MySQL documentation
- Original README.md for React/Python reference
