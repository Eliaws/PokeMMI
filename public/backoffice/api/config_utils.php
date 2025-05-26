<?php
// Utility functions and configurations for the backoffice API

// Function to sanitize filenames
function sanitize_filename($filename) {
    $filename = preg_replace('/[^a-zA-Z0-9_\-\s\.]/', '', $filename); // Remove special chars except _ - . and space
    $filename = str_replace(' ', '-', $filename); // Replace spaces with hyphens
    $filename = strtolower($filename); // Convert to lowercase
    // Remove accents
    $unwanted_array = [
        'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O',
        'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U', 'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss',
        'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c', 'è'=>'e', 'é'=>'e',
        'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o',
        'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 'þ'=>'b',
        'ÿ'=>'y', 'Ŕ'=>'R', 'ŕ'=>'r'
    ];
    $filename = strtr($filename, $unwanted_array);
    return $filename;
}

// Game versions mapping
$game_versions = [
    "red" => "Pokémon Rouge", "blue" => "Pokémon Bleue", "yellow" => "Pokémon Jaune",
    "gold" => "Pokémon Or", "silver" => "Pokémon Argent", "crystal" => "Pokémon Crystal",
    "sapphire" => "Pokémon Saphir", "ruby" => "Pokémon Rubis", "emerald" => "Pokémon Émeraude",
    "firered" => "Pokémon Rouge feu", "leafgreen" => "Pokémon Vert feuille",
    "diamond" => "Pokémon Diamant", "pearl" => "Pokémon Perle", "platinum" => "Pokémon Platine",
    "heartgold" => "Pokémon Or HeartGold", "soulsilver" => "Pokémon Argent SoulSilver",
    "white" => "Pokémon Blanche", "black" => "Pokémon Noire",
    "black-2" => "Pokémon Noire 2", "white-2" => "Pokémon Blanche 2",
    "x" => "Pokémon X", "y" => "Pokémon Y"
    // Add other versions if necessary
];

// It's a common practice to omit the closing PHP tag ?> at the end of files that only contain PHP code.
