<?php
require_once '../config/config.php';

$allowed_origin = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
$allowed_origin .= '://' . $_SERVER['HTTP_HOST'];

header("Access-Control-Allow-Origin: " . $allowed_origin);
header('Access-Control-Allow-Credentials: true');
header("Content-Type: application/json");

$query = isset($_GET['q']) ? $_GET['q'] : '';
$maxResults = isset($_GET['max_results']) ? (int)$_GET['max_results'] : 10;

if (empty($query)) {
    http_response_code(400);
    echo json_encode(["message" => "Query is required."]);
    exit();
}

$apiUrl = "https://www.googleapis.com/youtube/v3/search?" . http_build_query([
    'part' => 'snippet',
    'q' => $query . ' karaoke',
    'type' => 'video',
    'maxResults' => $maxResults,
    'videoCategoryId' => '10', // Music
    'key' => YOUTUBE_API_KEY
]);

$response = @file_get_contents($apiUrl);
if ($response === FALSE) {
    http_response_code(500);
    echo json_encode(["message" => "Failed to fetch data from YouTube API."]);
    exit();
}

$data = json_decode($response, true);
if (isset($data['error'])) {
    http_response_code($data['error']['code']);
    echo json_encode(["message" => $data['error']['message']]);
    exit();
}

$results = [];
foreach ($data['items'] as $item) {
    $results[] = [
        "video_id" => $item['id']['videoId'],
        "title" => $item['snippet']['title'],
        "thumbnail" => $item['snippet']['thumbnails']['medium']['url'],
        "channel" => $item['snippet']['channelTitle']
    ];
}

echo json_encode(["results" => $results]);
