<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';
include 'config.php';

$current_folder_id = isset($_GET['folder']) ? intval($_GET['folder']) : null;
$current_folder = null;
if ($current_folder_id) {
  $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = ?");
  $stmt->execute([$current_folder_id]);
  $current_folder = $stmt->fetch();
}

$folders = $pdo->prepare("SELECT * FROM folders WHERE parent_id " . ($current_folder_id ? "= ?" : "IS NULL"));
if ($current_folder_id) $folders->execute([$current_folder_id]);
else $folders->execute();

$files = $pdo->prepare("SELECT * FROM files WHERE folder_id " . ($current_folder_id ? "= ?" : "IS NULL"));
if ($current_folder_id) $files->execute([$current_folder_id]);
else $files->execute();

function buildBreadcrumb($pdo, $folder_id) {
  $trail = [];
  while ($folder_id) {
    $stmt = $pdo->prepare("SELECT * FROM folders WHERE id = ?");
    $stmt->execute([$folder_id]);
    $folder = $stmt->fetch();
    if ($folder) {
      array_unshift($trail, $folder);
      $folder_id = $folder['parent_id'];
    } else {
      break;
    }
  }
  return $trail;
}

$breadcrumb = buildBreadcrumb($pdo, $current_folder_id);
?>

<!DOCTYPE html>
<html lang="fa">
<head>
  <meta charset="UTF-8">
  <title>مدیریت فایل تلگرام</title>
  <style>
    body { font-family: 'Vazir', sans-serif; direction: rtl; padding: 30px; background: #f0f0f0; }
    .folder, .file {
      padding: 10px; margin: 10px; background: white;
      border: 1px solid #ccc; border-radius: 10px;
      cursor: pointer; display: inline-block; width: 150px; text-align: center;
      transition: all 0.3s ease;
    }
    .folder:hover, .file:hover { box-shadow: 0 0 10px rgba(0,0,0,0.1); transform: translateY(-3px); }
    .breadcrumb span { cursor: pointer; color: blue; text-decoration: underline; margin-left: 5px; }
    .actions { margin: 20px 0; }
    .hidden { display: none; }
    button { padding: 5px 10px; border: none; border-radius: 5px; background: #007bff; color: white; cursor: pointer; }
    button:hover { background: #0056b3; }
    input[type="text"] { padding: 5px; border-radius: 5px; border: 1px solid #ccc; }
  </style>
</head>
<body>

<h2>📁 فایل‌منیجر تلگرام</h2>

<div class="breadcrumb">
  <span onclick="goToFolder(null)">خانه</span>
  <?php foreach ($breadcrumb as $crumb): ?>
    > <span onclick="goToFolder(<?= $crumb['id'] ?>)"><?= htmlspecialchars($crumb['name']) ?></span>
  <?php endforeach; ?>
</div>

<div class="actions">
  <button onclick="document.getElementById('newFolderForm').classList.toggle('hidden')">➕ پوشه جدید</button>
  <input type="file" id="uploadFile" name="file">
</div>

<div id="newFolderForm" class="hidden">
  <form action="create_folder.php" method="POST">
    <input type="hidden" name="parent_id" value="<?= $current_folder_id ?>">
    <input type="text" name="name" placeholder="نام پوشه جدید" required>
    <button type="submit">ایجاد</button>
    <button type="button" onclick="document.getElementById('newFolderForm').classList.add('hidden')">انصراف</button>
  </form>
</div>

<h3>📂 پوشه‌ها</h3>
<?php foreach ($folders as $folder): ?>
  <div class="folder" onclick="goToFolder(<?= $folder['id'] ?>)">
    📁 <?= htmlspecialchars($folder['name']) ?>
  </div>
<?php endforeach; ?>

<h3>📄 فایل‌ها</h3>
<?php foreach ($files as $file): ?>
  <div class="file" onclick="window.open('<?= htmlspecialchars($file['telegram_url']) ?>', '_blank')">
    📎 <?= htmlspecialchars($file['name']) ?>
  </div>
<?php endforeach; ?>

<script>
function goToFolder(id) {
  window.location.href = "index.php" + (id ? "?folder=" + id : "");
}

document.getElementById('uploadFile').addEventListener('change', function () {
  const file = this.files[0];
  if (!file) return;

  const formData = new FormData();
  formData.append('file', file);
  formData.append('name', file.name);
  formData.append('folder_id', '<?= $current_folder_id ?>');

  fetch('upload_file.php', {
    method: 'POST',
    body: formData
  }).then(res => {
    if (res.ok) location.reload();
    else alert("خطا در آپلود فایل.");
  });
});
</script>

</body>
</html>
