<?php
// filepath: c:\Users\eliax\Desktop\repo_github\PokeMMI\public\backoffice\api\config.php

// Load environment variables from env.php if it exists
if (file_exists(__DIR__ . '/env.php')) {
    require_once __DIR__ . '/env.php';
}

$db_host = getenv('DB_HOST');
$db_user = getenv('DB_USER');
$db_pass = getenv('DB_PASS');
$db_name = getenv('DB_NAME');

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);

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
