
<?php
require_once 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['id'])) {
  http_response_code(400);
  exit;
}

$id = intval($data['id']);

// گرفتن مسیر فایل
$stmt = $db->prepare("SELECT path FROM files WHERE id = ?");
$stmt->execute([$id]);
$file = $stmt->fetch();

if ($file && file_exists($file['path'])) {
  unlink($file['path']); // حذف فایل از سرور
}

// حذف از دیتابیس
$stmt = $db->prepare("DELETE FROM files WHERE id = ?");
$stmt->execute([$id]);

http_response_code(200);
echo json_encode(['status' => 'deleted']);
?>
