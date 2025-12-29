<?php
require_once '../config/config.php';

$allowed_origin = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$allowed_origin .= '://' . $_SERVER['HTTP_HOST'];

header("Access-Control-Allow-Origin: " . $allowed_origin);
header('Access-control-allow-credentials: true');
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST");
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
    if (isset($_GET['song_id'])) {
        $song_id = $_GET['song_id'];
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
        $stmt = $pdo->prepare("SELECT * FROM scores WHERE song_id = :song_id ORDER BY score DESC LIMIT :limit");
        $stmt->execute([':song_id' => $song_id, ':limit' => $limit]);
        $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($scores);
    } else {
        // Leaderboard
        $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 20;
        $stmt = $pdo->prepare("SELECT * FROM scores ORDER BY score DESC LIMIT :limit");
        $stmt->execute([':limit' => $limit]);
        $scores = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($scores);
    }
} elseif ($method === 'POST') {
    $data = json_decode(file_get_contents("php://input"));

    if (empty($data->song_id) || empty($data->player_name) || !isset($data->score) || !isset($data->accuracy)) {
        http_response_code(400);
        echo json_encode(["message" => "Invalid input"]);
        exit();
    }

    $id = bin2hex(random_bytes(18)); // Generate a 36-character hex ID
    $created_at = date('Y-m-d H:i:s');

    $stmt = $pdo->prepare("INSERT INTO scores (id, song_id, player_name, score, accuracy, created_at) VALUES (:id, :song_id, :player_name, :score, :accuracy, :created_at)");

    $params = [
        ':id' => $id,
        ':song_id' => $data->song_id,
        ':player_name' => $data->player_name,
        ':score' => $data->score,
        ':accuracy' => $data->accuracy,
        ':created_at' => $created_at
    ];

    if ($stmt->execute($params)) {
        http_response_code(201);
        echo json_encode([
            "id" => $id,
            "song_id" => $data->song_id,
            "player_name" => $data->player_name,
            "score" => $data->score,
            "accuracy" => $data->accuracy,
            "created_at" => $created_at
        ]);
    } else {
        http_response_code(500);
        echo json_encode(["message" => "Failed to create score."]);
    }
}
