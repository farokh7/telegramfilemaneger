<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

include 'db.php';

$user_id = $_SESSION['user_id'];
$parent_id = isset($_POST['parent_id']) && $_POST['parent_id'] !== '' ? intval($_POST['parent_id']) : null;
$name = trim($_POST['name']);
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'fa'; // Get the language parameter

if ($name) {
  $stmt = $pdo->prepare("INSERT INTO folders (name, parent_id, user_id) VALUES (?, ?, ?)");
  $stmt->execute([$name, $parent_id, $user_id]);
}

// Redirect back to the index page with the correct language and folder
header('Location: index.php' . ($parent_id ? '?folder=' . $parent_id : '') . '&lang=' . $lang);
exit;
?>
