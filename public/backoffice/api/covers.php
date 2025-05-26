<?php
include 'config.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *'); // Allow requests from any origin

$sql = "SELECT game_version_key, game_version_name, image_path FROM game_covers ORDER BY game_version_name ASC";
$result = $conn->query($sql);

$covers = [];

if ($result) {
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            // Prepend 'backoffice/' to the image_path for correct frontend URL construction
            $row['image_path'] = 'backoffice/' . $row['image_path'];
            $covers[] = $row;
        }
        echo json_encode(["success" => true, "covers" => $covers]);
    } else {
        echo json_encode(["success" => true, "covers" => [], "message" => "No covers found."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Error fetching covers: " . $conn->error]);
}

$conn->close();
?>
