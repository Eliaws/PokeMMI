<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Log file path
$log_file = __DIR__ . '/upload_debug.log';

function log_message($message) {
    global $log_file;
    error_log(date('[Y-m-d H:i:s]') . ' ' . $message . "\\n", 3, $log_file);
}

log_message("upload.php script started.");
log_message("Request method: " . $_SERVER['REQUEST_METHOD']);

// Exit early if not a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    log_message("Invalid request method. Expected POST. Script will exit.");
    header('Content-Type: application/json');
    http_response_code(405); // Method Not Allowed
    echo json_encode(["success" => false, "message" => "Invalid request method. Only POST requests are allowed."]);
    log_message("upload.php script finished due to non-POST request.\\n---\\n");
    exit;
}

// --- Only proceed with POST specific logic below ---
log_message("POST request received. Proceeding with upload logic.");

include 'config.php'; // config.php is now included only for POST requests

// It's good practice to set the content type header as early as possible for POST responses.
header('Content-Type: application/json');

log_message("Included config.php. DB Host from getenv: " . getenv('DB_HOST'));

if (!$conn) {
    log_message("Database connection object is null after config.php include.");
    // Ensure $conn is available or handle error appropriately if config.php failed to provide it.
    // For safety, we'll assume config.php should define $conn. If not, this error is critical.
    echo json_encode(["success" => false, "message" => "Database connection failed. \$conn not established by config.php."]);
    log_message("upload.php script finished due to missing \$conn.\\n---\\n");
    exit;
}

if ($conn->connect_error) {
    log_message("Database connection error: " . $conn->connect_error);
    echo json_encode(["success" => false, "message" => "Database connection error: " . $conn->connect_error]);
    // No need to close $conn if connection itself failed, but good to log and exit.
    log_message("upload.php script finished due to DB connection error.\\n---\\n");
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
    log_message("upload.php script finished due to table creation error.\\n---\\n");
    exit;
}
log_message("Table checked/created successfully.");

// The rest of the POST handling logic (already within the implicit POST block by now)
// log_message("FILES data: " . print_r($_FILES, true)); // Already logged if needed
// log_message("POST data: " . print_r($_POST, true));  // Already logged if needed

