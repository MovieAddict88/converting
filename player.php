<?php
require_once __DIR__ . '/config.php';

$songId = $_GET['id'] ?? '';

// Get song data from database
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    die('Database connection failed');
}

$stmt = $conn->prepare("SELECT * FROM songs WHERE id = ?");
$stmt->bind_param('s', $songId);
$stmt->execute();
$result = $stmt->get_result();
$song = $result->fetch_assoc();

if (!$song) {
    die('Song not found');
}

$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($song['title']); ?> - NeonVox</title>
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

        html, body {
            height: 100%;
            overflow: hidden;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background: var(--background);
            color: var(--foreground);
            line-height: 1.6;
        }

        .player-container {
            height: 100vh;
            display: flex;
            flex-direction: column;
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
            font-size: clamp(1.25rem, 6vw, 1.75rem);
            font-weight: 900;
            background: linear-gradient(135deg, #FF0099 0%, #7000FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            letter-spacing: -0.05em;
        }

        .back-button {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: var(--muted-foreground);
            text-decoration: none;
            font-size: clamp(0.85rem, 3vw, 1rem);
            padding: clamp(0.5rem, 2vw, 0.75rem);
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            color: var(--foreground);
            background: rgba(255, 255, 255, 0.05);
        }

        .back-button svg {
            width: clamp(1rem, 3vw, 1.25rem);
            height: clamp(1rem, 3vw, 1.25rem);
        }

        /* Main Content */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
        }

        /* Video/Visualizer Area */
        .video-area {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #000;
        }

        .video-container {
            width: 100%;
            height: 100%;
            position: relative;
            overflow: hidden;
        }

        .video-container iframe {
            width: 100%;
            height: 100%;
            border: none;
        }

        .audio-player {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: clamp(1.5rem, 5vw, 3rem);
            background: linear-gradient(135deg, rgba(112, 0, 255, 0.2) 0%, rgba(255, 0, 153, 0.2) 100%);
        }

        .audio-player-placeholder {
            width: clamp(12rem, 35vw, 20rem);
            height: clamp(12rem, 35vw, 20rem);
            border-radius: 50%;
            background: radial-gradient(circle, rgba(255, 0, 153, 0.3) 0%, transparent 70%);
            display: flex;
            align-items: center;
            justify-content: center;
            margin-bottom: clamp(2rem, 6vw, 3rem);
            animation: pulse 3s ease-in-out infinite;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.1); opacity: 0.8; }
        }

        .audio-player-placeholder svg {
            width: clamp(4rem, 12vw, 8rem);
            height: clamp(4rem, 12vw, 8rem);
            color: white;
        }

        .song-info-display {
            text-align: center;
        }

        .song-title-display {
            font-size: clamp(1.5rem, 6vw, 2.5rem);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .song-artist-display {
            color: var(--muted-foreground);
            font-size: clamp(1rem, 4vw, 1.25rem);
        }

        /* Controls */
        .controls {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0, 0, 0, 0.8);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(255, 255, 255, 0.1);
            padding: clamp(1rem, 4vw, 1.5rem);
            display: flex;
            align-items: center;
            gap: clamp(1rem, 3vw, 1.5rem);
            flex-wrap: wrap;
            z-index: 100;
        }

        .play-button {
            width: clamp(3rem, 10vw, 4rem);
            height: clamp(3rem, 10vw, 4rem);
            border-radius: 50%;
            background: var(--primary);
            box-shadow: 0 0 20px rgba(255, 0, 153, 0.5);
            border: none;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.3s ease;
        }

        .play-button:hover {
            box-shadow: 0 0 30px rgba(255, 0, 153, 0.7);
            transform: scale(1.1);
        }

        .play-button svg {
            width: clamp(1.25rem, 4vw, 1.5rem);
            height: clamp(1.25rem, 4vw, 1.5rem);
            color: white;
        }

        .progress-bar {
            flex: 1;
            min-width: clamp(10rem, 30vw, 20rem);
        }

        .progress-track {
            width: 100%;
            height: clamp(0.25rem, 1vw, 0.375rem);
            background: var(--muted);
            border-radius: 9999px;
            overflow: hidden;
            cursor: pointer;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
            border-radius: 9999px;
            transition: width 0.1s linear;
        }

        .time-display {
            font-family: 'Space Mono', monospace;
            font-size: clamp(0.75rem, 2.5vw, 0.875rem);
            color: var(--muted-foreground);
        }

        .volume-control {
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .volume-slider {
            width: clamp(4rem, 12vw, 8rem);
            -webkit-appearance: none;
            appearance: none;
            height: clamp(0.25rem, 1vw, 0.375rem);
            background: var(--muted);
            border-radius: 9999px;
            outline: none;
        }

        .volume-slider::-webkit-slider-thumb {
            -webkit-appearance: none;
            appearance: none;
            width: clamp(0.75rem, 2vw, 1rem);
            height: clamp(0.75rem, 2vw, 1rem);
            background: var(--primary);
            border-radius: 50%;
            cursor: pointer;
        }

        .volume-slider::-moz-range-thumb {
            width: clamp(0.75rem, 2vw, 1rem);
            height: clamp(0.75rem, 2vw, 1rem);
            background: var(--primary);
            border-radius: 50%;
            cursor: pointer;
            border: none;
        }

        .volume-icon {
            width: clamp(1rem, 3vw, 1.25rem);
            height: clamp(1rem, 3vw, 1.25rem);
            color: var(--muted-foreground);
        }

        /* Score Display */
        .score-display {
            position: fixed;
            top: clamp(5rem, 15vw, 7rem);
            right: clamp(1rem, 4vw, 1.5rem);
            background: rgba(0, 0, 0, 0.7);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: clamp(0.5rem, 2vw, 0.75rem);
            padding: clamp(0.75rem, 3vw, 1rem);
            text-align: center;
            z-index: 50;
        }

        .score-label {
            font-size: clamp(0.7rem, 2vw, 0.8rem);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--muted-foreground);
            margin-bottom: 0.25rem;
        }

        .score-value {
            font-family: 'Space Mono', monospace;
            font-size: clamp(1.5rem, 5vw, 2.5rem);
            font-weight: 700;
            color: var(--primary);
        }

        .accuracy-value {
            font-size: clamp(0.85rem, 3vw, 1rem);
            color: var(--success);
            margin-top: 0.25rem;
        }

        /* Lyrics Overlay */
        .lyrics-overlay {
            position: fixed;
            bottom: clamp(8rem, 20vw, 12rem);
            left: 0;
            right: 0;
            text-align: center;
            padding: 0 clamp(2rem, 8vw, 4rem);
            z-index: 40;
        }

        .current-lyric {
            font-family: 'Syne', sans-serif;
            font-size: clamp(1.5rem, 5vw, 3rem);
            font-weight: 800;
            color: white;
            text-shadow: 0 0 20px rgba(255, 255, 255, 0.5);
            margin-bottom: 0.5rem;
        }

        .next-lyric {
            font-size: clamp(1rem, 4vw, 1.5rem);
            color: var(--muted-foreground);
        }

        /* Submit Score Modal */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 200;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s ease;
        }

        .modal-overlay.active {
            opacity: 1;
            pointer-events: auto;
        }

        .modal {
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: clamp(0.75rem, 3vw, 1.5rem);
            padding: clamp(1.5rem, 5vw, 2.5rem);
            max-width: clamp(20rem, 50vw, 28rem);
            width: calc(100% - 2rem);
            text-align: center;
        }

        .modal-title {
            font-size: clamp(1.5rem, 5vw, 2rem);
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .modal-subtitle {
            color: var(--muted-foreground);
            margin-bottom: clamp(1.5rem, 4vw, 2rem);
        }

        .modal-score {
            font-size: clamp(2.5rem, 8vw, 4rem);
            font-weight: 700;
            background: linear-gradient(135deg, #FF0099 0%, #7000FF 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: clamp(1.5rem, 4vw, 2rem);
        }

        .modal-input {
            width: 100%;
            padding: clamp(0.75rem, 3vw, 1rem);
            background: var(--input);
            border: 1px solid var(--border);
            border-radius: 0.5rem;
            color: white;
            font-size: clamp(0.9rem, 3vw, 1rem);
            margin-bottom: clamp(1rem, 3vw, 1.5rem);
            text-align: center;
        }

        .modal-buttons {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
        }

        .modal-button {
            flex: 1;
            min-width: clamp(8rem, 25vw, 10rem);
            padding: clamp(0.75rem, 3vw, 1rem);
            border: none;
            border-radius: 9999px;
            font-weight: bold;
            font-size: clamp(0.9rem, 3vw, 1rem);
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .modal-button.primary {
            background: var(--primary);
            color: white;
            box-shadow: 0 0 15px rgba(255, 0, 153, 0.4);
        }

        .modal-button.primary:hover {
            box-shadow: 0 0 25px rgba(255, 0, 153, 0.6);
            transform: scale(1.05);
        }

        .modal-button.secondary {
            background: var(--muted);
            color: var(--foreground);
        }

        .modal-button.secondary:hover {
            background: rgba(255, 255, 255, 0.1);
        }

        /* Responsive Breakpoints */
        @media (max-width: 768px) {
            .controls {
                padding: 0.75rem;
                gap: 0.75rem;
            }

            .score-display {
                top: 4.5rem;
                right: 0.75rem;
                padding: 0.5rem 0.75rem;
            }

            .lyrics-overlay {
                bottom: 7rem;
            }
        }

        @media (min-width: 1920px) {
            .modal {
                max-width: 32rem;
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

            .play-button {
                width: 5rem;
                height: 5rem;
            }

            .play-button svg {
                width: 2rem;
                height: 2rem;
            }

            .modal-button {
                font-size: 1.1rem;
                padding: 1rem 1.5rem;
            }
        }
    </style>
</head>
<body>
    <div class="player-container">
        <!-- Header -->
        <header class="header">
            <div class="header-left">
                <a href="index.php" class="back-button">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m12 19-7-7 7-7"/>
                        <path d="M19 12H5"/>
                    </svg>
                    Back
                </a>
                <h1 class="logo font-unbounded">NeonVox</h1>
            </div>
        </header>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Video/Audio Area -->
            <div class="video-area">
                <?php if ($song['source_type'] === 'youtube' && $song['source_url']): ?>
                    <div class="video-container">
                        <iframe
                            id="youtubePlayer"
                            src="<?php echo htmlspecialchars($song['source_url']); ?>"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen>
                        </iframe>
                    </div>
                <?php elseif ($song['source_type'] === 'mp4' && $song['file_path'] && file_exists($song['file_path'])): ?>
                    <div class="video-container">
                        <video id="videoPlayer" controls style="width: 100%; height: 100%;">
                            <source src="<?php echo htmlspecialchars($song['file_path']); ?>" type="video/mp4">
                            Your browser does not support the video tag.
                        </video>
                    </div>
                <?php elseif (in_array($song['source_type'], ['mp3', 'midi', 'kar', 'upload']) && $song['file_path'] && file_exists($song['file_path'])): ?>
                    <div class="audio-player">
                        <audio id="audioPlayer">
                            <source src="<?php echo htmlspecialchars($song['file_path']); ?>" type="audio/mpeg">
                            Your browser does not support the audio tag.
                        </audio>
                        <div class="audio-player-placeholder">
                            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2c-1.7 0-3 1.2-3 2.6v6.8c0 1.4 1.3 2.6 3 2.6s3-1.2 3-2.6V4.6C15 3.2 13.7 2 12 2z"/>
                                <path d="M19 10v2a7 7 0 0 1-14 0v-2"/>
                                <line x1="12" x2="12" y1="19" y2="22"/>
                                <line x1="8" x2="16" y1="22" y2="22"/>
                            </svg>
                        </div>
                        <div class="song-info-display">
                            <div class="song-title-display"><?php echo htmlspecialchars($song['title']); ?></div>
                            <div class="song-artist-display"><?php echo htmlspecialchars($song['artist']); ?></div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="audio-player">
                        <div class="song-info-display">
                            <div class="song-title-display"><?php echo htmlspecialchars($song['title']); ?></div>
                            <div class="song-artist-display"><?php echo htmlspecialchars($song['artist']); ?></div>
                            <p style="margin-top: 1rem; color: var(--muted-foreground);">
                                Source type: <?php echo htmlspecialchars($song['source_type']); ?>
                            </p>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Score Display -->
        <div class="score-display">
            <div class="score-label">Score</div>
            <div class="score-value" id="scoreValue">0</div>
            <div class="accuracy-value" id="accuracyValue">0.0%</div>
        </div>

        <!-- Lyrics Overlay -->
        <?php if ($song['lyrics']): ?>
            <div class="lyrics-overlay">
                <div class="current-lyric">♪ ♪ ♪</div>
                <div class="next-lyric">Singing...</div>
            </div>
        <?php endif; ?>

        <!-- Controls -->
        <div class="controls">
            <button class="play-button" id="playButton">
                <svg id="playIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M8 5v14l11-7z"/>
                </svg>
                <svg id="pauseIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="display: none;">
                    <rect x="6" y="4" width="4" height="16"/>
                    <rect x="14" y="4" width="4" height="16"/>
                </svg>
            </button>
            <div class="progress-bar">
                <div class="progress-track" id="progressTrack">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
            </div>
            <div class="time-display">
                <span id="currentTime">0:00</span> / <span id="duration">0:00</span>
            </div>
            <div class="volume-control">
                <svg class="volume-icon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <polygon points="11 5 6 9 2 9 2 15 6 15 11 19 11 5"/>
                </svg>
                <input type="range" class="volume-slider" id="volumeSlider" min="0" max="1" step="0.1" value="1">
            </div>
        </div>
    </div>

    <!-- Submit Score Modal -->
    <div class="modal-overlay" id="scoreModal">
        <div class="modal">
            <h2 class="modal-title">Performance Complete!</h2>
            <p class="modal-subtitle">Great singing! Submit your score to the leaderboard.</p>
            <div class="modal-score" id="finalScore">0</div>
            <input type="text" class="modal-input" id="playerName" placeholder="Enter your name" maxlength="20">
            <div class="modal-buttons">
                <button class="modal-button secondary" id="cancelScore">Cancel</button>
                <button class="modal-button primary" id="submitScore">Submit Score</button>
            </div>
        </div>
    </div>

    <script>
        const API_BASE = 'api/index.php';
        const SONG_ID = '<?php echo $songId; ?>';

        // Player elements
        const audioPlayer = document.getElementById('audioPlayer');
        const videoPlayer = document.getElementById('videoPlayer');
        const playButton = document.getElementById('playButton');
        const playIcon = document.getElementById('playIcon');
        const pauseIcon = document.getElementById('pauseIcon');
        const progressTrack = document.getElementById('progressTrack');
        const progressFill = document.getElementById('progressFill');
        const currentTimeEl = document.getElementById('currentTime');
        const durationEl = document.getElementById('duration');
        const volumeSlider = document.getElementById('volumeSlider');
        const scoreValue = document.getElementById('scoreValue');
        const accuracyValue = document.getElementById('accuracyValue');

        // Score modal elements
        const scoreModal = document.getElementById('scoreModal');
        const finalScoreEl = document.getElementById('finalScore');
        const playerNameInput = document.getElementById('playerName');
        const cancelScoreBtn = document.getElementById('cancelScore');
        const submitScoreBtn = document.getElementById('submitScore');

        // Game state
        let isPlaying = false;
        let score = 0;
        let perfectHits = 0;
        let goodHits = 0;
        let missHits = 0;
        let totalHits = 0;
        let mediaElement = null;

        // Determine which media element to use
        if (audioPlayer) {
            mediaElement = audioPlayer;
        } else if (videoPlayer) {
            mediaElement = videoPlayer;
        }

        // Format time
        function formatTime(seconds) {
            if (isNaN(seconds)) return '0:00';
            const mins = Math.floor(seconds / 60);
            const secs = Math.floor(seconds % 60);
            return `${mins}:${secs.toString().padStart(2, '0')}`;
        }

        // Play/Pause
        if (mediaElement) {
            playButton.addEventListener('click', () => {
                if (isPlaying) {
                    mediaElement.pause();
                    playIcon.style.display = 'block';
                    pauseIcon.style.display = 'none';
                } else {
                    mediaElement.play();
                    playIcon.style.display = 'none';
                    pauseIcon.style.display = 'block';
                }
                isPlaying = !isPlaying;
            });

            // Update progress
            mediaElement.addEventListener('timeupdate', () => {
                const progress = (mediaElement.currentTime / mediaElement.duration) * 100;
                progressFill.style.width = `${progress}%`;
                currentTimeEl.textContent = formatTime(mediaElement.currentTime);
                durationEl.textContent = formatTime(mediaElement.duration);
            });

            // Track ended
            mediaElement.addEventListener('ended', () => {
                showScoreModal();
            });

            // Progress click
            progressTrack.addEventListener('click', (e) => {
                const rect = progressTrack.getBoundingClientRect();
                const percent = (e.clientX - rect.left) / rect.width;
                mediaElement.currentTime = percent * mediaElement.duration;
            });

            // Volume control
            volumeSlider.addEventListener('input', (e) => {
                mediaElement.volume = e.target.value;
            });

            // Simulate scoring (in production, this would use actual audio analysis)
            setInterval(() => {
                if (isPlaying) {
                    const hit = Math.random();
                    totalHits++;

                    if (hit > 0.7) {
                        perfectHits++;
                        score += 100;
                    } else if (hit > 0.3) {
                        goodHits++;
                        score += 50;
                    } else {
                        missHits++;
                    }

                    const accuracy = ((perfectHits * 100 + goodHits * 50) / (totalHits * 100)) * 100;
                    scoreValue.textContent = score.toLocaleString();
                    accuracyValue.textContent = `${accuracy.toFixed(1)}%`;
                }
            }, 2000);
        }

        // Show score modal
        function showScoreModal() {
            finalScoreEl.textContent = score.toLocaleString();
            scoreModal.classList.add('active');
            isPlaying = false;
            if (mediaElement) {
                mediaElement.pause();
            }
            playIcon.style.display = 'block';
            pauseIcon.style.display = 'none';
        }

        // Submit score
        submitScoreBtn.addEventListener('click', async () => {
            const playerName = playerNameInput.value.trim() || 'Anonymous';

            try {
                const response = await fetch(`${API_BASE}/scores`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        song_id: SONG_ID,
                        player_name: playerName,
                        score: score,
                        accuracy: parseFloat(accuracyValue.textContent),
                        perfect_hits: perfectHits,
                        good_hits: goodHits,
                        miss_hits: missHits
                    })
                });

                if (response.ok) {
                    alert('Score submitted successfully!');
                    window.location.href = 'index.php';
                } else {
                    const result = await response.json();
                    alert(result.error || 'Failed to submit score');
                }
            } catch (error) {
                alert('Failed to submit score');
            }
        });

        // Cancel
        cancelScoreBtn.addEventListener('click', () => {
            scoreModal.classList.remove('active');
        });

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => {
            if (e.code === 'Space' && document.activeElement !== playerNameInput) {
                e.preventDefault();
                if (mediaElement) {
                    playButton.click();
                }
            }
        });
    </script>
</body>
</html>
