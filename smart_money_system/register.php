<?php
require 'db_connect.php';
session_start();

$errors = [];
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm = $_POST['confirm'] ?? '';

    if (!$name) $errors[] = 'Name is required.';
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Valid email is required.';
    if (strlen($password) < 6) $errors[] = 'Password must be at least 6 characters.';
    if ($password !== $confirm) $errors[] = 'Passwords do not match.';

    if (empty($errors)) {
        // check email exists
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$email]);
        if ($stmt->fetch()) {
            $errors[] = 'Email already registered.';
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare('INSERT INTO users (name, email, password) VALUES (?, ?, ?)');
            $stmt->execute([$name, $email, $hash]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            $_SESSION['name'] = $name;
            header('Location: dashboard.php');
            exit;
        }
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Register - Smart Money</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="app-bg">
  <div class="min-h-screen flex items-center justify-center p-6">
    <div class="w-full max-w-4xl grid lg:grid-cols-2 gap-6 items-center">
      <div class="hidden lg:block rounded-lg" style="background:linear-gradient(180deg,#0f3b6f,#0b2b58);padding:2.2rem;border-radius:14px;box-shadow:inset 0 1px 0 rgba(255,255,255,0.02);">
        <h2 style="color:#dbeafe;font-size:1.6rem;font-weight:800;margin-bottom:.6rem">Create an account</h2>
        <p style="color:var(--muted)">Start tracking your expenses and set monthly limits to stay on top of campus spending.</p>
      </div>
      <div class="glass-card p-8 rounded shadow">
        <h2 class="text-2xl mb-4">Create an account</h2>
      <?php if (!empty($errors)): ?>
        <div class="bg-red-100 p-3 mb-4 rounded">
          <ul>
            <?php foreach ($errors as $e): ?><li><?php echo htmlspecialchars($e); ?></li><?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>
      <form method="post" action="register.php" class="space-y-4">
        <div>
          <label class="block text-sm">Name</label>
          <input type="text" name="name" class="w-full border p-2 rounded" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" autofocus>
        </div>
        <div>
          <label class="block text-sm">Email</label>
          <input name="email" type="email" class="w-full border p-2 rounded" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
        </div>
        <div>
          <label class="block text-sm">Password</label>
          <input name="password" type="password" class="w-full border p-2 rounded" required>
        </div>
        <div>
          <label class="block text-sm">Confirm Password</label>
          <input name="confirm" type="password" class="w-full border p-2 rounded" required>
        </div>
        <div>
          <button class="w-full bg-sky-600 text-white p-2 rounded">Register</button>
        </div>
      </form>
      <p class="mt-4 text-sm">Already have an account? <a href="login.php" class="text-sky-600">Log in</a></p>
    </div>
  </div>
</body>
</html>
