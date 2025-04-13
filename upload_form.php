<?php include 'db.php'; ?>
<!DOCTYPE html>
<html lang="fa">
<head>
  <meta charset="UTF-8">
  <title>آپلود فایل به تلگرام</title>
</head>
<body>
  <h2>آپلود فایل به تلگرام</h2>
  <form action="upload_file.php" method="POST" enctype="multipart/form-data">
    <label>انتخاب فایل: <input type="file" name="file" required></label><br><br>
    <label>نام فایل: <input type="text" name="name" required></label><br><br>
    <label>انتخاب پوشه:
      <select name="folder_id">
        <option value="">ریشه</option>
        <?php
        $folders = $pdo->query("SELECT * FROM folders")->fetchAll();
        foreach ($folders as $folder) {
          echo "<option value='{$folder['id']}'>{$folder['name']}</option>";
        }
        ?>
      </select>
    </label><br><br>
    <button type="submit">ارسال</button>
  </form>
  <p><a href="index.php">بازگشت به مدیریت</a></p>
</body>
</html>
