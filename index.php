<?php
session_start();
if (!isset($_SESSION['user_id'])) {
  header('Location: login.php');
  exit;
}

$user_id = $_SESSION['user_id'];

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

include 'db.php';
include 'config.php';

// Language setup
$lang = isset($_GET['lang']) ? $_GET['lang'] : 'fa';
$_SESSION['lang'] = $lang;
$translations = [
  'fa' => [
    'home' => 'خانه',
    'new_folder' => '➕ پوشه جدید',
    'upload_file' => 'آپلود فایل',
    'logout' => 'خروج از سیستم',
    'create' => 'ایجاد',
    'cancel' => 'انصراف',
    'folders' => '📂 پوشه‌ها',
    'files' => '📄 فایل‌ها',
    'rename' => 'تغییر نام',
    'delete' => 'حذف',
    'edit' => 'ویرایش',
    'delete_file' => 'حذف فایل',
    'breadcrumb_separator' => '>',
  ],
  'en' => [
    'home' => 'Home',
    'new_folder' => '➕ New Folder',
    'upload_file' => 'Upload File',
    'logout' => 'Logout',
    'create' => 'Create',
    'cancel' => 'Cancel',
    'folders' => '📂 Folders',
    'files' => '📄 Files',
    'rename' => 'Rename',
    'delete' => 'Delete',
    'edit' => 'Edit',
    'delete_file' => 'Delete File',
    'breadcrumb_separator' => '>',
  ],
  'fr' => [
    'home' => 'Accueil',
    'new_folder' => '➕ Nouveau Dossier',
    'upload_file' => 'Télécharger un fichier',
    'logout' => 'Déconnexion',
    'create' => 'Créer',
    'cancel' => 'Annuler',
    'folders' => '📂 Dossiers',
    'files' => '📄 Fichiers',
    'rename' => 'Renommer',
    'delete' => 'Supprimer',
    'edit' => 'Modifier',
    'delete_file' => 'Supprimer le fichier',
    'breadcrumb_separator' => '>',
  ],
  'ar' => [
    'home' => 'الرئيسية',
    'new_folder' => '➕ مجلد جديد',
    'upload_file' => 'رفع ملف',
    'logout' => 'تسجيل الخروج',
    'create' => 'إنشاء',
    'cancel' => 'إلغاء',
    'folders' => '📂 المجلدات',
    'files' => '📄 الملفات',
    'rename' => 'إعادة تسمية',
    'delete' => 'حذف',
    'edit' => 'تعديل',
    'delete_file' => 'حذف الملف',
    'breadcrumb_separator' => '>',
  ],
];
$t = $translations[$lang];

