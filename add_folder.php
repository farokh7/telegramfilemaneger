<?php
include 'db.php';

$name = $_POST['name'];
$parent_id = !empty($_POST['folder_id']) ? $_POST['folder_id'] : null;

$stmt = $pdo->prepare("INSERT INTO folders (name, parent_id) VALUES (?, ?)");
$stmt->execute([$name, $parent_id]);
