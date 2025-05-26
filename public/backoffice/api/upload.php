<?php
// filepath: c:\Users\eliax\Desktop\repo_github\PokeMMI\public\backoffice\api\upload.php
include 'config.php';

header('Content-Type: application/json');

// Create table if it doesn't exist
$createTableSql = "CREATE TABLE IF NOT EXISTS game_covers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_version_key VARCHAR(255) NOT NULL,
    game_version_name VARCHAR(255) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

if (!$conn->query($createTableSql)) {
    echo json_encode(["success" => false, "message" => "Error creating table: " . $conn->error]);
    $conn->close();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['jaquette']) && isset($_POST['version'])) {
        $file = $_FILES['jaquette'];
        $game_version_key = $_POST['version'];

        if (!array_key_exists($game_version_key, $game_versions)) {
            echo json_encode(["success" => false, "message" => "Invalid game version selected."]);
            $conn->close();
            exit;
        }
        $game_version_name = $game_versions[$game_version_key];

        // Sanitize filename
        $original_filename = $file['name'];
        $sanitized_filename = sanitize_filename($original_filename);
        
        // Ensure the filename has an extension
        $file_extension = pathinfo($original_filename, PATHINFO_EXTENSION);
        if (empty($file_extension)) {
            // Try to guess extension from MIME type if original extension is missing
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mime_type = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
            $guessed_extension = '';
            switch ($mime_type) {
                case 'image/jpeg':
                    $guessed_extension = 'jpg';
                    break;
                case 'image/png':
                    $guessed_extension = 'png';
                    break;
                case 'image/gif':
                    $guessed_extension = 'gif';
                    break;
                // Add more MIME types and extensions as needed
            }
            if (!empty($guessed_extension)) {
                $sanitized_filename_base = pathinfo($sanitized_filename, PATHINFO_FILENAME);
                $sanitized_filename = $sanitized_filename_base . '.' . $guessed_extension;
            } else {
                 // Fallback if extension cannot be determined
                $sanitized_filename = sanitize_filename($game_version_key . '-' . time()); 
            }
        } else {
            // Ensure the sanitized filename retains its original extension
            $sanitized_filename_base = pathinfo($sanitized_filename, PATHINFO_FILENAME);
            $sanitized_filename = $sanitized_filename_base . '.' . $file_extension;
        }


        $upload_dir = '../uploads/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $upload_file_path = $upload_dir . $sanitized_filename;

        if (move_uploaded_file($file['tmp_name'], $upload_file_path)) {
            // Store in database
            $stmt = $conn->prepare("INSERT INTO game_covers (game_version_key, game_version_name, image_path) VALUES (?, ?, ?)");
            if ($stmt) {
                $relative_image_path = 'uploads/' . $sanitized_filename; // Store relative path for frontend
                $stmt->bind_param("sss", $game_version_key, $game_version_name, $relative_image_path);
                if ($stmt->execute()) {
                    echo json_encode(["success" => true, "message" => "Jaquette uploadée avec succès: " . $sanitized_filename, "filepath" => $relative_image_path]);
                } else {
                    echo json_encode(["success" => false, "message" => "Error saving to database: " . $stmt->error]);
                }
                $stmt->close();
            } else {
                 echo json_encode(["success" => false, "message" => "Error preparing statement: " . $conn->error]);
            }
        } else {
            echo json_encode(["success" => false, "message" => "Error uploading file."]);
        }
    } else {
        echo json_encode(["success" => false, "message" => "Missing file or version."]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method."]);
}

$conn->close();
?>