// Get a random background image from the "bg" folder
$bg_images = glob('bg/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
$random_bg = $bg_images[array_rand($bg_images)];

$current_folder_id = isset($_GET['folder']) ? intval($_GET['folder']) : null;
$current_folder = null;
if ($current_folder_id) {
  $stmt = $db->prepare("SELECT * FROM folders WHERE id = ? AND user_id = ?");
  $stmt->execute([$current_folder_id, $user_id]);
  $current_folder = $stmt->fetch();
}

$folders = $db->prepare("SELECT * FROM folders WHERE parent_id " . ($current_folder_id ? "= ?" : "IS NULL") . " AND user_id = ?");
if ($current_folder_id) $folders->execute([$current_folder_id, $user_id]);
else $folders->execute([$user_id]);

$files = $db->prepare("SELECT * FROM files WHERE folder_id " . ($current_folder_id ? "= ?" : "IS NULL") . " AND user_id = ?");
if ($current_folder_id) $files->execute([$current_folder_id, $user_id]);
else $files->execute([$user_id]);

function buildBreadcrumb($db, $folder_id, $user_id) {
  $trail = [];
  while ($folder_id) {
    $stmt = $db->prepare("SELECT * FROM folders WHERE id = ? AND user_id = ?");
    $stmt->execute([$folder_id, $user_id]);
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

$breadcrumb = buildBreadcrumb($db, $current_folder_id, $user_id);
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8">
  <title><?= $t['home'] ?></title>
  <style>
    body {
      font-family: 'Vazir', sans-serif;
      direction: <?= $lang === 'ar' || $lang === 'fa' ? 'rtl' : 'ltr' ?>;
      margin: 0;
      padding: 0;
      background: url('<?= $random_bg ?>') no-repeat center center fixed;
      background-size: cover;
      color: #333;
    }
    .container {
      background: rgba(255, 255, 255, 0.9);
      margin: 30px auto;
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      max-width: 1200px;
    }
    .language-selector {
      text-align: <?= $lang === 'fa' ? 'left' : 'right' ?>;
      margin-bottom: 20px;
    }
    .language-selector a {
      margin: 0 10px;
      text-decoration: none;
      color: #007bff;
      font-weight: bold;
    }
    .language-selector a:hover {
      text-decoration: underline;
      color: #0056b3;
    }
    .folder, .file {
      padding: 20px;
      margin: 15px;
      background: linear-gradient(145deg, #ffffff, #f0f0f0);
      border: 1px solid #ddd;
      border-radius: 15px;
      cursor: pointer;
      display: inline-block;
      width: 200px;
      text-align: center;
      transition: all 0.3s ease;
      position: relative;
      box-shadow: 4px 4px 8px rgba(0, 0, 0, 0.1), -4px -4px 8px rgba(255, 255, 255, 0.7);
    }
    .folder:hover, .file:hover {
      box-shadow: 6px 6px 12px rgba(0, 0, 0, 0.2), -6px -6px 12px rgba(255, 255, 255, 0.8);
      transform: translateY(-5px);
    }
    .folder .icon {
      font-size: 50px;
      color: #ffc107;
      margin-bottom: 10px;
    }
    .folder .name {
      font-size: 16px;
      font-weight: bold;
      color: #333;
    }
    .folder .actions {
      display: none; /* Hidden by default */
      margin-top: 10px;
      justify-content: center;
      gap: 5px;
    }
    .folder:hover .actions {
      display: flex; /* Show on hover */
    }
    .folder .actions button.delete {
      background: #dc3545; /* Red background for delete button */
      color: white;
    }
    .folder .actions button.delete:hover {
      background: #c82333; /* Darker red on hover */
    }
    .breadcrumb {
      font-size: 18px;
      font-weight: bold;
      margin-bottom: 20px;
      color: #333;
    }
    .breadcrumb a, .breadcrumb span {
      cursor: pointer;
      color: #007bff;
      text-decoration: none;
      margin-left: 5px;
    }
    .breadcrumb a:hover, .breadcrumb span:hover {
      color: #0056b3;
      text-decoration: underline;
    }
    .breadcrumb > a:first-child {
      margin-left: 0;
    }
    .breadcrumb > span::before {
      content: "<?= $t['breadcrumb_separator'] ?>";
      margin: 0 5px;
      color: #666;
    }
    .breadcrumb > span:first-child::before {
      content: "";
    }
    .actions {
      margin: 20px 0;
      display: flex;
      gap: 10px;
      align-items: center;
    }
    .actions button {
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      background: #28a745;
      color: white;
      font-size: 14px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .actions button:hover {
      background: #218838;
    }
    #newFolderForm {
      margin-top: 20px;
      display: none; /* Hidden by default */
    }
    #newFolderForm.visible {
      display: block; /* Show when toggled */
    }
    #newFolderForm input[type="text"] {
      padding: 10px;
      border-radius: 5px;
      border: 1px solid #ddd;
      width: 200px;
      margin-right: 10px;
    }
    #newFolderForm button {
      padding: 10px 15px;
      border: none;
      border-radius: 5px;
      background: #007bff;
      color: white;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    #newFolderForm button:hover {
      background: #0056b3;
    }
    #newFolderForm button.cancel {
      background: #dc3545;
    }
    #newFolderForm button.cancel:hover {
      background: #c82333;
    }
  </style>
