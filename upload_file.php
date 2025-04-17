<?php
session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$folder_id = isset($_POST['folder_id']) && $_POST['folder_id'] !== '' ? intval($_POST['folder_id']) : null;

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    die("No file uploaded or upload error.");
}

$tmp_file_path = $_FILES['file']['tmp_name'];
$filename = basename($_FILES['file']['name']);
$file_size = $_FILES['file']['size'];

file_put_contents("log.txt", date("Y-m-d H:i:s") . " - START upload of $filename" . PHP_EOL, FILE_APPEND);

// حذف فایل خروجی قدیمی
$result_file = __DIR__ . DIRECTORY_SEPARATOR . 'telegram_result.json';
if (file_exists($result_file)) unlink($result_file);

// اجرای اسکریپت پایتون با مسیر کامل
$python_path = 'C:\\Users\\Almahdi Laptop\\AppData\\Local\\Programs\\Python\\Python313\\python.exe';
$script_path = __DIR__ . DIRECTORY_SEPARATOR . 'send_to_telegram.py';

$command = '"' . $python_path . '" "' . $script_path . '" "' . $tmp_file_path . '" 2>&1';
$output = [];
exec($command, $output);
file_put_contents("log.txt", date("Y-m-d H:i:s") . ' - PYTHON OUTPUT: ' . implode(PHP_EOL, $output) . PHP_EOL, FILE_APPEND);

// ذخیره اولیه در دیتابیس
$stmt = $db->prepare("INSERT INTO files (name, path, size, user_id, folder_id) VALUES (?, ?, ?, ?, ?)");
$stmt->execute([$filename, '', $file_size, $user_id, $folder_id]);
$file_id_db = $db->lastInsertId();

// بررسی خروجی JSON
if (file_exists($result_file)) {
    $raw = file_get_contents($result_file);
    file_put_contents("log.txt", "telegram_result.json: " . $raw . PHP_EOL, FILE_APPEND);
    $json = json_decode($raw, true);

    if ($json && isset($json['telegram_file_id'], $json['telegram_url'])) {
        $token = bin2hex(random_bytes(16));
        $stmt = $db->prepare("UPDATE files SET telegram_file_id = ?, telegram_url = ?, token = ? WHERE id = ?");
        $stmt->execute([$json['telegram_file_id'], $json['telegram_url'], $token, $file_id_db]);
    } elseif (isset($json['error'])) {
        file_put_contents("log.txt", "⛔ Telegram Error: " . $json['error'] . PHP_EOL, FILE_APPEND);
    } else {
        file_put_contents("log.txt", "⚠️ JSON parsed but missing expected fields." . PHP_EOL, FILE_APPEND);
    }
} else {
    file_put_contents("log.txt", "❌ telegram_result.json not found after Python execution." . PHP_EOL, FILE_APPEND);
}

// ریدایرکت نهایی
header("Location: index.php" . ($folder_id ? "?folder=" . $folder_id : ""));
exit;
?>