<?php
// Songs CRUD endpoints

$conn = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (empty($resource)) {
        // Get all songs with optional search
        $search = $_GET['search'] ?? '';

        if (!empty($search)) {
            $stmt = $conn->prepare("
                SELECT * FROM songs
                WHERE title LIKE ? OR artist LIKE ?
                ORDER BY created_at DESC
                LIMIT 100
            ");
            $searchTerm = "%$search%";
            $stmt->bind_param('ss', $searchTerm, $searchTerm);
        } else {
            $stmt = $conn->prepare("SELECT * FROM songs ORDER BY created_at DESC LIMIT 100");
        }

        $stmt->execute();
        $result = $stmt->get_result();
        $songs = [];

        while ($row = $result->fetch_assoc()) {
            $songs[] = $row;
        }

        echo json_encode($songs);

    } else {
        // Get single song
        $stmt = $conn->prepare("SELECT * FROM songs WHERE id = ?");
        $stmt->bind_param('s', $resource);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(404);
            echo json_encode(['error' => 'Song not found']);
            exit;
        }

        echo json_encode($result->fetch_assoc());
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Create new song
    requireAuth();
    $input = json_decode(file_get_contents('php://input'), true);

    $id = uniqid();
    $title = $input['title'] ?? '';
    $artist = $input['artist'] ?? '';
    $sourceType = $input['source_type'] ?? 'upload';
    $sourceUrl = $input['source_url'] ?? null;
    $thumbnail = $input['thumbnail'] ?? null;
    $duration = $input['duration'] ?? null;
    $lyrics = $input['lyrics'] ?? null;

    if (empty($title) || empty($artist)) {
        http_response_code(400);
        echo json_encode(['error' => 'Title and artist are required']);
        exit;
    }

    $stmt = $conn->prepare("
        INSERT INTO songs (id, title, artist, source_type, source_url, thumbnail, duration, lyrics)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param('ssssssis', $id, $title, $artist, $sourceType, $sourceUrl, $thumbnail, $duration, $lyrics);

    if ($stmt->execute()) {
        echo json_encode(['id' => $id, 'message' => 'Song created successfully']);
    } else {
        http_response_code(500);
        echo json_encode(['error' => 'Failed to create song']);
    }

} elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
    // Update song
    requireAuth();

    if (empty($resource)) {
        http_response_code(400);
        echo json_encode(['error' => 'Song ID required']);
        exit;
    }

    $input = json_decode(file_get_contents('php://input'), true);

    $title = $input['title'] ?? null;
    $artist = $input['artist'] ?? null;
    $lyrics = $input['lyrics'] ?? null;
    $thumbnail = $input['thumbnail'] ?? null;

    if ($title !== null) {
        $stmt = $conn->prepare("UPDATE songs SET title = ?, artist = ?, lyrics = ?, thumbnail = ? WHERE id = ?");
        $stmt->bind_param('sssss', $title, $artist, $lyrics, $thumbnail, $resource);
        $stmt->execute();
    }

    echo json_encode(['message' => 'Song updated successfully']);

} elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
    // Delete song
    requireAuth();

    if (empty($resource)) {
        http_response_code(400);
        echo json_encode(['error' => 'Song ID required']);
        exit;
    }

    // Get song file path to delete file
    $stmt = $conn->prepare("SELECT file_path FROM songs WHERE id = ?");
    $stmt->bind_param('s', $resource);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $song = $result->fetch_assoc();
        if ($song['file_path'] && file_exists($song['file_path'])) {
            unlink($song['file_path']);
        }
    }

    $stmt = $conn->prepare("DELETE FROM songs WHERE id = ?");
    $stmt->bind_param('s', $resource);
    $stmt->execute();

    echo json_encode(['message' => 'Song deleted successfully']);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
