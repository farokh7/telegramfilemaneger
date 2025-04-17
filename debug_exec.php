<?php
$python_path = 'C:\\Users\\Almahdi Laptop\\AppData\\Local\\Programs\\Python\\Python313\\python.exe';
$script_path = __DIR__ . '\\send_to_telegram.py';
$test_file = __DIR__ . '\\uploads\\test.jpg';

if (!file_exists($test_file)) {
    die('❌ فایل تست پیدا نشد: uploads/test.jpg');
}
if (!file_exists($script_path)) {
    die('❌ فایل اسکریپت Python پیدا نشد: send_to_telegram.py');
}

$command = "\"$python_path\" \"$script_path\" \"$test_file\" 2>&1";
$output = [];
exec($command, $output);
file_put_contents("log.txt", implode(PHP_EOL, $output), FILE_APPEND);

echo "<h3>✅ اجرای کامل شد. خروجی:</h3><pre>" . implode(PHP_EOL, $output) . "</pre>";
?>
