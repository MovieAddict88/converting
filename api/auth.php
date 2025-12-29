<?php
require_once '../vendor/autoload.php';
require_once '../config/config.php';

use \Firebase\JWT\JWT;

$allowed_origin = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$allowed_origin .= '://' . $_SERVER['HTTP_HOST'];

header("Access-Control-Allow-Origin: " . $allowed_origin);
header('Access-Control-Allow-Credentials: true');
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle different content types
$content_type = isset($_SERVER['CONTENT_TYPE']) ? trim($_SERVER['CONTENT_TYPE']) : '';
$username = '';
$password = '';

if (strpos($content_type, 'application/json') !== false) {
    $data = json_decode(file_get_contents("php://input"));
    $username = $data->username ?? '';
    $password = $data->password ?? '';
} else {
    // Assume form data
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
}


if (empty($username) || empty($password)) {
    http_response_code(400);
    echo json_encode(["message" => "Invalid input. Username and password are required."]);
    exit();
}

$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed"]);
    exit();
}

$stmt = $conn->prepare("SELECT * FROM admins WHERE username = ?");
$stmt->bind_param("s", $username);
$stmt->execute();
$result = $stmt->get_result();
$admin = $result->fetch_assoc();

if (!$admin || !password_verify($password, $admin['password'])) {
    http_response_code(401);
    echo json_encode(["message" => "Invalid credentials"]);
    exit();
}

$issuer_claim = $_SERVER['SERVER_NAME'];
$audience_claim = $_SERVER['SERVER_NAME'];
$issuedat_claim = time();
$notbefore_claim = $issuedat_claim;
$expire_claim = $issuedat_claim + 3600;

$token = array(
    "iss" => $issuer_claim,
    "aud" => $audience_claim,
    "iat" => $issuedat_claim,
    "nbf" => $notbefore_claim,
    "exp" => $expire_claim,
    "data" => array(
        "id" => $admin['id'],
        "username" => $admin['username']
    )
);

$jwt = JWT::encode($token, JWT_SECRET, 'HS256');
http_response_code(200);
echo json_encode(
    array(
        "message" => "Successful login.",
        "token" => $jwt,
        "username" => $admin['username']
    )
);
