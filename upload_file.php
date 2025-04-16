
<?php
session_start();
require_once 'config.php';
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    die("Unauthorized");
}

$user_id = $_SESSION['user_id'];
$folder_id = $_POST['folder_id'] ?? null;

if (!isset($_FILES['file'])) {
    die("No file uploaded.");
}

$upload_dir = __DIR__ . '/uploads/';
if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);

$filename = basename($_FILES['file']['name']);
$target_path = $upload_dir . $filename;
$folder_id = isset($_POST['folder_id']) && $_POST['folder_id'] !== '' ? $_POST['folder_id'] : null;

if (move_uploaded_file($_FILES['file']['tmp_name'], $target_path)) {
    $stmt = $db->prepare("INSERT INTO files (name, path, size, user_id, folder_id) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $filename,
        'uploads/' . $filename,
        filesize($target_path),
        $user_id,
        $folder_id
    ]);

    $file_id_db = $db->lastInsertId();

    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendDocument";
    $post_fields = [
        'chat_id' => CHANNEL_USERNAME,
        'document' => new CURLFile($target_path)
    ];

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type:multipart/form-data"]);
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
    curl_setopt($ch, CURLOPT_PROXY, '127.0.0.1:10808');
    curl_setopt($ch, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);

    $output = curl_exec($ch);
$curl_error = curl_error($ch);
curl_close($ch);

// لاگ گرفتن
file_put_contents('log.txt', date("Y-m-d H:i:s") . " CURL OUTPUT: $output\nERROR: $curl_error\n", FILE_APPEND);


    $response = json_decode($output, true);
    if (isset($response['result']['document']['file_id'])) {
        $telegram_file_id = $response['result']['document']['file_id'];

        // دریافت file_path با CURL
        $getFileUrl = "https://api.telegram.org/bot" . BOT_TOKEN . "/getFile?file_id=" . $telegram_file_id;
        $ch2 = curl_init($getFileUrl);
        curl_setopt($ch2, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch2, CURLOPT_PROXY, '127.0.0.1:10808');
        curl_setopt($ch2, CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
        $tgResponseJson = curl_exec($ch2);
        curl_close($ch2);
        $tgResponse = json_decode($tgResponseJson, true);

        if (isset($tgResponse['result']['file_path'])) {
            $filePath = $tgResponse['result']['file_path'];
            $telegram_url = "https://api.telegram.org/file/bot" . BOT_TOKEN . "/" . $filePath;

            $stmt = $db->prepare("UPDATE files SET telegram_file_id = ?, telegram_url = ? WHERE id = ?");
            $stmt->execute([$telegram_file_id, $telegram_url, $file_id_db]);
        }
    }

    header("Location: index.php" . ($folder_id ? "?folder=" . $folder_id : ""));
    exit;
} else {
    die("File upload failed.");
}
?>
