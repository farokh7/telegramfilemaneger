
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
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'fa';

if ($name) {
  $stmt = $db->prepare("INSERT INTO folders (name, parent_id, user_id) VALUES (?, ?, ?)");
  $stmt->execute([$name, $parent_id, $user_id]);
}

header('Location: index.php' . ($parent_id ? '?folder=' . $parent_id . '&lang=' . $lang : '?lang=' . $lang));
exit;
?>
