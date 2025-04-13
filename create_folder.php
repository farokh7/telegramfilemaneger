<?php
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $parent_id = $_POST['parent_id'] ?? null;

    if ($name) {
        $stmt = $pdo->prepare("INSERT INTO folders (name, parent_id) VALUES (?, ?)");
        $stmt->execute([$name, $parent_id ?: null]);
    }
}

header("Location: index.php" . ($parent_id ? "?folder=$parent_id" : ""));
exit;
