<?php
/**
 * NeonVox API - Main Entry Point
 * Handles all API requests
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include configuration
if (!file_exists(__DIR__ . '/../config.php')) {
    http_response_code(500);
    echo json_encode(['error' => 'Configuration file not found. Please run install.php']);
    exit;
}

require_once __DIR__ . '/../config.php';

// Get request info
$method = $_SERVER['REQUEST_METHOD'];
$path = $_SERVER['PATH_INFO'] ?? '/';
$path = rtrim($path, '/');

// Parse URL path
$parts = explode('/', trim($path, '/'));
$endpoint = $parts[0] ?? '';
$resource = $parts[1] ?? '';
$id = $parts[2] ?? '';

// Database connection
function getDB() {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            http_response_code(500);
            echo json_encode(['error' => 'Database connection failed']);
            exit;
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

// JWT functions
function generateToken($data) {
    $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);
    $payload = json_encode($data + ['exp' => time() + 86400]); // 24 hours
    $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));
    $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));
    $signature = hash_hmac('sha256', $base64UrlHeader . '.' . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));
    return $base64UrlHeader . '.' . $base64UrlPayload . '.' . $base64UrlSignature;
}

function verifyToken($token) {
    try {
        $parts = explode('.', $token);
        if (count($parts) !== 3) return false;

        $header = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[0]));
        $payload = base64_decode(str_replace(['-', '_'], ['+', '/'], $parts[1]));
        $signature = str_replace(['-', '_'], ['+', '/'], $parts[2]);

        $expectedSignature = hash_hmac('sha256', $parts[0] . '.' . $parts[1], JWT_SECRET, true);
        if (!hash_equals($signature, $expectedSignature)) return false;

        $data = json_decode($payload, true);
        if ($data['exp'] < time()) return false;

        return $data;
    } catch (Exception $e) {
        return false;
    }
}

function requireAuth() {
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? '';

    if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    $token = $matches[1];
    $data = verifyToken($token);

    if (!$data) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid or expired token']);
        exit;
    }

    return $data;
}

// Route requests
switch ($endpoint) {
    case 'auth':
        require __DIR__ . '/auth.php';
        break;

    case 'songs':
        require __DIR__ . '/songs.php';
        break;

    case 'scores':
        require __DIR__ . '/scores.php';
        break;

    case 'youtube':
        require __DIR__ . '/youtube.php';
        break;

    case 'upload':
        require __DIR__ . '/upload.php';
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
