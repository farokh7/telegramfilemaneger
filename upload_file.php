<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';
include 'config.php';

function logError($message) {
    file_put_contents('log.txt', date('Y-m-d H:i:s') . " - " . $message . "\n", FILE_APPEND);
}

$user_id = $_SESSION['user_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['file'])) {
    $file = $_FILES['file'];
    $name = $_POST['name'] ?? $file['name'];
    $folder_id = isset($_POST['folder_id']) ? intval($_POST['folder_id']) : null;

    $tempPath = $file['tmp_name'];
    $filePath = realpath($tempPath);

    if (!$filePath) {
        http_response_code(500);
        echo "مسیر فایل نامعتبر است.";
        logError("Invalid file path.");
        exit;
    }

    $post_fields = [
        'chat_id' => CHANNEL_USERNAME,
        'caption' => $name,
        'document' => new CURLFile($filePath, mime_content_type($filePath), $file['name'])
    ];

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => "https://api.telegram.org/bot" . BOT_TOKEN . "/sendDocument",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $post_fields
    ]);

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    if ($error) {
        http_response_code(500);
        echo "خطا در CURL: $error";
        logError("Curl error: $error");
        exit;
    }

    file_put_contents('log.txt', "Telegram Response: " . $response . "\n", FILE_APPEND);

    $res = json_decode($response, true);
    if ($res && isset($res['ok']) && $res['ok'] && isset($res['result']['message_id'])) {
        $file_url = "https://t.me/" . ltrim(CHANNEL_USERNAME, "@") . "/" . $res['result']['message_id'];
        $stmt = $pdo->prepare("INSERT INTO files (name, telegram_url, folder_id, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$name, $file_url, $folder_id ?: null, $user_id]);
        echo "success";
    } else {
        http_response_code(500);
        echo "خطا در ارسال فایل به تلگرام.";
        logError("API Response Error: " . json_encode($res));
    }
} else {
    http_response_code(400);
    echo "درخواست نامعتبر";
}
?>
