<?php
session_start();
header('Content-Type: application/json');
$conn = new mysqli('localhost', 'root', '', 'art_gallery');
if ($conn->connect_error) {
    echo json_encode(['artworks' => []]);
    exit;
}

$page = max(1, intval($_GET['page'] ?? 1));
$artist_id = isset($_GET['artist_id']) && $_GET['artist_id'] !== '' ? intval($_GET['artist_id']) : null;
$type = isset($_GET['type']) && $_GET['type'] !== '' ? $conn->real_escape_string($_GET['type']) : null;

$limit = 6;
$offset = ($page - 1) * $limit;

$where = "WHERE available=1";
if ($artist_id) {
    $where .= " AND artist_id = $artist_id";
}
if ($type) {
    $where .= " AND type = '$type'";
}

// Join artworks with artist name from users table
$sql = "SELECT a.id, a.title, a.description, a.price, a.image, a.artist_id, a.type,
        u.name AS artist_name,
        (SELECT COUNT(*) FROM artwork_likes l WHERE l.artwork_id = a.id) AS likes
        FROM artworks a
        JOIN users u ON a.artist_id = u.id
        $where
        ORDER BY a.id DESC
        LIMIT $limit OFFSET $offset";

$result = $conn->query($sql);
$artworks = [];

while ($row = $result->fetch_assoc()) {
    $artworks[] = [
        'id' => intval($row['id']),
        'title' => $row['title'],
        'description' => $row['description'],
        'price' => floatval($row['price']),
        'image' => $row['image'],
        'artist_id' => intval($row['artist_id']),
        'artist_name' => $row['artist_name'],
        'type' => $row['type'],
        'likes' => intval($row['likes']),
    ];
}

echo json_encode(['artworks' => $artworks]);
