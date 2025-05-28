<?php
require_once 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Check database connection
if (!$conn || $conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed"]);
    exit;
}

$sql = "SELECT DISTINCT game_version_key, game_version_name, image_path FROM game_covers ORDER BY game_version_name ASC";
$result = $conn->query($sql);

$covers = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Clean up image_path to ensure it doesn't have double "backoffice/"
        $imagePath = $row['image_path'];
        
        // Remove any leading "backoffice/" if it exists
        if (strpos($imagePath, 'backoffice/') === 0) {
            $imagePath = substr($imagePath, 10); // Remove "backoffice/"
        }
        
        // Store the cleaned path (will be just "uploads/filename.ext")
        $row['image_path'] = $imagePath;
        $covers[] = $row;
    }
    
    // Remove duplicates based on game_version_key (keep only the first occurrence)
    $uniqueCovers = [];
    $seenKeys = [];
    
    foreach ($covers as $cover) {
        if (!in_array($cover['game_version_key'], $seenKeys)) {
            $uniqueCovers[] = $cover;
            $seenKeys[] = $cover['game_version_key'];
        }
    }
    
    echo json_encode(["success" => true, "covers" => $uniqueCovers]);
} else {
    echo json_encode(["success" => true, "covers" => []]);
}

$conn->close();