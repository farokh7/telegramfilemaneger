<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

include 'db.php';
include 'config.php';

$user_id = $_SESSION['user_id'];
$folder_id = isset($_POST['folder_id']) && $_POST['folder_id'] !== '' ? intval($_POST['folder_id']) : null;
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'fa';

if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
  $file_name = $_FILES['file']['name'];
  $file_tmp = $_FILES['file']['tmp_name'];
  $file_size = $_FILES['file']['size'];

  // Send the file directly to Telegram
  $telegram_url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendDocument";
  $post_fields = [
    'chat_id' => CHANNEL_USERNAME,
    'document' => new CURLFile(realpath($file_tmp)), // Use the temporary file directly
    'caption' => "📂 File: $file_name\n📦 Size: " . round($file_size / 1024, 2) . " KB"
  ];

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $telegram_url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  $response = curl_exec($ch);
  curl_close($ch);

  // Optional: Log the response for debugging
  // file_put_contents('telegram_log.txt', $response, FILE_APPEND);
}

// Redirect back to the index page with the correct language and folder
header('Location: index.php' . ($folder_id ? '?folder=' . $folder_id . '&lang=' . $lang : '?lang=' . $lang));
exit;
?>
