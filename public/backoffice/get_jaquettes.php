<?php

$conn = new mysqli(getenv('VITE_DB_HOST'), getenv('VITE_DB_USER'), getenv('VITE_DB_PASS'), getenv('VITE_DB_NAME'));
if ($conn->connect_error) die("Connexion échouée : " . $conn->connect_error);

$result = $conn->query("SELECT version_name, filename FROM jaquettes");
$jaquettes = [];

while ($row = $result->fetch_assoc()) {
    $jaquettes[$row['version_name']] = 'backoffice/uploads/' . $row['filename'];
}

header('Content-Type: application/json');
echo json_encode($jaquettes);
