<?php
require_once 'db.php';

// Check if game and file are set
if (!isset($_POST['game']) || !isset($_FILES['cover'])) {
    http_response_code(400);
    echo 'Jeu ou fichier manquant.';
    exit;
}

$game = $_POST['game'];
$file = $_FILES['cover'];

// Validate upload
if ($file['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo 'Erreur lors de l\'upload.';
    exit;
}

// Allowed MIME types
$allowed = ['image/jpeg', 'image/png'];
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime = finfo_file($finfo, $file['tmp_name']);
finfo_close($finfo);
if (!in_array($mime, $allowed, true)) {
    http_response_code(400);
    echo 'Format de fichier non autorisé.';
    exit;
}

// Sanitize filename
function sanitizeFilename(string $filename): string {
    // Remove accents
    $name = iconv('UTF-8', 'ASCII//TRANSLIT', $filename);
    // Replace non-alphanumeric by hyphens
    $name = preg_replace('/[^a-zA-Z0-9\.]+/', '-', $name);
    // Lowercase
    $name = strtolower($name);
    // Trim hyphens
    return trim($name, '-');
}

$originalName = pathinfo($file['name'], PATHINFO_FILENAME);
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$sanitizedBase = sanitizeFilename($originalName);
$sanitized = $sanitizedBase . '.' . strtolower($extension);

// Upload directory
$uploadDir = __DIR__ . '/uploads';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}
$destination = $uploadDir . '/' . $sanitized;

// Move file
if (!move_uploaded_file($file['tmp_name'], $destination)) {
    http_response_code(500);
    echo 'Erreur lors de la sauvegarde du fichier.';
    exit;
}

// Store in database (game unique)
$stmt = $db->prepare('INSERT OR REPLACE INTO covers (game, filename) VALUES (:game, :filename)');
$stmt->execute([
    ':game' => $game,
    ':filename' => $sanitized,
]);

// Redirect back with success message
header('Location: upload.php?success=1');
exit;