</head>
<body>
  <div class="container">
    <div class="language-selector">
      <a href="?lang=fa">فارسی</a> | <a href="?lang=en">English</a> | <a href="?lang=fr">Français</a> | <a href="?lang=ar">العربية</a>
    </div>

    <h2><?= $t['home'] ?></h2>

    <div class="breadcrumb">
      <a href="index.php?lang=<?= $lang ?>"><?= $t['home'] ?></a>
      <?php foreach ($breadcrumb as $crumb): ?>
        <span onclick="goToFolder(<?= $crumb['id'] ?>)"><?= htmlspecialchars($crumb['name']) ?></span>
      <?php endforeach; ?>
    </div>

    <div class="actions">
      <button id="toggleNewFolderForm"><?= $t['new_folder'] ?></button>
      <form action="upload_file.php?lang=<?= $lang ?>" method="POST" enctype="multipart/form-data" style="display: inline;">
        <input type="hidden" name="folder_id" value="<?= $current_folder_id ?>">
        <label for="uploadFile" style="cursor: pointer; padding: 10px 15px; background: #007bff; color: white; border-radius: 5px; text-align: center; transition: background 0.3s ease;"><?= $t['upload_file'] ?></label>
        <input type="file" id="uploadFile" name="file" style="display: none;" onchange="this.form.submit()">
      </form>
      <?PHP
      $stmt = $db->prepare("SELECT is_admin FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$is_admin = $stmt->fetchColumn();
?>

<?php if ($is_admin): ?>
  <a href="user_admin.php" style="margin-left: 10px; padding: 10px 15px; background: #6c757d; color: white; text-decoration: none; border-radius: 5px;">مدیریت کاربران</a>
<?php endif; ?>


      <a href="logout.php" style="margin-left: auto; padding: 10px 15px; background: #dc3545; color: white; text-decoration: none; border-radius: 5px; transition: background 0.3s ease;"><?= $t['logout'] ?></a>
    </div>

    <div id="newFolderForm">
      <form action="create_folder.php?lang=<?= $lang ?>" method="POST"> <!-- Added lang parameter -->
        <input type="hidden" name="parent_id" value="<?= $current_folder_id ?>">
        <input type="text" name="name" placeholder="<?= $t['new_folder'] ?>" required>
        <button type="submit"><?= $t['create'] ?></button>
        <button type="button" class="cancel" onclick="toggleNewFolderForm()"><?= $t['cancel'] ?></button>
      </form>
    </div>

    <h3><?= $t['folders'] ?></h3>
    <?php foreach ($folders as $folder): ?>
      <div class="folder" data-folder-id="<?= $folder['id'] ?>" onclick="goToFolder(<?= $folder['id'] ?>)">
        <div class="icon">📁</div>
        <div class="name"><?= htmlspecialchars($folder['name']) ?></div>
        <div class="actions">
          <button class="rename" onclick="event.stopPropagation(); renameFolder(<?= $folder['id'] ?>, '<?= htmlspecialchars($folder['name']) ?>')"><?= $t['rename'] ?></button>
          <button class="delete" onclick="event.stopPropagation(); deleteFolder(<?= $folder['id'] ?>)"><?= $t['delete'] ?></button>
        </div>
      </div>
    <?php endforeach; ?>

    <h3><?= $t['files'] ?></h3>
    <?php foreach ($files as $file): ?>
  <div class="file">
    <div onclick="window.open('<?= !empty($file['token']) ? 'file.php?token=' . htmlspecialchars($file['token']) : '#' ?>', '_blank')">
      📎 <?= htmlspecialchars($file['name']) ?>
    </div>
    <div class="actions">
      <button onclick="editFile(<?= $file['id'] ?>, '<?= htmlspecialchars($file['name']) ?>')"><?= $t['edit'] ?></button>
      <button onclick="deleteFile(<?= $file['id'] ?>)"><?= $t['delete_file'] ?></button>
    </div>
  </div>
<?php endforeach; ?>

  </div>

  <script>
  function goToFolder(id) {
    if (id !== null && id !== undefined) {
      window.location.href = "index.php?folder=" + id + "&lang=<?= $lang ?>"; // Corrected the URL to use "&" for additional parameters
    }
  }

  function deleteFolder(id) {
    if (confirm("<?= $t['delete'] ?>?")) {
      fetch('delete_folder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id })
      }).then(res => {
        if (res.ok) location.reload();
        else alert("<?= $t['delete_file'] ?>");
      });
    }
  }

  function renameFolder(id, currentName) {
    const newName = prompt("<?= $t['rename'] ?>:", currentName);
    if (newName && newName !== currentName) {
      fetch('rename_folder.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id, name: newName })
      }).then(res => {
        if (res.ok) location.reload();
        else alert("<?= $t['rename'] ?>");
      });
    }
  }

  function toggleNewFolderForm() {
    const form = document.getElementById('newFolderForm');
    form.classList.toggle('visible'); // Toggle visibility of the form
  }

  document.getElementById('toggleNewFolderForm').addEventListener('click', toggleNewFolderForm);
 
 
 
 
  function deleteFile(id) {
  if (confirm("آیا مطمئنی می‌خوای این فایل حذف بشه؟")) {
    fetch('delete_file.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id })
    })
    .then(res => {
      if (res.ok) location.reload();
      else alert("خطا در حذف فایل");
    })
    .catch(() => alert("ارتباط با سرور برقرار نشد"));
  }
}



function editFile(id, currentName) {
  const newName = prompt("نام جدید فایل:", currentName);
  if (newName && newName !== currentName) {
    fetch('edit_file.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id, name: newName })
    })
    .then(res => {
      if (res.ok) location.reload();
      else alert("خطا در ویرایش فایل");
    })
    .catch(() => alert("مشکل در ارتباط با سرور"));
  }
}

 </script>
</body>
</html>


