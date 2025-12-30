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

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conn->prepare("SELECT * FROM songs WHERE id = ?");
        $stmt->bind_param("s", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $song = $result->fetch_assoc();
        echo json_encode($song);
    } else {
        $search = isset($_GET['search']) ? "%" . $_GET['search'] . "%" : "%";
        $stmt = $conn->prepare("SELECT * FROM songs WHERE title LIKE ? OR artist LIKE ? ORDER BY created_at DESC");
        $stmt->bind_param("ss", $search, $search);
        $stmt->execute();
        $result = $stmt->get_result();
        $songs = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($songs);
    }
} elseif ($method === 'POST') {
    verify_token();

    if (empty($_POST['title']) || empty($_POST['artist']) || empty($_POST['source_type'])) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit();
    }

    $id = uniqid();
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

    $stmt = $conn->prepare("INSERT INTO songs (id, title, artist, source_type, source_url, file_path, thumbnail, duration, lyrics, created_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssisss", $id, $_POST['title'], $_POST['artist'], $_POST['source_type'], $_POST['source_url'], $file_path, $_POST['thumbnail'], $_POST['duration'], $_POST['lyrics'], $created_at);

    if ($stmt->execute()) {
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
    $stmt = $conn->prepare("SELECT file_path FROM songs WHERE id = ?");
    $stmt->bind_param("s", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $song = $result->fetch_assoc();

    if ($song && !empty($song['file_path']) && file_exists($song['file_path'])) {
        unlink($song['file_path']);
    }

    $stmt = $conn->prepare("DELETE FROM songs WHERE id = ?");
    $stmt->bind_param("s", $id);
    if ($stmt->execute()) {
        http_response_code(200);
        echo json_encode(["message" => "Song deleted"]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to delete song."]);
    }
}

$conn->close();
