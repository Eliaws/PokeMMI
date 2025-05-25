<?php

$host = getenv('VITE_DB_HOST');
$user = getenv('VITE_DB_USER');
$pass = getenv('VITE_DB_PASS');
$db   = getenv('VITE_DB_NAME');

// Connexion sans base sélectionnée pour créer la base si elle n'existe pas
$conn = new mysqli($host, $user, $pass);
if ($conn->connect_error) die("Connexion échouée : " . $conn->connect_error);

$conn->query("CREATE DATABASE IF NOT EXISTS `$db` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

$conn->select_db($db);

$conn->query("
CREATE TABLE IF NOT EXISTS jaquettes (
  id INT AUTO_INCREMENT PRIMARY KEY,
  version_name VARCHAR(50) NOT NULL,
  filename VARCHAR(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
");

//Sanitization
function sanitize_filename($filename) {
    $filename = iconv('UTF-8', 'ASCII//TRANSLIT', $filename);
    $filename = preg_replace('/[^a-zA-Z0-9]/', '-', $filename);
    return strtolower(trim($filename, '-')) . '.' . pathinfo($filename, PATHINFO_EXTENSION);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['jaquette'], $_POST['version'])) {
    $version = $_POST['version'];
    $file = $_FILES['jaquette'];

    $targetDir = __DIR__ . '/uploads/';
    if (!is_dir($targetDir)) mkdir($targetDir, 0777, true);

    $sanitized = sanitize_filename($file['name']);
    $targetFile = $targetDir . $sanitized;

    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO jaquettes (version_name, filename) VALUES (?, ?)");
        $stmt->bind_param("ss", $version, $sanitized);
        $stmt->execute();
        echo "Upload réussi.";
    } else {
        echo "Erreur lors de l'upload.";
    }
} else {
    echo "Requête invalide.";
}
