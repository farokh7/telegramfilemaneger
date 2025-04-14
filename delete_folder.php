<?php
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$folder_id = intval($data['id']);

// Delete all files in the folder
$stmt = $pdo->prepare("DELETE FROM files WHERE folder_id = ?");
$stmt->execute([$folder_id]);

// Delete all subfolders recursively
function deleteSubfolders($pdo, $parent_id) {
  $stmt = $pdo->prepare("SELECT id FROM folders WHERE parent_id = ?");
  $stmt->execute([$parent_id]);
  $subfolders = $stmt->fetchAll();

  foreach ($subfolders as $subfolder) {
    deleteSubfolders($pdo, $subfolder['id']);
    $stmt = $pdo->prepare("DELETE FROM folders WHERE id = ?");
    $stmt->execute([$subfolder['id']]);
  }
}

deleteSubfolders($pdo, $folder_id);

// Delete the folder itself
$stmt = $pdo->prepare("DELETE FROM folders WHERE id = ?");
$stmt->execute([$folder_id]);

header('Content-Type: application/json'); // Ensure correct Content-Type
http_response_code(200);
echo json_encode(['status' => 'success']);
?>
