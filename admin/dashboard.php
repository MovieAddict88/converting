<?php
require_once __DIR__ . '/../config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Admin | NeonVox</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@400;700;900&family=Outfit:wght@300;400;600&family=Space+Mono:wght@400;700&family=Syne:wght@700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --background: #030305;
            --foreground: #FFFFFF;
            --card: #0A0A0F;
            --primary: #FF0099;
            --primary-foreground: #FFFFFF;
            --secondary: #00F0FF;
            --secondary-foreground: #000000;
            --muted: #1A1A24;
            --muted-foreground: #A1A1AA;
            --border: #27272A;
            --input: #18181B;
            --ring: #FF0099;
            --success: #00FF94;
            --error: #FF0055;
            --sidebar-width: clamp(240px, 15vw, 280px);
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--background);
            color: var(--foreground);
            min-height: 100vh;
            line-height: 1.6;
            display: flex;
        }

        /* Sidebar */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--card);
            border-right: 1px solid var(--border);
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            padding: clamp(1rem, 3vw, 1.5rem);
            z-index: 100;
            transform: translateX(0);
            transition: transform 0.3s ease;
        }

        .sidebar.hidden {
            transform: translateX(-100%);
        }

        .logo {
            font-family: 'Unbounded', sans-serif;
            font-size: clamp(1.5rem, 8vw, 2rem);
            font-weight: 900;
            background: linear-gradient(135deg, #FF0099 0%, #7000FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.05em;
            margin-bottom: clamp(2rem, 8vw, 3rem);
        }

        .nav-menu {
            list-style: none;
        }

        .nav-menu li {
            margin-bottom: 0.5rem;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: clamp(0.75rem, 3vw, 1rem);
            color: var(--muted-foreground);
            text-decoration: none;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
            font-size: clamp(0.9rem, 3vw, 1rem);
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--foreground);
            background: rgba(255, 0, 153, 0.1);
        }

        .nav-link svg {
            width: clamp(1.25rem, 4vw, 1.5rem);
            height: clamp(1.25rem, 4vw, 1.5rem);
            flex-shrink: 0;
        }

        .user-info {
            position: absolute;
            bottom: clamp(1rem, 3vw, 1.5rem);
            left: clamp(1rem, 3vw, 1.5rem);
            right: clamp(1rem, 3vw, 1.5rem);
            padding-top: 1rem;
            border-top: 1px solid var(--border);
        }

        .user-name {
            font-weight: 600;
            font-size: clamp(0.9rem, 3vw, 1rem);
        }

        .logout-link {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--error);
            text-decoration: none;
            margin-top: 0.5rem;
            font-size: clamp(0.85rem, 3vw, 0.9rem);
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .logout-link:hover {
            color: #ff2277;
        }

        .logout-link svg {
            width: clamp(1rem, 3vw, 1.25rem);
            height: clamp(1rem, 3vw, 1.25rem);
        }

        /* Main Content */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            min-height: 100vh;
            transition: margin-left 0.3s ease;
        }

        .main-content.expanded {
            margin-left: 0;
        }

        /* Header */
        .header {
            border-bottom: 1px solid var(--border);
            background: var(--card);
            padding: clamp(1rem, 4vw, 1.5rem);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .menu-toggle {
            display: none;
            background: none;
            border: none;
            color: var(--foreground);
            cursor: pointer;
            padding: 0.5rem;
        }

        .menu-toggle svg {
            width: clamp(1.5rem, 5vw, 2rem);
            height: clamp(1.5rem, 5vw, 2rem);
        }

        .page-title {
            font-family: 'Unbounded', sans-serif;
            font-size: clamp(1.25rem, 5vw, 1.75rem);
            font-weight: 700;
        }

        .header-subtitle {
            color: var(--muted-foreground);
            font-size: clamp(0.85rem, 3vw, 1rem);
        }

        /* Content Area */
        .content {
            padding: clamp(1.5rem, 5vw, 2rem);
        }

        /* Action Buttons */
        .actions {
            display: flex;
            gap: 1rem;
            margin-bottom: clamp(1.5rem, 5vw, 2rem);
            flex-wrap: wrap;
        }

        .button {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: clamp(0.625rem, 2.5vw, 0.875rem) clamp(1.25rem, 5vw, 1.75rem);
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 9999px;
            font-family: 'Outfit', sans-serif;
            font-weight: bold;
            font-size: clamp(0.85rem, 3vw, 0.95rem);
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            box-shadow: 0 0 15px rgba(255, 0, 153, 0.4);
            transition: box-shadow 0.3s ease, transform 0.3s ease;
            white-space: nowrap;
        }

        .button:hover {
            box-shadow: 0 0 25px rgba(255, 0, 153, 0.6);
            transform: scale(1.02);
        }

        .button.secondary {
            background: var(--secondary);
            color: var(--secondary-foreground);
            box-shadow: 0 0 15px rgba(0, 240, 255, 0.4);
        }

        .button.secondary:hover {
            box-shadow: 0 0 25px rgba(0, 240, 255, 0.6);
        }

        .button svg {
            width: clamp(1rem, 3vw, 1.25rem);
            height: clamp(1rem, 3vw, 1.25rem);
        }

        /* Forms */
        .form-card {
            background: rgba(10, 10, 15, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: clamp(0.75rem, 3vw, 1rem);
            padding: clamp(1.25rem, 5vw, 1.75rem);
            margin-bottom: clamp(1.5rem, 5vw, 2rem);
            display: none;
        }

        .form-card.active {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-card h3 {
            font-family: 'Unbounded', sans-serif;
            font-size: clamp(1.25rem, 5vw, 1.5rem);
            margin-bottom: clamp(1rem, 4vw, 1.5rem);
            font-weight: 700;
        }

        .form-group {
            margin-bottom: clamp(1rem, 4vw, 1.25rem);
        }

        .form-label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            font-size: clamp(0.85rem, 3vw, 1rem);
        }

        .form-input,
        .form-textarea {
            width: 100%;
            padding: clamp(0.625rem, 2.5vw, 0.875rem);
            background: var(--input);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: white;
            font-family: 'Outfit', sans-serif;
            font-size: clamp(0.9rem, 3vw, 1rem);
            transition: border-color 0.3s ease, box-shadow 0.3s ease;
        }

        .form-input:focus,
        .form-textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 2px rgba(255, 0, 153, 0.2);
        }

        .form-textarea {
            resize: vertical;
            min-height: clamp(4rem, 15vw, 6rem);
        }

        /* Tables */
        .table-card {
            background: rgba(10, 10, 15, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: clamp(0.75rem, 3vw, 1rem);
            overflow: hidden;
        }

        .table-header {
            padding: clamp(1rem, 4vw, 1.5rem);
            border-bottom: 1px solid var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .table-header h3 {
            font-family: 'Unbounded', sans-serif;
            font-size: clamp(1.25rem, 5vw, 1.5rem);
            font-weight: 700;
        }

        .search-box {
            display: flex;
            gap: 0.5rem;
            flex: 1;
            max-width: 400px;
        }

        .search-input {
            flex: 1;
            padding: clamp(0.5rem, 2vw, 0.75rem) clamp(0.75rem, 3vw, 1rem);
            background: var(--input);
            border: 1px solid var(--border);
            border-radius: 9999px;
            color: white;
            font-family: 'Outfit', sans-serif;
            font-size: clamp(0.85rem, 3vw, 0.95rem);
        }

        .search-input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .table-container {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th {
            text-align: left;
            padding: clamp(0.75rem, 3vw, 1rem);
            font-size: clamp(0.75rem, 2.5vw, 0.85rem);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted-foreground);
            border-bottom: 1px solid var(--border);
            white-space: nowrap;
        }

        td {
            padding: clamp(0.75rem, 3vw, 1rem);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            font-size: clamp(0.85rem, 3vw, 1rem);
        }

        tr:last-child td {
            border-bottom: none;
        }

        tr:hover td {
            background: rgba(255, 255, 255, 0.05);
        }

        .song-info {
            display: flex;
            align-items: center;
            gap: clamp(0.5rem, 2vw, 0.75rem);
        }

        .thumbnail {
            width: clamp(2.5rem, 8vw, 3rem);
            height: clamp(2.5rem, 8vw, 3rem);
            object-fit: cover;
            border-radius: 0.5rem;
            flex-shrink: 0;
        }

        .thumbnail-placeholder {
            width: clamp(2.5rem, 8vw, 3rem);
            height: clamp(2.5rem, 8vw, 3rem);
            background: var(--muted);
            border-radius: 0.5rem;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .thumbnail-placeholder svg {
            width: clamp(1rem, 3vw, 1.25rem);
            height: clamp(1rem, 3vw, 1.25rem);
            color: var(--muted-foreground);
        }

        .song-title {
            font-weight: 500;
        }

        .song-artist {
            color: var(--muted-foreground);
            font-size: clamp(0.75rem, 2.5vw, 0.85rem);
        }

        .badge {
            display: inline-block;
            padding: clamp(0.25rem, 1vw, 0.375rem) clamp(0.5rem, 2vw, 0.75rem);
            background: rgba(255, 0, 153, 0.1);
            border: 1px solid rgba(255, 0, 153, 0.3);
            border-radius: 9999px;
            font-size: clamp(0.7rem, 2vw, 0.8rem);
            font-family: 'Space Mono', monospace;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--primary);
        }

        .action-btn {
            padding: 0.5rem;
            background: none;
            border: none;
            color: var(--error);
            cursor: pointer;
            border-radius: 0.5rem;
            transition: background 0.3s ease;
        }

        .action-btn:hover {
            background: rgba(255, 0, 85, 0.1);
        }

        .action-btn svg {
            width: clamp(1rem, 3vw, 1.25rem);
            height: clamp(1rem, 3vw, 1.25rem);
        }

        /* Empty State */
        .empty-state {
            padding: clamp(3rem, 10vw, 4rem);
            text-align: center;
            color: var(--muted-foreground);
        }

        .empty-state p {
            font-size: clamp(1rem, 4vw, 1.1rem);
        }

        /* YouTube Results */
        .youtube-result {
            display: flex;
            align-items: center;
            gap: clamp(0.75rem, 3vw, 1rem);
            padding: clamp(0.75rem, 3vw, 1rem);
            background: var(--muted);
            border-radius: 0.5rem;
            margin-bottom: 0.75rem;
        }

        .youtube-thumb {
            width: clamp(5rem, 15vw, 6rem);
            height: clamp(3.5rem, 10vw, 4rem);
            object-fit: cover;
            border-radius: 0.5rem;
            flex-shrink: 0;
        }

        .youtube-info {
            flex: 1;
            min-width: 0;
        }

        .youtube-title {
            font-weight: 500;
            font-size: clamp(0.9rem, 3vw, 1rem);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .youtube-channel {
            color: var(--muted-foreground);
            font-size: clamp(0.8rem, 2.5vw, 0.9rem);
        }

        .youtube-add {
            padding: clamp(0.5rem, 2vw, 0.625rem) clamp(0.75rem, 3vw, 1rem);
            background: var(--primary);
            color: white;
            border: none;
            border-radius: 9999px;
            font-size: clamp(0.85rem, 3vw, 0.95rem);
            font-weight: bold;
            cursor: pointer;
            white-space: nowrap;
            flex-shrink: 0;
        }

        .youtube-add:hover {
            box-shadow: 0 0 15px rgba(255, 0, 153, 0.4);
        }

        /* Toast */
        .toast {
            position: fixed;
            bottom: clamp(1rem, 4vw, 1.5rem);
            right: clamp(1rem, 4vw, 1.5rem);
            padding: clamp(0.75rem, 3vw, 1rem) clamp(1.25rem, 5vw, 1.75rem);
            background: var(--success);
            color: var(--background);
            border-radius: 0.5rem;
            font-weight: 600;
            z-index: 1000;
            transform: translateY(100px);
            opacity: 0;
            transition: all 0.3s ease;
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast.error {
            background: var(--error);
            color: white;
        }

        /* Loading */
        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.7);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 2000;
            display: none;
        }

        .loading-overlay.active {
            display: flex;
        }

        .loading-spinner {
            width: clamp(3rem, 10vw, 4rem);
            height: clamp(3rem, 10vw, 4rem);
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        /* Responsive Breakpoints */
        @media (max-width: 1024px) {
            .sidebar {
                transform: translateX(-100%);
            }

            .sidebar.open {
                transform: translateX(0);
            }

            .main-content {
                margin-left: 0;
            }

            .menu-toggle {
                display: block;
            }

            .search-box {
                max-width: 100%;
            }
        }

        @media (max-width: 640px) {
            .header {
                flex-direction: column;
                align-items: flex-start;
                gap: 0.75rem;
            }

            .header-left {
                width: 100%;
            }

            .actions {
                flex-direction: column;
            }

            .button {
                width: 100%;
                justify-content: center;
            }

            .song-info {
                flex-direction: column;
                align-items: flex-start;
            }

            .youtube-result {
                flex-direction: column;
            }

            .youtube-thumb {
                width: 100%;
                height: auto;
                aspect-ratio: 16/9;
            }
        }

        @media (min-width: 1920px) {
            .content {
                max-width: 1600px;
                margin: 0 auto;
            }
        }

        /* TV Optimizations */
        @media (min-width: 2560px) {
            :root {
                --sidebar-width: 320px;
            }

            .content {
                max-width: 2000px;
                margin: 0 auto;
            }

            .button {
                font-size: 1rem;
                padding: 0.875rem 2rem;
            }

            .form-input,
            .form-textarea {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <aside class="sidebar" id="sidebar">
        <div class="logo">NeonVox</div>
        <ul class="nav-menu">
            <li>
                <a href="#" class="nav-link active">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="7" height="9" x="3" y="3" rx="1"/><rect width="7" height="5" x="14" y="3" rx="1"/><rect width="7" height="9" x="14" y="12" rx="1"/><rect width="7" height="5" x="3" y="16" rx="1"/></svg>
                    Dashboard
                </a>
            </li>
            <li>
                <a href="../index.php" class="nav-link" target="_blank">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polygon points="10 8 16 12 10 16 10 8"/></svg>
                    View Site
                </a>
            </li>
        </ul>
        <div class="user-info">
            <div class="user-name" id="username">Admin</div>
            <a href="#" class="logout-link" id="logout">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" x2="9" y1="12" y2="12"/></svg>
                Logout
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="main-content">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="3" x2="21" y1="6" y2="6"/><line x1="3" x2="21" y1="12" y2="12"/><line x1="3" x2="21" y1="18" y2="18"/></svg>
                </button>
                <div>
                    <h1 class="page-title">Admin Dashboard</h1>
                    <p class="header-subtitle">Manage your karaoke library</p>
                </div>
            </div>
        </header>

        <!-- Content -->
        <div class="content">
            <!-- Action Buttons -->
            <div class="actions">
                <button class="button" id="uploadBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" x2="12" y1="3" y2="15"/></svg>
                    Upload File
                </button>
                <button class="button secondary" id="youtubeBtn">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/></svg>
                    YouTube Search
                </button>
            </div>

            <!-- Upload Form -->
            <div class="form-card" id="uploadForm">
                <h3>Upload Song File</h3>
                <form id="uploadFormEl">
                    <div class="form-group">
                        <label class="form-label">Title *</label>
                        <input type="text" class="form-input" id="uploadTitle" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Artist *</label>
                        <input type="text" class="form-input" id="uploadArtist" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">File (.mp4, .mid, .kar, .mp3, .webm) *</label>
                        <input type="file" class="form-input" id="uploadFile" accept=".mp4,.mid,.midi,.kar,.mp3,.webm,.wav" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Lyrics (optional)</label>
                        <textarea class="form-textarea" id="uploadLyrics" placeholder="Add lyrics..."></textarea>
                    </div>
                    <button type="submit" class="button">Upload Song</button>
                    <button type="button" class="button" style="background: var(--muted);" id="cancelUpload">Cancel</button>
                </form>
            </div>

            <!-- YouTube Search Form -->
            <div class="form-card" id="youtubeForm">
                <h3>Search YouTube</h3>
                <div class="form-group">
                    <label class="form-label">YouTube API Key</label>
                    <input type="password" class="form-input" id="youtubeApiKey" placeholder="Enter your YouTube Data API v3 key">
                </div>
                <div class="form-group">
                    <label class="form-label">Search</label>
                    <div style="display: flex; gap: 0.5rem;">
                        <input type="text" class="form-input" id="youtubeQuery" placeholder="Search for karaoke songs...">
                        <button type="button" class="button" id="youtubeSearchBtn">Search</button>
                    </div>
                </div>
                <div id="youtubeResults"></div>
                <button type="button" class="button" style="background: var(--muted); margin-top: 1rem;" id="cancelYoutube">Cancel</button>
            </div>

            <!-- Songs Table -->
            <div class="table-card">
                <div class="table-header">
                    <h3>Songs Library (<span id="songCount">0</span>)</h3>
                    <div class="search-box">
                        <input type="text" class="search-input" id="searchInput" placeholder="Search songs or artists...">
                    </div>
                </div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>Song</th>
                                <th>Artist</th>
                                <th>Type</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="songsTableBody"></tbody>
                    </table>
                    <div class="empty-state" id="emptyState" style="display: none;">
                        <p>No songs yet. Add your first song!</p>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="loading-spinner"></div>
    </div>

    <script>
        // Check auth
        const token = localStorage.getItem('admin_token');
        if (!token) {
            window.location.href = 'login.php';
        }

        // API base URL
        const API_BASE = '../api/index.php';

        // Elements
        const sidebar = document.getElementById('sidebar');
        const menuToggle = document.getElementById('menuToggle');
        const uploadBtn = document.getElementById('uploadBtn');
        const uploadForm = document.getElementById('uploadForm');
        const cancelUpload = document.getElementById('cancelUpload');
        const youtubeBtn = document.getElementById('youtubeBtn');
        const youtubeForm = document.getElementById('youtubeForm');
        const cancelYoutube = document.getElementById('cancelYoutube');
        const uploadFormEl = document.getElementById('uploadFormEl');
        const songsTableBody = document.getElementById('songsTableBody');
        const emptyState = document.getElementById('emptyState');
        const songCount = document.getElementById('songCount');
        const searchInput = document.getElementById('searchInput');
        const toast = document.getElementById('toast');
        const loadingOverlay = document.getElementById('loadingOverlay');
        const usernameEl = document.getElementById('username');
        const logoutBtn = document.getElementById('logout');

        // Set username
        usernameEl.textContent = localStorage.getItem('admin_username') || 'Admin';

        // Show/hide loading
        function showLoading() {
            loadingOverlay.classList.add('active');
        }

        function hideLoading() {
            loadingOverlay.classList.remove('active');
        }

        // Show toast
        function showToast(message, isError = false) {
            toast.textContent = message;
            toast.classList.toggle('error', isError);
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // Toggle sidebar
        menuToggle.addEventListener('click', () => {
            sidebar.classList.toggle('open');
        });

        // Show upload form
        uploadBtn.addEventListener('click', () => {
            uploadForm.classList.add('active');
            youtubeForm.classList.remove('active');
        });

        // Cancel upload
        cancelUpload.addEventListener('click', () => {
            uploadForm.classList.remove('active');
            uploadFormEl.reset();
        });

        // Show YouTube form
        youtubeBtn.addEventListener('click', () => {
            youtubeForm.classList.add('active');
            uploadForm.classList.remove('active');
        });

        // Cancel YouTube
        cancelYoutube.addEventListener('click', () => {
            youtubeForm.classList.remove('active');
        });

        // Handle file upload
        uploadFormEl.addEventListener('submit', async (e) => {
            e.preventDefault();

            const formData = new FormData();
            formData.append('title', document.getElementById('uploadTitle').value);
            formData.append('artist', document.getElementById('uploadArtist').value);
            formData.append('file', document.getElementById('uploadFile').files[0]);
            formData.append('lyrics', document.getElementById('uploadLyrics').value);

            showLoading();
            try {
                const response = await fetch(`${API_BASE}/upload`, {
                    method: 'POST',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    },
                    body: formData
                });

                const result = await response.json();

                if (response.ok) {
                    showToast('Song uploaded successfully!');
                    uploadForm.classList.remove('active');
                    uploadFormEl.reset();
                    loadSongs();
                } else {
                    throw new Error(result.error || 'Upload failed');
                }
            } catch (error) {
                showToast(error.message, true);
            } finally {
                hideLoading();
            }
        });

        // YouTube search
        document.getElementById('youtubeSearchBtn').addEventListener('click', async () => {
            const apiKey = document.getElementById('youtubeApiKey').value;
            const query = document.getElementById('youtubeQuery').value;

            if (!apiKey || !query) {
                showToast('Please enter API key and search query', true);
                return;
            }

            showLoading();
            try {
                const response = await fetch(`${API_BASE}/youtube/search?q=${encodeURIComponent(query)}&api_key=${apiKey}`);
                const result = await response.json();

                if (response.ok && result.results) {
                    displayYoutubeResults(result.results);
                } else {
                    throw new Error(result.error || 'Search failed');
                }
            } catch (error) {
                showToast(error.message, true);
            } finally {
                hideLoading();
            }
        });

        // Display YouTube results
        function displayYoutubeResults(results) {
            const container = document.getElementById('youtubeResults');

            if (results.length === 0) {
                container.innerHTML = '<p style="color: var(--muted-foreground);">No results found</p>';
                return;
            }

            container.innerHTML = results.map(video => `
                <div class="youtube-result">
                    <img src="${video.thumbnail}" alt="${video.title}" class="youtube-thumb">
                    <div class="youtube-info">
                        <div class="youtube-title">${video.title}</div>
                        <div class="youtube-channel">${video.channel}</div>
                    </div>
                    <button class="youtube-add" onclick="addYoutubeSong('${video.video_id}')">Add</button>
                </div>
            `).join('');
        }

        // Add YouTube song
        window.addYoutubeSong = async (videoId) => {
            const apiKey = document.getElementById('youtubeApiKey').value;
            const query = document.getElementById('youtubeQuery').value;

            // Get video details from results
            const response = await fetch(`${API_BASE}/youtube/search?q=${encodeURIComponent(query)}&api_key=${apiKey}`);
            const result = await response.json();
            const video = result.results.find(v => v.video_id === videoId);

            if (!video) {
                showToast('Video not found', true);
                return;
            }

            showLoading();
            try {
                const songResponse = await fetch(`${API_BASE}/songs`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Authorization': `Bearer ${token}`
                    },
                    body: JSON.stringify({
                        title: video.title,
                        artist: video.channel,
                        source_type: 'youtube',
                        source_url: `https://www.youtube.com/watch?v=${videoId}`,
                        thumbnail: video.thumbnail
                    })
                });

                const songResult = await songResponse.json();

                if (songResponse.ok) {
                    showToast('Song added successfully!');
                    youtubeForm.classList.remove('active');
                    document.getElementById('youtubeResults').innerHTML = '';
                    loadSongs();
                } else {
                    throw new Error(songResult.error || 'Failed to add song');
                }
            } catch (error) {
                showToast(error.message, true);
            } finally {
                hideLoading();
            }
        };

        // Load songs
        async function loadSongs(search = '') {
            showLoading();
            try {
                const url = search ? `${API_BASE}/songs?search=${encodeURIComponent(search)}` : `${API_BASE}/songs`;
                const response = await fetch(url);
                const songs = await response.json();

                displaySongs(songs);
            } catch (error) {
                showToast('Failed to load songs', true);
            } finally {
                hideLoading();
            }
        }

        // Display songs
        function displaySongs(songs) {
            songCount.textContent = songs.length;

            if (songs.length === 0) {
                songsTableBody.innerHTML = '';
                emptyState.style.display = 'block';
                return;
            }

            emptyState.style.display = 'none';
            songsTableBody.innerHTML = songs.map(song => `
                <tr>
                    <td>
                        <div class="song-info">
                            ${song.thumbnail
                                ? `<img src="${song.thumbnail}" alt="${song.title}" class="thumbnail">`
                                : `<div class="thumbnail-placeholder">
                                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 18V5l12-2v13"/><circle cx="6" cy="18" r="3"/><circle cx="18" cy="16" r="3"/></svg>
                                   </div>`
                            }
                            <div>
                                <div class="song-title">${song.title}</div>
                            </div>
                        </div>
                    </td>
                    <td>${song.artist}</td>
                    <td><span class="badge">${song.source_type}</span></td>
                    <td>
                        <button class="action-btn" onclick="deleteSong('${song.id}')">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/></svg>
                        </button>
                    </td>
                </tr>
            `).join('');
        }

        // Delete song
        window.deleteSong = async (songId) => {
            if (!confirm('Are you sure you want to delete this song?')) return;

            showLoading();
            try {
                const response = await fetch(`${API_BASE}/songs/${songId}`, {
                    method: 'DELETE',
                    headers: {
                        'Authorization': `Bearer ${token}`
                    }
                });

                if (response.ok) {
                    showToast('Song deleted');
                    loadSongs(searchInput.value);
                } else {
                    const result = await response.json();
                    throw new Error(result.error || 'Delete failed');
                }
            } catch (error) {
                showToast(error.message, true);
            } finally {
                hideLoading();
            }
        };

        // Search
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadSongs(e.target.value);
            }, 300);
        });

        // Logout
        logoutBtn.addEventListener('click', () => {
            localStorage.removeItem('admin_token');
            localStorage.removeItem('admin_username');
            localStorage.removeItem('admin_user_id');
            window.location.href = 'login.php';
        });

        // Initial load
        loadSongs();

        // Close sidebar when clicking outside on mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 1024) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target)) {
                    sidebar.classList.remove('open');
                }
            }
        });
    </script>
</body>
</html>
