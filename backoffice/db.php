<?php
// Connect to SQLite database for covers
$db = new PDO('sqlite:' . __DIR__ . '/covers.db');
$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// Create covers table if not exists (unique game)
$db->exec("CREATE TABLE IF NOT EXISTS covers (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    game TEXT UNIQUE,
    filename TEXT NOT NULL
)");
?>
