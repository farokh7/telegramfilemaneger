<?php
require_once 'config.php';
require_once 'db.php';

if (!isset($_GET['token']) || empty($_GET['token'])) {
    http_response_code(400);
    echo "Invalid request.";
    exit;
}

$token = $_GET['token'];
$stmt = $db->prepare("SELECT telegram_url FROM files WHERE token = ?");
$stmt->execute([$token]);
$file = $stmt->fetch();

if ($file && !empty($file['telegram_url'])) {
    header("Location: " . $file['telegram_url']);
    exit;
} else {
    http_response_code(404);
    echo "File not found or link expired.";
    exit;
}
?>