<?php
header('Content-Type: application/json');

// Vérification simple de la méthode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["success" => false, "message" => "Méthode non autorisée"]);
    exit;
}

// Inclusion des fichiers de configuration
require_once 'config.php';
require_once 'config_utils.php';

// Vérification de la connexion DB
if (!$conn || $conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Erreur de connexion à la base de données"]);
    exit;
}

// Création de la table si nécessaire
$createTableSql = "CREATE TABLE IF NOT EXISTS game_covers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_version_key VARCHAR(255) NOT NULL,
    game_version_name VARCHAR(255) NOT NULL,
    image_path VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";
$conn->query($createTableSql);

// Vérification des données POST
if (!isset($_FILES['jaquette']) || !isset($_POST['version'])) {
    echo json_encode(["success" => false, "message" => "Fichier ou version manquant"]);
    exit;
}

$file = $_FILES['jaquette'];
$game_version_key = $_POST['version'];

// Vérification de l'erreur d'upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "message" => "Erreur lors de l'upload du fichier"]);
    exit;
}

// Vérification de la version du jeu
if (!in_array($game_version_key, $game_versions_keys)) {
    echo json_encode(["success" => false, "message" => "Version de jeu invalide"]);
    exit;
}

$game_version_name = $game_versions[$game_version_key];

// Traitement du fichier
$file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

if (!in_array($file_extension, $allowed_extensions)) {
    echo json_encode(["success" => false, "message" => "Type de fichier non autorisé"]);
    exit;
}

// Création du nom de fichier sécurisé
$sanitized_filename = sanitize_filename(pathinfo($file['name'], PATHINFO_FILENAME)) . '.' . $file_extension;

// Dossier d'upload
$upload_dir = __DIR__ . '/../uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

$upload_path = $upload_dir . $sanitized_filename;

// Vérification si le fichier existe déjà
if (file_exists($upload_path)) {
    echo json_encode(["success" => false, "message" => "Un fichier avec ce nom existe déjà"]);
    exit;
}

// Upload du fichier
if (move_uploaded_file($file['tmp_name'], $upload_path)) {
    // Chemin relatif pour la base de données
    $relative_path = 'backoffice/uploads/' . $sanitized_filename;
    
    // Sauvegarde en base de données
    $stmt = $conn->prepare("INSERT INTO game_covers (game_version_key, game_version_name, image_path) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $game_version_key, $game_version_name, $relative_path);
    
    if ($stmt->execute()) {
        echo json_encode([
            "success" => true, 
            "message" => "Fichier uploadé avec succès",
            "filepath" => $relative_path
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Erreur lors de la sauvegarde en base"]);
    }
    $stmt->close();
} else {
    echo json_encode(["success" => false, "message" => "Erreur lors du déplacement du fichier"]);
}

$conn->close();