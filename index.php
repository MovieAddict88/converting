<?php
require_once __DIR__ . '/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>NeonVox - The Ultimate Karaoke Experience</title>
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
            --accent: #7000FF;
            --muted: #1A1A24;
            --muted-foreground: #A1A1AA;
            --border: #27272A;
            --input: #18181B;
            --ring: #FF0099;
            --success: #00FF94;
            --warning: #FFD600;
            --error: #FF0055;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--background);
            color: var(--foreground);
            min-height: 100vh;
            line-height: 1.6;
            overflow-x: hidden;
        }

        /* Typography */
        .font-unbounded {
            font-family: 'Unbounded', sans-serif;
        }

        .font-outfit {
            font-family: 'Outfit', sans-serif;
        }

        .font-mono {
            font-family: 'Space Mono', monospace;
        }

        .font-syne {
            font-family: 'Syne', sans-serif;
        }

        /* Header */
        .header {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 50;
            background: rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(20px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.05);
            height: clamp(4rem, 15vw, 5rem);
            display: flex;
            align-items: center;
            padding: 0 clamp(1.5rem, 5vw, 3rem);
            justify-content: space-between;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: clamp(0.75rem, 3vw, 1rem);
        }

        .logo {
            font-size: clamp(1.5rem, 8vw, 2rem);
            font-weight: 900;
            background: linear-gradient(135deg, #FF0099 0%, #7000FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.05em;
        }

        .admin-link {
            color: var(--muted-foreground);
            text-decoration: none;
            font-size: clamp(0.85rem, 3vw, 1rem);
            padding: clamp(0.5rem, 2vw, 0.75rem) clamp(0.75rem, 3vw, 1rem);
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .admin-link:hover {
            color: var(--foreground);
            background: rgba(255, 255, 255, 0.05);
        }

        /* Hero Section */
        .hero {
            position: relative;
            padding: clamp(8rem, 25vw, 12rem) clamp(1.5rem, 5vw, 3rem) clamp(5rem, 15vw, 8rem);
            overflow: hidden;
        }

        .hero-bg {
            position: absolute;
            inset: 0;
            z-index: 0;
            background-image: url('https://images.unsplash.com/photo-1683582544815-66d1ea55d3c2?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NTY2NzZ8MHwxfHNlYXJjaHwxfHxwYXJ0eSUyMGNyb3dzJTIwc2lsaG91ZXR0ZSUyMGNvbmNlcnR8ZW58MHx8fHwxNzY3MDM2NzUwfDA&ixlib=rb-4.1.0&q=85');
            background-size: cover;
            background-position: center;
        }

        .hero-overlay {
            position: absolute;
            inset: 0;
            background: linear-gradient(to bottom, var(--background) 0%, var(--background) 60%, rgba(3, 3, 5, 0.8) 100%);
        }

        .hero-content {
            position: relative;
            z-index: 10;
            max-width: clamp(24rem, 60vw, 60rem);
            margin: 0 auto;
            text-align: center;
        }

        .hero-title {
            font-size: clamp(2.5rem, 12vw, 6rem);
            font-weight: 900;
            background: linear-gradient(135deg, #FF0099 0%, #7000FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.05em;
            margin-bottom: clamp(1rem, 4vw, 1.5rem);
            line-height: 1.1;
        }

        .hero-subtitle {
            font-size: clamp(1rem, 4vw, 1.25rem);
            color: var(--muted-foreground);
            margin-bottom: clamp(2rem, 8vw, 3rem);
        }

        /* Search Bar */
        .search-form {
            max-width: clamp(18rem, 50vw, 38rem);
            margin: 0 auto;
        }

        .search-box {
            position: relative;
            background: rgba(0, 0, 0, 0.4);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 9999px;
            overflow: hidden;
        }

        .search-icon {
            position: absolute;
            left: clamp(1rem, 3vw, 1.5rem);
            top: 50%;
            transform: translateY(-50%);
            width: clamp(1rem, 3vw, 1.25rem);
            height: clamp(1rem, 3vw, 1.25rem);
            color: var(--muted-foreground);
        }

        .search-input {
            width: 100%;
            padding: clamp(0.875rem, 3vw, 1.25rem) clamp(2.5rem, 7vw, 4rem);
            background: transparent;
            border: none;
            color: white;
            font-family: 'Outfit', sans-serif;
            font-size: clamp(0.9rem, 3vw, 1rem);
        }

        .search-input::placeholder {
            color: rgba(161, 161, 170, 0.5);
        }

        .search-input:focus {
            outline: none;
        }

        .search-input:focus + .search-icon,
        .search-input:focus ~ .search-box {
            box-shadow: 0 0 0 2px rgba(255, 0, 153, 0.2);
            border-color: var(--primary);
        }

        /* Songs Section */
        .songs-section {
            padding: clamp(2rem, 8vw, 4rem) clamp(1.5rem, 5vw, 3rem);
        }

        .songs-section-header {
            display: flex;
            align-items: center;
            gap: clamp(0.5rem, 2vw, 0.75rem);
            margin-bottom: clamp(1.5rem, 5vw, 2rem);
        }

        .songs-section-icon {
            width: clamp(1.5rem, 4vw, 1.5rem);
            height: clamp(1.5rem, 4vw, 1.5rem);
            color: var(--primary);
        }

        .songs-section-title {
            font-size: clamp(1.5rem, 6vw, 2rem);
            font-weight: 700;
        }

        .songs-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(min(100%, clamp(18rem, 50vw, 22rem)), 1fr));
            gap: clamp(1rem, 3vw, 1.5rem);
        }

        /* Song Card */
        .song-card {
            position: relative;
            background: rgba(10, 10, 15, 0.8);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: clamp(0.75rem, 3vw, 1rem);
            overflow: hidden;
            cursor: pointer;
            transition: border-color 0.3s ease;
        }

        .song-card:hover {
            border-color: rgba(255, 255, 255, 0.2);
        }

        .song-thumbnail {
            position: relative;
            height: clamp(10rem, 30vw, 12rem);
            background: var(--muted);
            overflow: hidden;
        }

        .song-thumbnail img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.5s ease;
        }

        .song-card:hover .song-thumbnail img {
            transform: scale(1.1);
        }

        .song-thumbnail-placeholder {
            width: 100%;
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, rgba(255, 0, 153, 0.2) 0%, rgba(112, 0, 255, 0.2) 100%);
        }

        .song-thumbnail-placeholder svg {
            width: clamp(2.5rem, 8vw, 4rem);
            height: clamp(2.5rem, 8vw, 4rem);
            color: rgba(255, 255, 255, 0.3);
        }

        .play-overlay {
            position: absolute;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .song-card:hover .play-overlay {
            opacity: 1;
        }

        .play-button {
            width: clamp(3rem, 10vw, 4rem);
            height: clamp(3rem, 10vw, 4rem);
            border-radius: 50%;
            background: var(--primary);
            box-shadow: 0 0 20px rgba(255, 0, 153, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            transform: scale(0);
            transition: transform 0.3s ease;
        }

        .song-card:hover .play-button {
            transform: scale(1);
        }

        .play-button svg {
            width: clamp(1.5rem, 5vw, 2rem);
            height: clamp(1.5rem, 5vw, 2rem);
            color: white;
            margin-left: 2px;
        }

        .song-info {
            padding: clamp(0.875rem, 3vw, 1.25rem);
        }

        .song-title {
            font-size: clamp(1rem, 4vw, 1.1rem);
            font-weight: 500;
            margin-bottom: 0.25rem;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .song-artist {
            color: var(--muted-foreground);
            font-size: clamp(0.85rem, 3vw, 0.95rem);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .song-badge {
            display: inline-block;
            margin-top: clamp(0.5rem, 2vw, 0.625rem);
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

        /* Loading and Empty States */
        .state-container {
            padding: clamp(3rem, 10vw, 5rem);
            text-align: center;
        }

        .state-icon {
            width: clamp(10rem, 30vw, 14rem);
            height: clamp(10rem, 30vw, 14rem);
            margin: 0 auto clamp(1.5rem, 4vw, 1.5rem);
            border-radius: clamp(0.75rem, 3vw, 1rem);
            object-fit: cover;
            opacity: 0.5;
        }

        .state-text {
            color: var(--muted-foreground);
            font-size: clamp(1rem, 4vw, 1.1rem);
        }

        .loading-spinner {
            width: clamp(2.5rem, 8vw, 3rem);
            height: clamp(2.5rem, 8vw, 3rem);
            border: 3px solid rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
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
            max-width: calc(100% - 2rem);
        }

        .toast.show {
            transform: translateY(0);
            opacity: 1;
        }

        .toast.error {
            background: var(--error);
            color: white;
        }

        /* Responsive Breakpoints */
        @media (max-width: 640px) {
            .header {
                padding: 0 1rem;
            }

            .hero {
                padding: 6rem 1rem 4rem;
            }

            .songs-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (min-width: 768px) {
            .songs-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .songs-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }

        @media (min-width: 1920px) {
            .hero-content {
                max-width: 80rem;
            }

            .songs-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        /* TV Optimizations */
        @media (min-width: 2560px) {
            :root {
                --header-height: 6rem;
            }

            .header {
                height: var(--header-height);
            }

            .hero-title {
                font-size: 7rem;
            }

            .hero-subtitle {
                font-size: 1.5rem;
            }

            .songs-grid {
                grid-template-columns: repeat(5, 1fr);
                gap: 2rem;
            }

            .song-card {
                border-radius: 1.25rem;
            }

            .button {
                font-size: 1.1rem;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-left">
            <svg xmlns="http://www.w3.org/2000/svg" class="logo-icon" viewBox="0 0 24 24" fill="none" stroke="#FF0099" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="width: clamp(1.5rem, 5vw, 2rem); height: clamp(1.5rem, 5vw, 2rem);">
                <path d="M12 2c-1.7 0-3 1.2-3 2.6v6.8c0 1.4 1.3 2.6 3 2.6s3-1.2 3-2.6V4.6C15 3.2 13.7 2 12 2z"/>
                <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                <line x1="12" x2="12" y1="19" y2="22"/>
                <line x1="8" x2="16" y1="22" y2="22"/>
            </svg>
            <h1 class="logo font-unbounded">NeonVox</h1>
        </div>
        <a href="admin/login.php" class="admin-link">Admin</a>
    </header>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-bg"></div>
        <div class="hero-overlay"></div>
        <div class="hero-content">
            <h2 class="hero-title font-unbounded">Enter the Stage</h2>
            <p class="hero-subtitle">Sing your heart out with real-time scoring</p>

            <form class="search-form" id="searchForm">
                <div class="search-box">
                    <input
                        type="text"
                        class="search-input"
                        id="searchInput"
                        placeholder="Search songs or artists..."
                        autocomplete="off"
                    >
                    <svg xmlns="http://www.w3.org/2000/svg" class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/>
                        <path d="m21 21-4.3-4.3"/>
                    </svg>
                </div>
            </form>
        </div>
    </section>

    <!-- Songs Section -->
    <section class="songs-section">
        <div class="songs-section-header">
            <svg xmlns="http://www.w3.org/2000/svg" class="songs-section-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/>
                <polyline points="16 7 22 7 22 13"/>
            </svg>
            <h3 class="songs-section-title font-unbounded">Trending Now</h3>
        </div>

        <div id="songsContainer">
            <!-- Songs will be loaded here -->
        </div>
    </section>

    <!-- Toast -->
    <div class="toast" id="toast"></div>

    <script>
        // API base URL
        const API_BASE = 'api/index.php';

        // Elements
        const songsContainer = document.getElementById('songsContainer');
        const searchForm = document.getElementById('searchForm');
        const searchInput = document.getElementById('searchInput');
        const toast = document.getElementById('toast');

        // Show toast
        function showToast(message, isError = false) {
            toast.textContent = message;
            toast.classList.toggle('error', isError);
            toast.classList.add('show');
            setTimeout(() => toast.classList.remove('show'), 3000);
        }

        // Create song card HTML
        function createSongCard(song) {
            return `
                <div class="song-card" data-song-id="${song.id}">
                    <div class="song-thumbnail">
                        ${song.thumbnail
                            ? `<img src="${song.thumbnail}" alt="${song.title}" loading="lazy">`
                            : `<div class="song-thumbnail-placeholder">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 2c-1.7 0-3 1.2-3 2.6v6.8c0 1.4 1.3 2.6 3 2.6s3-1.2 3-2.6V4.6C15 3.2 13.7 2 12 2z"/>
                                    <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                    <line x1="12" x2="12" y1="19" y2="22"/>
                                    <line x1="8" x2="16" y1="22" y2="22"/>
                                </svg>
                               </div>`
                        }
                        <div class="play-overlay">
                            <div class="play-button">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M8 5v14l11-7z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    <div class="song-info">
                        <div class="song-title">${song.title}</div>
                        <div class="song-artist">${song.artist}</div>
                        <span class="song-badge">${song.source_type}</span>
                    </div>
                </div>
            `;
        }

        // Load songs
        async function loadSongs(search = '') {
            songsContainer.innerHTML = `
                <div class="state-container">
                    <div class="loading-spinner"></div>
                </div>
            `;

            try {
                const url = search ? `${API_BASE}/songs?search=${encodeURIComponent(search)}` : `${API_BASE}/songs`;
                const response = await fetch(url);
                const songs = await response.json();

                if (songs.length === 0) {
                    songsContainer.innerHTML = `
                        <div class="state-container">
                            <img src="https://images.unsplash.com/photo-1719437364093-82c24d719303?crop=entropy&cs=srgb&fm=jpg&ixid=M3w3NDk1Nzd8MHwxfHNlYXJjaHwxfHxuZW9uJTIwa2FyYW9rZSUyMG1pY3JvcGhvbmUlMjBzdGFnZXxlbnwwfHx8fDE3NjcwMzY3NDh8MA&ixlib=rb-4.1.0&q=85" alt="No songs" class="state-icon">
                            <p class="state-text">${search ? 'No songs found. Try a different search.' : 'No songs found. Ask admin to add some!'}</p>
                        </div>
                    `;
                    return;
                }

                const grid = document.createElement('div');
                grid.className = 'songs-grid';
                grid.innerHTML = songs.map(song => createSongCard(song)).join('');
                songsContainer.innerHTML = '';
                songsContainer.appendChild(grid);

                // Add click handlers
                grid.querySelectorAll('.song-card').forEach(card => {
                    card.addEventListener('click', () => {
                        const songId = card.dataset.songId;
                        window.location.href = `player.php?id=${songId}`;
                    });
                });

            } catch (error) {
                console.error('Error loading songs:', error);
                songsContainer.innerHTML = `
                    <div class="state-container">
                        <p class="state-text">Failed to load songs. Please try again.</p>
                    </div>
                `;
            }
        }

        // Search handler
        let searchTimeout;
        searchInput.addEventListener('input', (e) => {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadSongs(e.target.value);
            }, 300);
        });

        // Form submit
        searchForm.addEventListener('submit', (e) => {
            e.preventDefault();
            loadSongs(searchInput.value);
        });

        // Initial load
        loadSongs();
    </script>
</body>
</html>
