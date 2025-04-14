<?php
include 'db.php';

$data = json_decode(file_get_contents('php://input'), true);
$folder_id = intval($data['id']);
$new_name = trim($data['name']);

if ($new_name) {
  $stmt = $pdo->prepare("UPDATE folders SET name = ? WHERE id = ?");
  $stmt->execute([$new_name, $folder_id]);
  http_response_code(200);
  echo json_encode(['status' => 'success']);
} else {
  http_response_code(400);
  echo json_encode(['status' => 'error', 'message' => 'Invalid name']);
}
?>
