<?php
$host = 'localhost';
$dbname = 'telegram_files'; // نام دیتابیست رو بذار اینجا
$user = 'root';
$pass = ''; // اگه روی XAMPP هستی معمولاً پسورد نداره

try {
    $db = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $user, $pass);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
