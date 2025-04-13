<?php
include 'db.php';

$folder_id = isset($_GET['folder_id']) && $_GET['folder_id'] !== '' ? $_GET['folder_id'] : null;

// breadcrumb
function buildBreadcrumb($id) {
  global $pdo;
  $breadcrumb = [];
  while ($id) {
    $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = ?");
    $stmt->execute([$id]);
    if ($folder = $stmt->fetch()) {
      array_unshift($breadcrumb, $folder['name']);
      $id = $folder['parent_id'];
    } else break;
  }
  return 'خانه' . (count($breadcrumb) ? ' > ' . implode(' > ', $breadcrumb) : '');
}

$folders = $pdo->prepare("SELECT * FROM folders WHERE parent_id " . ($folder_id ? "= ?" : "IS NULL"));
$folders->execute($folder_id ? [$folder_id] : []);
$folders = $folders->fetchAll();

$files = $pdo->prepare("SELECT * FROM files WHERE folder_id " . ($folder_id ? "= ?" : "IS NULL"));
$files->execute($folder_id ? [$folder_id] : []);
$files = $files->fetchAll();

echo json_encode([
  "breadcrumb" => buildBreadcrumb($folder_id),
  "folders" => $folders,
  "files" => $files
]);
