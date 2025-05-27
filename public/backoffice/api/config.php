<?php
// This file is intended to be overwritten by the CI/CD pipeline.
// The pipeline should define $DB_HOST, $DB_USER, $DB_PASS, $DB_NAME directly
// and ensure config_utils.php is present.

// Alternative: Try to load from .env file if pipeline variables are not set
$env_file_path_relative = __DIR__ . '/../.env';
$env_file_path_absolute = realpath($env_file_path_relative); // Attempt to resolve to canonical path

$debug_env_info = [
    "env_file_path_original_string" => $env_file_path_relative,
    "env_file_path_realpath_resolved" => $env_file_path_absolute ?: 'realpath_failed_or_file_does_not_exist',
    "php_version" => phpversion(),
    "__DIR__" => __DIR__,
    "file_exists_on_original_string" => file_exists($env_file_path_relative) ? 'yes' : 'no',
    "is_readable_on_original_string" => is_readable($env_file_path_relative) ? 'yes' : 'no_or_does_not_exist',
];

if ($env_file_path_absolute) {
    $debug_env_info["file_exists_on_realpath"] = file_exists($env_file_path_absolute) ? 'yes' : 'no';
    $debug_env_info["is_readable_on_realpath"] = is_readable($env_file_path_absolute) ? 'yes' : 'no_or_does_not_exist';
} else {
    $debug_env_info["file_exists_on_realpath"] = 'not_checked_realpath_failed';
    $debug_env_info["is_readable_on_realpath"] = 'not_checked_realpath_failed';
}

// Determine which path to use for parsing
$path_to_parse_env = null;
if ($env_file_path_absolute && file_exists($env_file_path_absolute) && is_readable($env_file_path_absolute)) {
    $path_to_parse_env = $env_file_path_absolute;
} elseif (!$env_file_path_absolute && file_exists($env_file_path_relative) && is_readable($env_file_path_relative)) {
    // Fallback to relative if realpath failed but relative seems to work
    $path_to_parse_env = $env_file_path_relative;
}


if (!isset($DB_HOST) && $path_to_parse_env) {
    $env_vars = parse_ini_file($path_to_parse_env);
    if ($env_vars !== false) {
        $DB_HOST = $env_vars['VITE_DB_HOST'] ?? null;
        $DB_USER = $env_vars['VITE_DB_USER'] ?? null;
        $DB_PASS = $env_vars['VITE_DB_PASS'] ?? null;
        $DB_NAME = $env_vars['VITE_DB_NAME'] ?? null;
        $debug_env_info["env_parsed_successfully_from"] = $path_to_parse_env;
        $debug_env_info["env_content_keys_found"] = array_keys($env_vars);
    } else {
        $debug_env_info["parse_ini_file_failed_on"] = $path_to_parse_env;
        $last_error = error_get_last();
        $debug_env_info["parse_ini_file_error"] = $last_error ? $last_error['message'] : 'no_error_message_retrieved';
    }
} elseif (!isset($DB_HOST)) {
     $debug_env_info["env_not_parsed_reason"] = $path_to_parse_env ? "DB_HOST_already_set_or_other_condition_false" : "path_to_parse_env_is_null (file not found or not readable)";
     $debug_env_info["path_to_parse_env_value_at_decision"] = $path_to_parse_env;
}


// The pipeline is responsible for ensuring config_utils.php is present.
if (!file_exists(__DIR__ . '/config_utils.php')) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(503); // Service Unavailable
    }
    echo json_encode([
        "success" => false,
        "message" => "CRITICAL: config_utils.php not found. Deployment issue from CI/CD pipeline.",
        "debug_info" => [
            "expected_path" => __DIR__ . '/config_utils.php',
            "env_debug_details" => $debug_env_info
        ]
    ]);
    exit;
}
require_once __DIR__ . '/config_utils.php';

// Check if essential database configuration variables are set by the pipeline or .env file
if (!isset($DB_HOST) || !isset($DB_USER) || !isset($DB_PASS) || !isset($DB_NAME)) {
    if (!headers_sent()) {
        header('Content-Type: application/json');
        http_response_code(500); // Internal Server Error
    }
    echo json_encode([
        "success" => false,
        "message" => "CRITICAL: Database configuration variables (DB_HOST, DB_USER, DB_PASS, DB_NAME) are not set. Please check CI/CD pipeline configuration or create a .env file.",
        "debug_info" => [
            "db_host_status" => !isset($DB_HOST) ? 'MISSING' : 'set',
            "db_user_status" => !isset($DB_USER) ? 'MISSING' : 'set',
            "db_pass_status" => !isset($DB_PASS) ? 'MISSING' : 'set',
            "db_name_status" => !isset($DB_NAME) ? 'MISSING' : 'set',
            "env_debug_details" => $debug_env_info,
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
        "message" => "Internal Server Error: Could not connect to the database. Please check server logs or contact support.",
        "debug_info" => [
             "db_host_used" => $DB_HOST, // Be careful with exposing sensitive info, even hostnames
             "db_user_used" => $DB_USER,
             "env_debug_details" => $debug_env_info
        ]
    ]);
    exit;
}

// Set character set to utf8mb4 for proper encoding support
if (!$conn->set_charset("utf8mb4")) {
    error_log("Error loading character set utf8mb4: " . $conn->error);
}

// The $conn object is now initialized and ready for use by scripts 
// (e.g., upload.php, covers.php) that include this config.php.
