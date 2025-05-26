<?php
// filepath: c:\Users\eliax\Desktop\repo_github\PokeMMI\public\backoffice\api\upload.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log file path
$log_file = __DIR__ . '/upload_debug.log';
// Clear log file for each request for easier debugging during development
// file_put_contents($log_file, "--- New Upload Request ---\n", FILE_APPEND);

function log_message($message) {
    global $log_file;
    error_log(date('[Y-m-d H:i:s]') . ' ' . $message . "\n", 3, $log_file);
}

log_message("upload.php script started.");

include 'config.php';

header('Content-Type: application/json');

log_message("Included config.php. DB Host from getenv: " . getenv('DB_HOST'));

if (!$conn) {
    log_message("Database connection object ($conn) is null after config.php include.");
    echo json_encode(["success" => false, "message" => "Database connection failed. Check server logs and env.php."]);
    exit;
}
if ($conn->connect_error) {
    log_message("Database connection error: " . $conn->connect_error);
    echo json_encode(["success" => false, "message" => "Database connection error: " . $conn->connect_error]);
    exit;
}
log_message("Database connection successful.");

// Create table if it doesn't exist
$createTableSql = "CREATE TABLE IF NOT EXISTS game_covers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_version_key VARCHAR(255) NOT NULL,
    game_version_name VARCHAR(255) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);";

log_message("Attempting to create table if not exists.");
if (!$conn->query($createTableSql)) {
    $error_message = "Error creating table: " . $conn->error;
    log_message($error_message);
    echo json_encode(["success" => false, "message" => $error_message]);
    $conn->close();
    exit;
}
log_message("Table checked/created successfully.");

log_message("Request method: " . $_SERVER['REQUEST_METHOD']);
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    log_message("POST request received.");
    log_message("FILES data: " . print_r($_FILES, true));
    log_message("POST data: " . print_r($_POST, true));

    if (isset($_FILES['jaquette']) && isset($_POST['version'])) {
        log_message("File and version are set.");
        $file = $_FILES['jaquette'];
        $game_version_key = $_POST['version'];

        if ($file['error'] !== UPLOAD_ERR_OK) {
            $error_message = "File upload error code: " . $file['error'];
            log_message($error_message);
            echo json_encode(["success" => false, "message" => $error_message]);
            $conn->close();
            exit;
        }

        if (!array_key_exists($game_version_key, $game_versions)) {
            $error_message = "Invalid game version selected: " . $game_version_key;
            log_message($error_message);
            echo json_encode(["success" => false, "message" => $error_message]);
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

        log_message("Sanitized filename: " . $sanitized_filename);
        $upload_dir = '../uploads/';
        log_message("Upload directory: " . $upload_dir);
        $upload_file_path = $upload_dir . $sanitized_filename;
        log_message("Upload file path: " . $upload_file_path);

        if (!is_dir($upload_dir)) {
            log_message("Upload directory does not exist, attempting to create: " . $upload_dir);
            if (!mkdir($upload_dir, 0777, true)) {
                $error_message = "Failed to create upload directory: " . $upload_dir;
                log_message($error_message);
                echo json_encode(["success" => false, "message" => $error_message]);
                $conn->close();
                exit;
            }
            log_message("Upload directory created successfully.");
        }

        if (move_uploaded_file($file['tmp_name'], $upload_file_path)) {
            log_message("File moved successfully to: " . $upload_file_path);
            // Store in database
            $stmt = $conn->prepare("INSERT INTO game_covers (game_version_key, game_version_name, image_path) VALUES (?, ?, ?)");
            if ($stmt) {
                log_message("Statement prepared successfully.");
                $relative_image_path = 'uploads/' . $sanitized_filename; // Store relative path for frontend
                $stmt->bind_param("sss", $game_version_key, $game_version_name, $relative_image_path);
                log_message("Parameters bound: {$game_version_key}, {$game_version_name}, {$relative_image_path}");
                if ($stmt->execute()) {
                    log_message("Statement executed successfully. Rows affected: " . $stmt->affected_rows);
                    echo json_encode(["success" => true, "message" => "Jaquette uploadée avec succès: " . $sanitized_filename, "filepath" => $relative_image_path]);
                } else {
                    $error_message = "Error saving to database: " . $stmt->error;
                    log_message($error_message);
                    echo json_encode(["success" => false, "message" => $error_message]);
                }
                $stmt->close();
            } else {
                $error_message = "Error preparing statement: " . $conn->error;
                log_message($error_message);
                 echo json_encode(["success" => false, "message" => $error_message]);
            }
        } else {
            $error_message = "Error moving uploaded file. Check permissions for " . $upload_dir . " and ensure PHP has write access. Temp name: " . $file['tmp_name'] . ", Dest: " . $upload_file_path;
            log_message($error_message);
            echo json_encode(["success" => false, "message" => $error_message]);
        }
    } else {
        $error_message = "Missing file (jaquette) or version in POST data.";
        log_message($error_message);
        echo json_encode(["success" => false, "message" => $error_message]);
    }
} else {
    $error_message = "Invalid request method. Expected POST.";
    log_message($error_message);
    echo json_encode(["success" => false, "message" => $error_message]);
}

log_message("Closing database connection.");
$conn->close();
log_message("upload.php script finished.\n---\n");
?>
