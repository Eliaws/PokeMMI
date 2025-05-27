<?php
// Test script to verify database configuration
// This file should be removed or protected in production

header('Content-Type: application/json');

// Include the config file
include 'config.php';

// Test database connection
$response = [
    "config_test" => true,
    "timestamp" => date('Y-m-d H:i:s'),
    "tests" => []
];

// Test 1: Check if config_utils.php is loaded
$response["tests"]["config_utils_loaded"] = function_exists('sanitize_filename') && isset($game_versions);

// Test 2: Check if database connection exists
$response["tests"]["db_connection_exists"] = isset($conn) && $conn instanceof mysqli;

// Test 3: Test database connection
if (isset($conn) && $conn instanceof mysqli) {
    $response["tests"]["db_connection_active"] = !$conn->connect_error;
    
    if (!$conn->connect_error) {
        // Test a simple query
        $result = $conn->query("SELECT 1 as test");
        $response["tests"]["db_query_test"] = $result !== false;
        if ($result) {
            $result->close();
        }
    } else {
        $response["tests"]["db_connection_active"] = false;
        $response["tests"]["db_error"] = $conn->connect_error;
    }
} else {
    $response["tests"]["db_connection_active"] = false;
}

// Test 4: Check if sanitize_filename function works
if (function_exists('sanitize_filename')) {
    $test_filename = "Test File! @#$%^&*().jpg";
    $sanitized = sanitize_filename($test_filename);
    $response["tests"]["sanitize_function"] = [
        "input" => $test_filename,
        "output" => $sanitized,
        "works" => !empty($sanitized) && $sanitized !== $test_filename
    ];
}

// Test 5: Check game versions array
if (isset($game_versions)) {
    $response["tests"]["game_versions"] = [
        "loaded" => true,
        "count" => count($game_versions),
        "sample" => array_slice($game_versions, 0, 3, true)
    ];
}

echo json_encode($response, JSON_PRETTY_PRINT);

// Close connection if it exists
if (isset($conn) && $conn instanceof mysqli) {
    $conn->close();
}
?>
