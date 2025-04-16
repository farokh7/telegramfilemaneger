<?php
session_start();
include 'db.php';

$lang = isset($_GET['lang']) ? $_GET['lang'] : 'fa';
$translations = [
  'fa' => [
    'login' => 'ورود',
    'username' => 'نام کاربری',
    'password' => 'رمز عبور',
    'submit' => 'ورود',
    'register' => 'ثبت نام',
    'no_account' => 'حساب کاربری ندارید؟',
  ],
  'en' => [
    'login' => 'Login',
    'username' => 'Username',
    'password' => 'Password',
    'submit' => 'Login',
    'register' => 'Register',
    'no_account' => 'Don\'t have an account?',
  ],
  'fr' => [
    'login' => 'Connexion',
    'username' => 'Nom d\'utilisateur',
    'password' => 'Mot de passe',
    'submit' => 'Connexion',
    'register' => 'S\'inscrire',
    'no_account' => 'Pas de compte?',
  ],
  'ar' => [
    'login' => 'تسجيل الدخول',
    'username' => 'اسم المستخدم',
    'password' => 'كلمة المرور',
    'submit' => 'تسجيل الدخول',
    'register' => 'إنشاء حساب',
    'no_account' => 'ليس لديك حساب؟',
  ],
];
$t = $translations[$lang];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $username = trim($_POST['username']);
  $password = trim($_POST['password']);

  $stmt = $db->prepare("SELECT * FROM users WHERE username = ?");
  $stmt->execute([$username]);
  $user = $stmt->fetch();

  if ($user && password_verify($password, $user['password'])) {
    $_SESSION['user_id'] = $user['id'];
    header('Location: index.php?lang=' . $lang); // Preserve language
    exit;
  } else {
    $error = $lang === 'fa' ? 'نام کاربری یا رمز عبور اشتباه است.' : ($lang === 'fr' ? 'Nom d\'utilisateur ou mot de passe incorrect.' : ($lang === 'ar' ? 'اسم المستخدم أو كلمة المرور غير صحيحة.' : 'Invalid username or password.'));
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
  <title><?= $t['login'] ?></title>
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
    .login-container {
      background: rgba(255, 255, 255, 0.9);
      padding: 30px;
      border-radius: 10px;
      box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
      text-align: center;
      width: 300px;
    }
    .login-container h2 {
      margin-bottom: 20px;
      color: #333;
    }
    .login-container input {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 5px;
    }
    .login-container button {
      width: 100%;
      padding: 10px;
      background: #007bff;
      color: white;
      border: none;
      border-radius: 5px;
      cursor: pointer;
      transition: background 0.3s ease;
    }
    .login-container button:hover {
      background: #0056b3;
    }
    .login-container a {
      color: #007bff;
      text-decoration: none;
    }
    .login-container a:hover {
      text-decoration: underline;
    }
    .error {
      color: red;
      margin-bottom: 10px;
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
  </style>
</head>
<body>
  <div class="login-container">
    <div class="language-selector">
      <a href="?lang=fa">فارسی</a> | <a href="?lang=en">English</a> | <a href="?lang=fr">Français</a> | <a href="?lang=ar">العربية</a>
    </div>
    <h2><?= $t['login'] ?></h2>
    <?php if (isset($error)): ?>
      <p class="error"><?= htmlspecialchars($error) ?></p>
    <?php endif; ?>
    <form method="POST" action="?lang=<?= $lang ?>">
      <input type="text" name="username" placeholder="<?= $t['username'] ?>" required>
      <input type="password" name="password" placeholder="<?= $t['password'] ?>" required>
      <button type="submit"><?= $t['submit'] ?></button>
    </form>
    <p><?= $t['no_account'] ?> <a href="register.php?lang=<?= $lang ?>"><?= $t['register'] ?></a></p>
  </div>
</body>
</html>
