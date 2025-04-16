
<?php
session_start();
require_once 'config.php';
require_once 'db.php';

// بررسی دسترسی ادمین
$stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();
if (!$user || $user['is_admin'] != 1) {
    die("شما دسترسی به این بخش ندارید.");
}

// تعداد کاربران
$totalUsers = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();

// افزودن کاربر
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['username'], $_POST['password'])) {
    $username = trim($_POST['username']);
    $password = password_hash($_POST['password'], PASSWORD_BCRYPT);

    $stmt = $db->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password]);

    header("Location: user_admin.php");
    exit;
}

// حذف کاربر
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: user_admin.php");
    exit;
}

// گرفتن لیست کاربران و آمار هرکدوم
$stmt = $db->query("SELECT * FROM users ORDER BY id DESC");
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($users as &$u) {
    $stmt = $db->prepare("SELECT COUNT(*) FROM files WHERE user_id = ?");
    $stmt->execute([$u['id']]);
    $u['file_count'] = $stmt->fetchColumn();

    $stmt = $db->prepare("SELECT COUNT(*) FROM folders WHERE user_id = ?");
    $stmt->execute([$u['id']]);
    $u['folder_count'] = $stmt->fetchColumn();
}

$bg_images = glob('bg/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
$random_bg = $bg_images[array_rand($bg_images)];
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>مدیریت کاربران</title>
    <style>
        body {
            font-family: Tahoma;
            background: url('<?= $random_bg ?>') no-repeat center center fixed;
            background-size: cover;
            padding: 30px;
            direction: rtl;
            color: #333;
        }
        .container {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 10px;
            max-width: 900px;
            margin: auto;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
        h2 { text-align: center; }
        table { width: 100%; margin-top: 20px; border-collapse: collapse; }
        th, td { border: 1px solid #ccc; padding: 10px; text-align: center; }
        th { background: #eee; }
        form { margin-top: 30px; display: flex; flex-wrap: wrap; justify-content: space-between; gap: 10px; }
        input { padding: 10px; flex: 1 1 45%; }
        button { padding: 10px 20px; background: #28a745; color: #fff; border: none; border-radius: 5px; cursor: pointer; }
        a.delete { color: red; text-decoration: none; }
        .top-bar {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            align-items: center;
        }
        .top-bar .meta {
            font-weight: bold;
            color: #555;
        }
        .top-bar a {
            padding: 8px 16px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 6px;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="top-bar">
        <div class="meta">👥 تعداد کل کاربران: <?= $totalUsers ?></div>
        <a href="logout.php">خروج</a>
    </div>

    <h2>مدیریت کاربران</h2>

    <table>
        <tr>
            <th>شناسه</th>
            <th>نام کاربری</th>
            <th>ادمین؟</th>
            <th>تعداد فایل</th>
            <th>تعداد پوشه</th>
            <th>عملیات</th>
        </tr>
        <?php foreach ($users as $user): ?>
        <tr>
            <td><?= $user['id'] ?></td>
            <td><?= htmlspecialchars($user['username']) ?></td>
            <td><?= $user['is_admin'] ? '✅' : '❌' ?></td>
            <td><?= $user['file_count'] ?></td>
            <td><?= $user['folder_count'] ?></td>
            <td><a href="?delete=<?= $user['id'] ?>" class="delete" onclick="return confirm('آیا حذف شود؟')">حذف</a></td>
        </tr>
        <?php endforeach; ?>
    </table>

    <form method="POST">
        <input type="text" name="username" placeholder="نام کاربری جدید" required>
        <input type="password" name="password" placeholder="رمز عبور" required>
        <button type="submit">افزودن کاربر</button>
    </form>
</div>
</body>
</html>
