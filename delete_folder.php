<?php
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$folder_id = intval($data['id']);

// حذف همه فایل‌های داخل پوشه
$stmt = $db->prepare("DELETE FROM files WHERE folder_id = ?");
$stmt->execute([$folder_id]);

// حذف پوشه‌های زیرمجموعه به‌صورت بازگشتی
function deleteSubfolders($db, $parent_id) {
  $stmt = $db->prepare("SELECT id FROM folders WHERE parent_id = ?");
  $stmt->execute([$parent_id]);
  $subfolders = $stmt->fetchAll();

  foreach ($subfolders as $subfolder) {
    deleteSubfolders($db, $subfolder['id']);
    $stmt = $db->prepare("DELETE FROM folders WHERE id = ?");
    $stmt->execute([$subfolder['id']]);
  }
}

deleteSubfolders($db, $folder_id);

// حذف خود پوشه
$stmt = $db->prepare("DELETE FROM folders WHERE id = ?");
$stmt->execute([$folder_id]);

header('Content-Type: application/json');
http_response_code(200);
echo json_encode(['status' => 'success']);
?>
