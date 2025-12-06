<?php
require 'db_connect.php';
require 'includes/auth_session.php';

$user_id = $_SESSION['user_id'];
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$stmt = $pdo->prepare('SELECT * FROM expenses WHERE id = ? AND user_id = ? LIMIT 1');
$stmt->execute([$id, $user_id]);
$expense = $stmt->fetch();
if (!$expense) {
    echo 'Expense not found.';
    exit;
}

$errors = [];
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $description = trim($_POST['description'] ?? '');
    $amount = $_POST['amount'] ?? '';
    $category = $_POST['category'] ?? 'Other';
    $date = $_POST['date'] ?? '';

    if ($description === '') $errors[] = 'Description required.';
    if (!is_numeric($amount) || $amount <= 0) $errors[] = 'Enter a valid amount.';
    if (!$date) $errors[] = 'Select a date.';

    if (empty($errors)) {
        $stmt = $pdo->prepare('UPDATE expenses SET description=?, amount=?, category=?, expense_date=? WHERE id=? AND user_id=?');
        $stmt->execute([$description, $amount, $category, $date, $id, $user_id]);
        $success = 'Expense updated.';
        // refresh expense
        $stmt = $pdo->prepare('SELECT * FROM expenses WHERE id = ? AND user_id = ? LIMIT 1');
        $stmt->execute([$id, $user_id]);
        $expense = $stmt->fetch();
    }
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Edit Expense - Smart Money</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="app-bg">
<?php include 'includes/navbar.php'; ?>
<main class="container mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4">Edit Expense</h1>
  <?php if ($success): ?><div class="bg-green-100 p-3 mb-4 rounded"><?php echo htmlspecialchars($success); ?></div><?php endif; ?>
  <?php if (!empty($errors)): ?><div class="bg-red-100 p-3 mb-4 rounded"><ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul></div><?php endif; ?>
  <form method="post" class="glass-card p-4 rounded shadow space-y-4">
    <div>
      <label class="block text-sm">Description</label>
      <input type="text" name="description" value="<?php echo htmlspecialchars($expense['description']); ?>" class="w-full border p-2 rounded" required>
    </div>
    <div>
      <label class="block text-sm">Amount (Rs.)</label>
      <input name="amount" type="number" step="0.01" value="<?php echo $expense['amount']; ?>" class="w-full border p-2 rounded" required>
    </div>
    <div>
      <label class="block text-sm">Category</label>
      <select name="category" class="w-full border p-2 rounded">
        <?php $cats=['Food','Fun / Hanging out','Relationship','Other']; foreach($cats as $c) echo '<option'.(($c==$expense['category'])?' selected':'').'>'.htmlspecialchars($c).'</option>'; ?>
      </select>
    </div>
    <div>
      <label class="block text-sm">Date</label>
      <input name="date" type="date" value="<?php echo $expense['expense_date']; ?>" class="w-full border p-2 rounded" required>
    </div>
    <div>
      <button class="bg-sky-600 text-white p-2 rounded">Save Changes</button>
    </div>
  </form>
</main>
</body>
</html>
