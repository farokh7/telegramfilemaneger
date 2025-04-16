<?php
require_once 'db.php';

$data = json_decode(file_get_contents("php://input"), true);
$id = intval($data['id']);
$newName = trim($data['name']);

if ($id && $newName) {
  $stmt = $db->prepare("UPDATE files SET name = ? WHERE id = ?");
  $stmt->execute([$newName, $id]);
  http_response_code(200);
} else {
  http_response_code(400);
}
?>
