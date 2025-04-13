<?php
include 'db.php';

$name = $_POST['name'];
$url = $_POST['telegram_url'];
$folder_id = !empty($_POST['folder_id']) ? $_POST['folder_id'] : null;

$stmt = $pdo->prepare("INSERT INTO files (name, telegram_url, folder_id) VALUES (?, ?, ?)");
$stmt->execute([$name, $url, $folder_id]);
