<?php
// Scores endpoints

$conn = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new score
    $input = json_decode(file_get_contents('php://input'), true);

    $id = uniqid();
    $songId = $input['song_id'] ?? '';
    $playerName = $input['player_name'] ?? '';
    $score = $input['score'] ?? 0;
    $accuracy = $input['accuracy'] ?? 0;
    $perfectHits = $input['perfect_hits'] ?? 0;
    $goodHits = $input['good_hits'] ?? 0;
    $missHits = $input['miss_hits'] ?? 0;

    if (empty($songId) || empty($playerName)) {
        http_response_code(400);
        echo json_encode(['error' => 'Song ID and player name are required']);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO scores (id, song_id, player_name, score, accuracy, perfect_hits, good_hits, miss_hits)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('sssdiiii', $id, $songId, $playerName, $score, $accuracy, $perfectHits, $goodHits, $missHits);

    if ($stmt->execute()) {
        echo json_encode(['id' => $id, 'message' => 'Score saved successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to save score']);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if ($resource === 'leaderboard') {
        // Get global leaderboard
        $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 100) : 20;

        $stmt = $conn->prepare("
            SELECT s.*, song.title as song_title, song.artist as song_artist
            FROM scores s
            LEFT JOIN songs song ON s.song_id = song.id
            ORDER BY s.score DESC
            LIMIT ?
        ");
        $stmt->bind_param('i', $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $scores = [];
        while ($row = $result->fetch_assoc()) {
            $scores[] = $row;
        }

        echo json_encode($scores);

    } else {
        // Get scores for a specific song
        $limit = isset($_GET['limit']) ? min((int)$_GET['limit'], 50) : 10;

        $stmt = $conn->prepare("
            SELECT * FROM scores
            WHERE song_id = ?
            ORDER BY score DESC
            LIMIT ?
        ");
        $stmt->bind_param('si', $resource, $limit);
        $stmt->execute();
        $result = $stmt->get_result();

        $scores = [];
        while ($row = $result->fetch_assoc()) {
            $scores[] = $row;
        }

        echo json_encode($scores);
    }

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
