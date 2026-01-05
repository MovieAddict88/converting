<?php
// YouTube search endpoint

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$query = $_GET['q'] ?? '';
$apiKey = $_GET['api_key'] ?? '';
$maxResults = isset($_GET['max_results']) ? min((int)$_GET['max_results'], 50) : 10;

if (empty($query) || empty($apiKey)) {
    http_response_code(400);
    echo json_encode(['error' => 'Query and API key are required']);
    exit;
}

// YouTube API endpoint
$endpoint = 'https://www.googleapis.com/youtube/v3/search?' . http_build_query([
    'part' => 'snippet',
    'q' => $query . ' karaoke',
    'type' => 'video',
    'maxResults' => $maxResults,
    'videoCategoryId' => '10',
    'key' => $apiKey
]);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $endpoint);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$error = curl_error($ch);
curl_close($ch);

if ($error) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to connect to YouTube API: ' . $error]);
    exit;
}

if ($httpCode !== 200) {
    $data = json_decode($response, true);
    $errorMsg = $data['error']['message'] ?? 'Unknown error';

    if (strpos($errorMsg, 'quotaExceeded') !== false) {
        http_response_code(429);
        echo json_encode(['error' => 'YouTube API quota exceeded']);
    } else {
        http_response_code(400);
        echo json_encode(['error' => $errorMsg]);
    }
    exit;
}

$data = json_decode($response, true);
$results = [];

if (isset($data['items'])) {
    foreach ($data['items'] as $item) {
        $results[] = [
            'video_id' => $item['id']['videoId'],
            'title' => $item['snippet']['title'],
            'thumbnail' => $item['snippet']['thumbnails']['medium']['url'] ?? $item['snippet']['thumbnails']['default']['url'],
            'channel' => $item['snippet']['channelTitle']
        ];
    }
}

echo json_encode(['results' => $results]);
