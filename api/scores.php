<?php
require_once '../config/config.php';

$allowed_origin = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$allowed_origin .= '://' . $_SERVER['HTTP_HOST'];

header("Access-Control-Allow-Origin: " . $allowed_origin);
header('Access-Control-Allow-Credentials: true');
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit();
}

$method = $_SERVER['REQUEST_METHOD'];

if ($method === 'GET') {
    if (isset($_GET['song_id'])) {
        $song_id = $_GET['song_id'];
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $stmt = $conn->prepare("SELECT * FROM scores WHERE song_id = ? ORDER BY score DESC LIMIT ?");
        $stmt->bind_param("si", $song_id, $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $scores = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($scores);
    } else {
        // Leaderboard
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $stmt = $conn->prepare("SELECT * FROM scores ORDER BY score DESC LIMIT ?");
        $stmt->bind_param("i", $limit);
        $stmt->execute();
        $result = $stmt->get_result();
        $scores = $result->fetch_all(MYSQLI_ASSOC);
        echo json_encode($scores);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->song_id) || empty($data->player_name) || !isset($data->score) || !isset($data->accuracy)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit();
    }

    $id = uniqid();
    $created_at = date('Y-m-d H:i:s');

    $stmt = $conn->prepare("INSERT INTO scores (id, song_id, player_name, score, accuracy, created_at) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("sssids", $id, $data->song_id, $data->player_name, $data->score, $data->accuracy, $created_at);

    if ($stmt->execute()) {
        http_response_code(201);
        echo json_encode(["id" => $id, "song_id" => $data->song_id, "player_name" => $data->player_name, "score" => $data->score, "accuracy" => $data->accuracy, "created_at" => $created_at]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to create score."]);
    }
}

$conn->close();
