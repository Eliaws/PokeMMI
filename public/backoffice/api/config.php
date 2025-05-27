<?php
// This file is intended to be overwritten by the CI/CD pipeline.
// The pipeline should define $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME directly
// and ensure config_utils.php is present.

// Alternative: Try to load from .env file if pipeline variables are not set
$env_file_path = __DIR__ . '/../.env';
if (!isset($DB_HOST) && file_exists($env_file_path)) {
    // Load .env file using parse_ini_file() as fallback
    $env = parse_ini_file($env_file_path);
    if ($env !== false) {
        $DB_HOST = $env['VITE_DB_HOST'] ?? null;
        $DB_USER = $env['VITE_DB_USER'] ?? null;
        $DB_PASS = $env['VITE_DB_PASS'] ?? null;
        $DB_NAME = $env['VITE_DB_NAME'] ?? null;
    }
}

// The pipeline is responsible for ensuring config_utils.php is present.
if (!file_exists(__DIR__ . '/config_utils.php')) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        "success" => false,
        "message" => "CRITICAL: config_utils.php not found. Deployment issue from CI/CD pipeline.",
        "debug_info" => [
            "expected_path" => __DIR__ . '/config_utils.php'
        ]
    ]);
    exit;
}
require_once __DIR__ . '/config_utils.php';

// Database credentials ($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME)
// are expected to be defined by the pipeline-generated config.php that overwrites this file.

// Check if essential database configuration variables are set by the pipeline or .env file
if (!isset($DB_HOST) || !isset($DB_USER) || !isset($DB_PASS) || !isset($DB_NAME)) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        "success" => false,
        "message" => "CRITICAL: Database configuration variables (DB_HOST, DB_USER, DB_PASS, DB_NAME) are not set. Please check CI/CD pipeline configuration or create a .env file.",
        "debug_info" => [
            "db_host_status" => !isset($DB_HOST) ? 'MISSING' : 'set',
            "db_user_status" => !isset($DB_USER) ? 'MISSING' : 'set',
            "db_pass_status" => !isset($DB_PASS) ? 'MISSING' : 'set',
            "db_name_status" => !isset($DB_NAME) ? 'MISSING' : 'set',
            "env_file_exists" => file_exists($env_file_path) ? 'yes' : 'no',
            "env_file_path" => $env_file_path,
            "config_utils_loaded" => (function_exists('sanitize_filename') && isset($game_versions)) ? 'yes' : 'no'
        ]
    ]);
    exit;
}

// Establish database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Check connection
if ($conn->connect_error) {
    // Log detailed error to server logs (PHP error log)
    error_log("CRITICAL: Database connection failed for user '$DB_USER' to database '$DB_NAME' on host '$DB_HOST'. Error: " . $conn->connect_error);

    // Send a generic JSON error response to the client
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500); // Internal Server Error
    }
    echo json_encode([
        "success" => false,
        "message" => "Internal Server Error: Could not connect to the database. Please check server logs or contact support."
    ]);
    exit;
}

// Set character set to utf8mb4 for proper encoding support
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
}

// The $conn object is now initialized and ready for use by scripts 
// (e.g., upload.php, covers.php) that include this config.php.

?>
