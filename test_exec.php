<?php
$file_path = __DIR__ . '/uploads/p.jpg';
$python_script = __DIR__ . '/send_to_telegram.py';

if (!file_exists($file_path)) {
    die("فایل تست test.jpg در پوشه uploads وجود ندارد.");
}

if (!file_exists($python_script)) {
    die("فایل send_to_telegram.py پیدا نشد!");
}

$command = 'python "' . $python_script . '" "' . $file_path . '" 2>&1';
$output = [];
exec($command, $output);
echo "<pre>" . implode(PHP_EOL, $output) . "</pre>";
?>
