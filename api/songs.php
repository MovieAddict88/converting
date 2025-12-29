<?php
require_once '../config/config.php';
require_once 'middleware.php';

$allowed_origin = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$allowed_origin .= '://' . $_SERVER['HTTP_HOST'];

header("Access-Control-Allow-Origin: " . $allowed_origin);
header('Access-Control-Allow-Credentials: true');
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

try {
    $pdo = new PDO('sqlite:' . DB_PATH);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed: " . $e->getMessage()]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $pdo->prepare("SELECT * FROM songs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $song = $stmt->fetch(PDO::FETCH_ASSOC);
        echo json_encode($song);
    } else {
        $search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";
        $stmt = $pdo->prepare("SELECT * FROM songs WHERE title LIKE :search OR artist LIKE :search ORDER BY created_at DESC");
        $stmt->execute([':search' => $search]);
        $songs = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($songs);
    }
} elseif ($method === 'POST') {
    verify_token();

    if (empty($_POST['title']) || empty($_POST['artist']) || empty($_POST['source_type'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit();
    }

    $id = bin2hex(random_bytes(18)); // Generate a 36-character hex ID
    $created_at = date('Y-m-d H:i:s');
    $file_path = null;

    if (isset($_FILES['file'])) {
        $upload_dir = '../uploads/';
        $file_ext = pathinfo($_FILES['file']['name'], PATHINFO_EXTENSION);
        $file_path = $upload_dir . $id . '.' . $file_ext;
        if (!move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            http_response_code(500);
            echo json_encode(["message" => "Failed to upload file."]);
            exit();
        }
    }

    $stmt = $pdo->prepare("INSERT INTO songs (id, title, artist, source_type, source_url, file_path, thumbnail, duration, lyrics, created_at) VALUES (:id, :title, :artist, :source_type, :source_url, :file_path, :thumbnail, :duration, :lyrics, :created_at)");

    $params = [
        ':id' => $id,
        ':title' => $_POST['title'],
        ':artist' => $_POST['artist'],
        ':source_type' => $_POST['source_type'],
        ':source_url' => $_POST['source_url'] ?? null,
        ':file_path' => $file_path,
        ':thumbnail' => $_POST['thumbnail'] ?? null,
        ':duration' => $_POST['duration'] ?? null,
        ':lyrics' => $_POST['lyrics'] ?? null,
        ':created_at' => $created_at
    ];

    if ($stmt->execute($params)) {
        http_response_code(201);
        echo json_encode(["id" => $id, "title" => $_POST['title'], "artist" => $_POST['artist'], "created_at" => $created_at]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to create song."]);
    }
} elseif ($method === 'DELETE') {
    verify_token();

    if (empty($_GET['id'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit();
    }

    $id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT file_path FROM songs WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $song = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($song && !empty($song['file_path']) && file_exists($song['file_path'])) {
        unlink($song['file_path']);
    }

    $stmt = $pdo->prepare("DELETE FROM songs WHERE id = :id");
    if ($stmt->execute([':id' => $id])) {
        http_response_code(200);
        echo json_encode(["message" => "Song deleted"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to delete song."]);
    }
}
