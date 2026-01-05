<?php
// Authentication endpoints

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);

    if (!isset($resource) || $resource === 'login') {
        // Login
        $username = $input['username'] ?? '';
        $password = $input['password'] ?? '';

        if (empty($username) || empty($password)) {
            http_response_code(400);
            echo json_encode(['error' => 'Username and password required']);
            exit;
        }

        $conn = getDB();
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 0) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            exit;
        }

        $admin = $result->fetch_assoc();

        if (!password_verify($password, $admin['password'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid credentials']);
            exit;
        }

        $token = generateToken([
            'user_id' => $admin['id'],
            'username' => $admin['username']
        ]);

        echo json_encode([
            'token' => $token,
            'username' => $admin['username'],
            'user_id' => $admin['id']
        ]);

    } elseif ($resource === 'verify') {
        // Verify token
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? '';

        if (!preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            http_response_code(401);
            echo json_encode(['valid' => false]);
            exit;
        }

        $data = verifyToken($matches[1]);
        echo json_encode(['valid' => $data !== false, 'data' => $data]);

    } else {
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
}
