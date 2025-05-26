<?php

clearstatcache(true); // Clear PHP's file stat cache

$env_php_path = __DIR__ . '/env.php';
$env_php_exists_check_for_inclusion = file_exists($env_php_path);
$env_php_readable_check_for_inclusion = is_readable($env_php_path);

// Initialize variables to null or default values
$DB_HOST = null;
$DB_USER = null;
$DB_PASS = null;
$DB_NAME = null;

// Load environment variables from env.php if it exists
if ($env_php_exists_check_for_inclusion) {
    require_once $env_php_path; // This will define $DB_HOST, $DB_USER, etc.
}

// Use the variables directly (they should be defined by env.php)
// $db_host, $db_user, $db_pass, $db_name are now expected to be set by the included env.php

// Early exit for debugging if DB_HOST is not set
// Check if the global variables from env.php are set and not empty
if (empty($DB_HOST)) { // Use the variable $DB_HOST directly
    // Attempt to set a more specific content type if headers haven't been sent
    if (!headers_sent()) {
        header('Content-Type: application/json');
    }

    $debug_payload = [
        "env_php_path_checked" => $env_php_path,
        "env_php_exists_when_checked_for_inclusion" => $env_php_exists_check_for_inclusion ? 'yes' : 'no',
        "env_php_readable_when_checked_for_inclusion" => $env_php_readable_check_for_inclusion ? 'yes' : 'no',
        // Check the global variables directly
        "db_host_variable_set_and_not_empty" => !empty($DB_HOST),
        "db_user_variable_set_and_not_empty" => !empty($DB_USER),
        "db_pass_variable_set_status" => isset($DB_PASS) ? 'set' : 'not_set', // Avoid logging actual password
        "db_name_variable_set_and_not_empty" => !empty($DB_NAME),
        "open_basedir_config" => ini_get('open_basedir') ?: 'not_set_or_empty',
        "api_directory_listing" => scandir(__DIR__) ?: 'scandir_failed_or_empty',
        "php_script_user" => get_current_user(),
        "php_version" => PHP_VERSION,
        "server_software" => isset($_SERVER['SERVER_SOFTWARE']) ? $_SERVER['SERVER_SOFTWARE'] : 'unknown'
    ];

    // Log the issue if log_message is available (it might not be if upload.php hasn't defined it yet)
    if (function_exists('log_message')) { 
        log_message("CRITICAL: DB_HOST is empty. env.php not loaded or variables not set. Debug: " . json_encode($debug_payload));
    }
    
    // Output JSON and exit
    echo json_encode([
        "success" => false, 
        "message" => "Configuration error: DB_HOST is not set. env.php might not be loaded or variables are missing.",
        "debug_info" => $debug_payload
    ]);
    exit;
}

// Use the global variables for the connection
$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Function to sanitize filenames
function sanitize_filename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9_\-\s\.]/', '', $filename); // Remove special chars except _ - . and space
    $filename = str_replace(' ', '-', $filename); // Replace spaces with hyphens
    $filename = strtolower($filename); // Convert to lowercase
    // Remove accents (simplified version, might need a more robust solution for all cases)
    $unwanted_array = ['À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                       'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
                       'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
                       'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
                       'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
                       'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'þ'=>'b',
                       'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r'];
    $filename = strtr($filename, $unwanted_array);
    return $filename;
}

// Game versions mapping (from your upload.js)
$game_versions = [
    "red" => "Pokémon Rouge",
    "blue" => "Pokémon Bleue",
    "yellow" => "Pokémon Jaune",
    "gold" => "Pokémon Or",
    "silver" => "Pokémon Argent",
    "crystal" => "Pokémon Crystal",
    "sapphire" => "Pokémon Saphir",
    "ruby" => "Pokémon Rubis",
    "emerald" => "Pokémon Émeraude",
    "firered" => "Pokémon Rouge feu",
    "leafgreen" => "Pokémon Vert feuille",
    "diamond" => "Pokémon Diamant",
    "pearl" => "Pokémon Perle",
    "platinum" => "Pokémon Platine",
    "heartgold" => "Pokémon Or HeartGold",
    "soulsilver" => "Pokémon Argent SoulSilver",
    "white" => "Pokémon Blanche",
    "black" => "Pokémon Noire",
    "black-2" => "Pokémon Noire 2",
    "white-2" => "Pokémon Blanche 2",
    "x" => "Pokémon X",
    "y" => "Pokémon Y",
    "omega-ruby" => "Pokémon Rubis Oméga",
    "ultra-sun" => "Pokémon Ultra-Soleil",
    "sun" => "Pokémon Soleil",
    "moon" => "Pokémon Lune",
    "ultra-moon" => "Pokémon Ultra-Lune",
    "alpha-sapphire" => "Pokémon Saphir Alpha",
    "sword" => "Pokémon Épée",
    "shield" => "Pokémon Bouclier",
    "violet" => "Pokémon Violet",
    "scarlet" => "Pokémon Écarlate",
    "lets-go-eevee" => "Pokémon Let's Go, Évoli",
    "lets-go-pikachu" => "Pokémon Let's Go, Pikachu",
    "legends-arceus" => "Légendes Pokémon : Arceus",
];

?>
