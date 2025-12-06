<?php
require 'db_connect.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Enter a valid email.';
    if (!$password) $errors[] = 'Enter the password.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, name, password FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            header('Location: dashboard.php');
            exit;
        } else {
            $errors[] = 'Invalid credentials.';
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Login - Smart Money</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="app-bg">
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-4xl grid lg:grid-cols-2 gap-6 items-center">
      <div class="hidden lg:block rounded-lg" style="background:linear-gradient(180deg,#0f3b6f,#0b2b58);padding:2.2rem;border-radius:14px;box-shadow:inset 0 1px 0 rgba(255,255,255,0.02);">
        <h2 style="color:#dbeafe;font-size:1.6rem;font-weight:800;margin-bottom:.6rem">Welcome back!</h2>
        <p style="color:var(--muted)">Log in to see your campus expense dashboard and manage your money smartly.</p>
      </div>
      <div class="glass-card p-8 rounded shadow">
        <h2 class="text-2xl mb-4">Log In</h2>
      <?php if (!empty($errors)): ?>
        <div class="bg-red-100 p-3 mb-4 rounded">
          <ul>
            <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <form method="post" action="login.php" class="space-y-4">
        <div>
          <label class="block text-sm">Email</label>
          <input name="email" type="email" class="w-full border p-2 rounded" required>
        </div>
        <div>
          <label class="block text-sm">Password</label>
          <input name="password" type="password" class="w-full border p-2 rounded" required>
        </div>
        <div>
          <button class="w-full bg-sky-600 text-white p-2 rounded">Log In</button>
        </div>
      </form>
      <p class="mt-4 text-sm">Don't have an account? <a href="register.php" class="text-sky-600">Create an account</a></p>
    </div>
  </div>
</body>
</html>