if (isset($_FILES['jaquette']) && isset($_POST['version'])) {
    log_message("File and version are set.");
    $file = $_FILES['jaquette'];
    $game_version_key = $_POST['version'];

    // Include game_versions array, ideally from config_utils.php or similar
    // For now, assuming $game_versions is available (e.g., from config.php or config_utils.php)
    // If $game_versions is not defined, the array_key_exists check will cause issues.
    // It should be defined in config.php or a file included by it.
    // Example: require_once 'config_utils.php'; // if game_versions is there

    // Check if $game_versions is defined
    if (!isset($game_versions) || !is_array($game_versions)) {
        $error_message = "Game versions array is not defined or not an array.";
        log_message($error_message);
        echo json_encode(["success" => false, "message" => $error_message]);
        $conn->close();
        log_message("upload.php script finished due to missing game_versions array.\\n---\\n");
        exit;
    }


    if ($file['error'] !== UPLOAD_ERR_OK) {
        $upload_errors = array(
            UPLOAD_ERR_INI_SIZE   => "The uploaded file exceeds the upload_max_filesize directive in php.ini.",
            UPLOAD_ERR_FORM_SIZE  => "The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML form.",
            UPLOAD_ERR_PARTIAL    => "The uploaded file was only partially uploaded.",
            UPLOAD_ERR_NO_FILE    => "No file was uploaded.",
            UPLOAD_ERR_NO_TMP_DIR => "Missing a temporary folder.",
            UPLOAD_ERR_CANT_WRITE => "Failed to write file to disk.",
            UPLOAD_ERR_EXTENSION  => "A PHP extension stopped the file upload."
        );
        $error_code = $file['error'];
        $error_message = isset($upload_errors[$error_code]) ? $upload_errors[$error_code] : "Unknown file upload error code: " . $error_code;
        log_message("File upload error: " . $error_message);
        echo json_encode(["success" => false, "message" => "File upload error: " . $error_message]);
        $conn->close();
        log_message("upload.php script finished due to file upload error.\\n---\\n");
        exit;
    }

    if (!array_key_exists($game_version_key, $game_versions)) {
        $error_message = "Invalid game version selected: " . htmlspecialchars($game_version_key);
        log_message($error_message);
        echo json_encode(["success" => false, "message" => $error_message]);
        $conn->close();
        log_message("upload.php script finished due to invalid game version.\\n---\\n");
        exit;
    }
    $game_version_name = $game_versions[$game_version_key];

    // Sanitize filename (ensure sanitize_filename function is available, e.g. from config_utils.php)
    // Example: require_once 'config_utils.php'; // if sanitize_filename is there
    if (!function_exists('sanitize_filename')) {
        $error_message = "sanitize_filename function is not defined.";
        log_message($error_message);
        echo json_encode(["success" => false, "message" => $error_message . " Please ensure config_utils.php is included and defines it."]);
        $conn->close();
        log_message("upload.php script finished due to missing sanitize_filename function.\\n---\\n");
        exit;
    }
    $original_filename = $file['name'];
    $sanitized_filename = sanitize_filename($original_filename);
    
    // Ensure the filename has an extension
    $file_extension = strtolower(pathinfo($original_filename, PATHINFO_EXTENSION));
    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp']; // Define allowed extensions

    if (empty($file_extension) || !in_array($file_extension, $allowed_extensions)) {
        // Try to guess extension from MIME type if original extension is missing or not allowed
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime_type = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $guessed_extension = '';
        switch ($mime_type) {
            case 'image/jpeg': $guessed_extension = 'jpg'; break;
            case 'image/png':  $guessed_extension = 'png'; break;
            case 'image/gif':  $guessed_extension = 'gif'; break;
            case 'image/webp': $guessed_extension = 'webp'; break;
        }

        if (!empty($guessed_extension) && in_array($guessed_extension, $allowed_extensions)) {
            $file_extension = $guessed_extension; // Use guessed extension if valid
        } else {
            $error_message = "Invalid or missing file extension. Original: '{$original_filename}'. Allowed: " . implode(', ', $allowed_extensions) . ". Detected MIME: {$mime_type}";
            log_message($error_message);
            echo json_encode(["success" => false, "message" => $error_message]);
            $conn->close();
            log_message("upload.php script finished due to invalid file extension.\\n---\\n");
            exit;
        }
    }
    
    // Re-sanitize filename with the confirmed (or guessed) valid extension
    $sanitized_filename_base = pathinfo($sanitized_filename, PATHINFO_FILENAME);
    // Ensure base name is not empty after sanitization
    if(empty($sanitized_filename_base)) {
        $sanitized_filename_base = sanitize_filename($game_version_key . '-' . time());
    }
    $sanitized_filename = $sanitized_filename_base . '.' . $file_extension;


    log_message("Sanitized filename: " . $sanitized_filename);
    // Use __DIR__ for robust path construction
    $upload_dir_name = 'uploads'; // Relative to the 'api' directory's parent
    $upload_dir = __DIR__ . '/../' . $upload_dir_name . '/'; // e.g., /path/to/api/../uploads/ -> /path/to/uploads/
    
    log_message("Absolute upload directory target: " . realpath($upload_dir) ?: $upload_dir);
    
    $upload_file_path = $upload_dir . $sanitized_filename;
    log_message("Attempting to use upload file path: " . $upload_file_path);

    if (!is_dir($upload_dir)) {
        log_message("Upload directory does not exist, attempting to create: " . $upload_dir);
        if (!mkdir($upload_dir, 0775, true)) { // Use 0775 for better security
            $error_message = "Failed to create upload directory: " . $upload_dir . ". Check permissions.";
            log_message($error_message . " Last PHP error: " . print_r(error_get_last(), true));
            echo json_encode(["success" => false, "message" => $error_message]);
            $conn->close();
            log_message("upload.php script finished due to mkdir failure.\\n---\\n");
            exit;
        }
        log_message("Upload directory created successfully: " . $upload_dir);
    } else {
        log_message("Upload directory already exists: " . $upload_dir);
    }

    if (!is_writable($upload_dir)) {
        $error_message = "Upload directory is not writable: " . $upload_dir . ". Check permissions.";
        log_message($error_message);
        echo json_encode(["success" => false, "message" => $error_message]);
        $conn->close();
        log_message("upload.php script finished due to non-writable upload directory.\\n---\\n");
        exit;
    }
    log_message("Upload directory is writable: " . $upload_dir);


    if (move_uploaded_file($file['tmp_name'], $upload_file_path)) {
        log_message("File moved successfully to: " . $upload_file_path);
        // Store relative path for frontend, relative to where index.html (frontend root) can access it
        // If uploads/ is inside public/backoffice/, and index.html is in public/, then path is 'backoffice/uploads/...'
        // The $upload_dir_name is 'uploads', and it's inside 'backoffice' directory.
        // So, from the perspective of the main site (e.g. /index.html), the path is 'backoffice/uploads/filename.ext'
        $relative_image_path = 'backoffice/' . $upload_dir_name . '/' . $sanitized_filename; 
        log_message("Storing relative image path in DB: " . $relative_image_path);

        $stmt = $conn->prepare("INSERT INTO game_covers (game_version_key, game_version_name, image_path) VALUES (?, ?, ?)");
        if ($stmt) {
            log_message("Statement prepared successfully.");
            $stmt->bind_param("sss", $game_version_key, $game_version_name, $relative_image_path);
            log_message("Parameters bound: {$game_version_key}, {$game_version_name}, {$relative_image_path}");
            if ($stmt->execute()) {
                log_message("Statement executed successfully. Rows affected: " . $stmt->affected_rows);
                echo json_encode(["success" => true, "message" => "Jaquette '" . htmlspecialchars($sanitized_filename) . "' uploadée avec succès pour " . htmlspecialchars($game_version_name) . ".", "filepath" => $relative_image_path]);
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
        $php_errormsg = error_get_last()['message'] ?? 'Unknown error';
        $error_message = "Error moving uploaded file. Check permissions for " . realpath($upload_dir) . " and ensure PHP has write access. Temp name: " . $file['tmp_name'] . ", Dest: " . $upload_file_path . ". PHP Error: " . $php_errormsg;
        log_message($error_message);
        echo json_encode(["success" => false, "message" => $error_message]);
    }
} else {
    $error_message = "Missing file (jaquette) or version in POST data.";
    log_message($error_message);
    echo json_encode(["success" => false, "message" => $error_message]);
}

log_message("Closing database connection.");
$conn->close();
log_message("upload.php script finished successfully for POST request.\\n---\\n");
?>
