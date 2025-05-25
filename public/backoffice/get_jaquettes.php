<?php
require_once '../../env.php';

$conn = new mysqli(getenv('DB_HOST'), getenv('DB_USER'), getenv('DB_PASS'), getenv('DB_NAME'));
if ($conn->connect_error) die("Connexion échouée : " . $conn->connect_error);

$result = $conn->query("SELECT version_name, filename FROM jaquettes");
$jaquettes = [];

while ($row = $result->fetch_assoc()) {
    $jaquettes[$row['version_name']] = 'backoffice/uploads/' . $row['filename'];
}

header('Content-Type: application/json');
echo json_encode($jaquettes);
