<?php
session_start();
include 'db.php';

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'fa';
$translations = [
  'fa' => [
    'register' => 'ثبت نام',
    'username' => 'نام کاربری',
    'password' => 'رمز عبور',
    'confirm_password' => 'تایید رمز عبور',
    'submit' => 'ثبت نام',
    'login' => 'ورود',
    'already_registered' => 'قبلاً ثبت‌نام کرده‌اید؟',
  ],
  'en' => [
    'register' => 'Register',
    'username' => 'Username',
    'password' => 'Password',
    'confirm_password' => 'Confirm Password',
    'submit' => 'Register',
    'login' => 'Login',
    'already_registered' => 'Already registered?',
  ],
  'fr' => [
    'register' => 'S\'inscrire',
    'username' => 'Nom d\'utilisateur',
    'password' => 'Mot de passe',
    'confirm_password' => 'Confirmer le mot de passe',
    'submit' => 'S\'inscrire',
    'login' => 'Connexion',
    'already_registered' => 'Déjà inscrit?',
  ],
  'ar' => [
    'register' => 'إنشاء حساب',
    'username' => 'اسم المستخدم',
    'password' => 'كلمة المرور',
    'confirm_password' => 'تأكيد كلمة المرور',
    'submit' => 'إنشاء حساب',
    'login' => 'تسجيل الدخول',
    'already_registered' => 'مسجل بالفعل؟',
  ],
];
$t = $translations[$lang];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);
  $confirm_password = trim($_POST['confirm_password']);

  if ($password !== $confirm_password) {
    $error = $lang === 'fa' ? 'رمز عبور و تایید رمز عبور یکسان نیستند.' : ($lang === 'fr' ? 'Les mots de passe ne correspondent pas.' : ($lang === 'ar' ? 'كلمات المرور غير متطابقة.' : 'Passwords do not match.'));
  } else {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
      $error = $lang === 'fa' ? 'این نام کاربری قبلاً ثبت شده است.' : ($lang === 'fr' ? 'Ce nom d\'utilisateur est déjà pris.' : ($lang === 'ar' ? 'اسم المستخدم مأخوذ بالفعل.' : 'This username is already taken.'));
    } else {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
      $stmt->execute([$username, $hashed_password]);
      $_SESSION['user_id'] = $pdo->lastInsertId();
      header('Location: index.php?lang=' . $lang); // Preserve language
      exit;
    }
  }
}

// Get a random background image from the "bg" folder
$bg_images = glob('bg/*.{jpg,jpeg,png,gif}', GLOB_BRACE);
$random_bg = $bg_images[array_rand($bg_images)];
?>

<!DOCTYPE html>
<html lang="<?= $lang ?>">
<head>
  <meta charset="UTF-8">
  <title><?= $t['register'] ?></title>
  <style>
    body {
      margin: 0;
      padding: 0;
      font-family: 'Vazir', sans-serif;
      background: url('<?= $random_bg ?>') no-repeat center center fixed;
      background-size: cover;
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
    }
    .register-container {
      background: rgba(255, 255, 255, 0.9);
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      text-align: center;
      width: 300px;
    }
    .language-selector {
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
    .register-container h2 {
      margin-bottom: 20px;
      color: #333;
    }
    .register-container input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 5px;
    }
    .register-container button {
      width: 100%;
      padding: 10px;
      background: #28a745;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .register-container button:hover {
      background: #218838;
    }
    .register-container button.delete {
      background: #dc3545; /* Red background for delete button */
    }
    .register-container button.delete:hover {
      background: #c82333; /* Darker red on hover */
    }
    .register-container a {
      color: #007bff;
      text-decoration: none;
    }
    .register-container a:hover {
      text-decoration: underline;
    }
    .error {
      color: red;
      margin-bottom: 10px;
    }
  </style>
</head>
<body>
  <div class="register-container">
    <div class="language-selector">
      <a href="?lang=fa">فارسی</a> | <a href="?lang=en">English</a> | <a href="?lang=fr">Français</a> | <a href="?lang=ar">العربية</a>
    </div>
    <h2><?= $t['register'] ?></h2>
    <?php if (isset($error)): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="?lang=<?= $lang ?>">
      <input type="text" name="username" placeholder="<?= $t['username'] ?>" required>
      <input type="password" name="password" placeholder="<?= $t['password'] ?>" required>
      <input type="password" name="confirm_password" placeholder="<?= $t['confirm_password'] ?>" required>
      <button type="submit"><?= $t['submit'] ?></button>
    </form>
    <p><?= $t['already_registered'] ?> <a href="login.php?lang=<?= $lang ?>"><?= $t['login'] ?></a></p>
  </div>
</body>
</html>
