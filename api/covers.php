<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once __DIR__ . '/../backoffice/db.php';

// Fetch covers from database
$stmt = $db->query('SELECT game, filename FROM covers');
$covers = [];
$uploadPath = dirname($_SERVER['SCRIPT_NAME']) . '/../backoffice/uploads/';

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $covers[] = [
        'game' => $row['game'],
        'url'  => $uploadPath . $row['filename'],
    ];
}

echo json_encode($covers);
