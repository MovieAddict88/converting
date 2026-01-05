<?php
// File upload handler

requireAuth();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'No file uploaded or upload error']);
    exit;
}

$conn = getDB();

// Get form data
$title = $_POST['title'] ?? '';
$artist = $_POST['artist'] ?? '';
$lyrics = $_POST['lyrics'] ?? '';

if (empty($title) || empty($artist)) {
    http_response_code(400);
    echo json_encode(['error' => 'Title and artist are required']);
    exit;
}

$file = $_FILES['file'];
$fileSize = $file['size'];

// Check file size
if ($fileSize > MAX_UPLOAD_SIZE) {
    http_response_code(400);
    echo json_encode(['error' => 'File too large. Maximum size: ' . (MAX_UPLOAD_SIZE / 1024 / 1024) . 'MB']);
    exit;
}

// Get file extension
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

// Allowed extensions
$allowedExtensions = ['mp3', 'mp4', 'webm', 'mid', 'midi', 'kar', 'wav'];
if (!in_array($extension, $allowedExtensions)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid file type. Allowed: ' . implode(', ', $allowedExtensions)]);
    exit;
}

// Determine source type
$sourceTypeMap = [
    'mid' => 'midi',
    'midi' => 'midi',
    'kar' => 'kar',
    'mp3' => 'mp3',
    'mp4' => 'mp4',
    'webm' => 'webm',
    'wav' => 'mp3'
];
$sourceType = $sourceTypeMap[$extension] ?? 'upload';

// Create upload directory if it doesn't exist
if (!file_exists(UPLOAD_DIR)) {
    mkdir(UPLOAD_DIR, 0755, true);
}

// Generate unique filename
$fileId = uniqid();
$filename = $fileId . '.' . $extension;
$filePath = UPLOAD_DIR . $filename;

// Move uploaded file
if (!move_uploaded_file($file['tmp_name'], $filePath)) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to move uploaded file']);
    exit;
}

// Insert into database
$id = uniqid();
$stmt = $conn->prepare("
    INSERT INTO songs (id, title, artist, source_type, file_path, lyrics)
    VALUES (?, ?, ?, ?, ?, ?)
");
$stmt->bind_param('ssssss', $id, $title, $artist, $sourceType, $filePath, $lyrics);

if ($stmt->execute()) {
    echo json_encode([
        'id' => $id,
        'message' => 'File uploaded successfully',
        'file_path' => $filePath
    ]);
} else {
    // Delete file if database insert failed
    unlink($filePath);
    http_response_code(500);
    echo json_encode(['error' => 'Failed to save song to database']);
}
