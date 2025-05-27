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

// Check if essential database configuration variables are set by the pipeline
if (!isset($DB_HOST) || !isset($DB_USER) || !isset($DB_PASS) || !isset($DB_NAME)) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    echo json_encode([
        "success" => false,
        "message" => "CRITICAL: Database configuration variables (DB_HOST, DB_USER, DB_PASS, DB_NAME) are not set. The CI/CD pipeline might have failed to generate config.php correctly with these variables.",
        "debug_info" => [
            "db_host_expected_from_pipeline" => !isset($DB_HOST) ? 'MISSING' : 'set',
            "db_user_expected_from_pipeline" => !isset($DB_USER) ? 'MISSING' : 'set',
            "db_pass_expected_from_pipeline" => !isset($DB_PASS) ? 'MISSING' : 'set', // Avoid logging actual password value
            "db_name_expected_from_pipeline" => !isset($DB_NAME) ? 'MISSING' : 'set',
            "config_utils_loaded" => (function_exists('sanitize_filename') && isset($game_versions)) ? 'yes' : 'no, or functions/vars missing',
        ]
    ]);
    exit;
}

// Establish database connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

// Check connection
if ($conn->connect_error) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }
    if (function_exists('log_message')) {
        log_message("CRITICAL: Database connection failed: " . $conn->connect_error . " (Host: $DB_HOST, User: $DB_USER)");
    }
    echo json_encode([
        "success" => false,
        "message" => "Database connection failed: " . $conn->connect_error,
        "debug_info" => [
            "host_used" => $DB_HOST,
            "user_used" => $DB_USER,
            "error_code" => $conn->connect_errno,
            "error_message" => $conn->connect_error
        ]
    ]);
    exit;
}

// The connection $conn is now available.
// sanitize_filename() and $game_versions are available from config_utils.php.
?>
