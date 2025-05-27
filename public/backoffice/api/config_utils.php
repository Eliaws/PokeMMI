<?php
// Utility functions and configurations for the backoffice API

if (!function_exists('sanitize_filename')) {
    // Function to sanitize filenames
    function sanitize_filename($basename) {
        // Decode URL-encoded characters
        $basename = urldecode($basename);

        // Remove characters that are not letters (unicode), numbers, hyphens, underscores, or spaces.
        $basename = preg_replace('/[^\pL\pN\s_-]+/u', '', $basename);

        // Replace spaces and sequences of hyphens/underscores with a single hyphen
        $basename = preg_replace('/\s+/', '-', $basename);
        $basename = preg_replace('/[-_]+/', '-', $basename);

        // Convert to lowercase
        $basename = strtolower($basename);

        // Trim hyphens from the beginning and end
        $basename = trim($basename, '-');

        // Prevent overly long filenames (e.g., 200 chars for the base name)
        $basename = substr($basename, 0, 200);

        if (empty($basename)) {
            return 'default-filename'; // Fallback for empty results
        }

        return $basename;
    }
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

$game_versions_keys = [
    'red',
    'blue',
    'yellow',
    'gold',
    'silver',
    'crystal',
    'sapphire',
    'ruby',
    'emerald',
    'firered',
    'leafgreen',
    'diamond',
    'pearl',
    'platinum',
    'heartgold',
    'soulsilver',
    'white',
    'black',
    'black-2',
    'white-2',
    'x',
    'y',
    'omega-ruby',
    'ultra-sun',
    'sun',
    'moon',
    'ultra-moon',
    'alpha-sapphire',
    'sword',
    'shield',
    'violet',
    'scarlet',
    'lets-go-eevee',
    'lets-go-pikachu',
    'legends-arceus'
];

// It's a common practice to omit the closing PHP tag ?> at the end of files that only contain PHP code.
