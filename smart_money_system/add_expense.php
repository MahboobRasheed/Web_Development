<?php
require 'db_connect.php';
require 'includes/auth_session.php';

$user_id = $_SESSION['user_id'];
$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $description = trim($_POST['description'] ?? '');
  $amount = (float)str_replace(',', '', ($_POST['amount'] ?? '0'));
  $category = $_POST['category'] ?? 'Other';
  $date = $_POST['date'] ?? '';

  if ($description === '') $errors[] = 'Description required.';
  if (!is_numeric($amount) || $amount <= 0) $errors[] = 'Enter a valid amount.';
  if (!$date) $errors[] = 'Select a date.';

  // If there are no basic validation errors, enforce monthly budget limit
  if (empty($errors)) {
    $d = new DateTime($date);
    $m = (int)$d->format('n');
    $y = (int)$d->format('Y');

    // get monthly limit for this user/month/year, default to 5000.00
    $stmt = $pdo->prepare('SELECT monthly_limit FROM budgets WHERE user_id = ? AND month = ? AND year = ? LIMIT 1');
    $stmt->execute([$user_id, $m, $y]);
    $limit = $stmt->fetchColumn();
    if ($limit === false) $limit = 5000.00;

    // current total for the month
    $stmt = $pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id = ? AND MONTH(expense_date)=? AND YEAR(expense_date)=?');
    $stmt->execute([$user_id, $m, $y]);
    $currentTotal = (float)$stmt->fetchColumn();

    $remaining = $limit - $currentTotal;

    if ($remaining <= 0) {
      $errors[] = 'Monthly limit reached for ' . $d->format('F Y') . '. You cannot add more expenses for this month.';
    } elseif ($amount > $remaining) {
      $errors[] = 'This expense would exceed your monthly limit. You can add up to Rs. ' . number_format($remaining,2) . ' for ' . $d->format('F Y') . '.';
    } else {
      // OK to insert
      $stmt = $pdo->prepare('INSERT INTO expenses (user_id, description, amount, category, expense_date) VALUES (?, ?, ?, ?, ?)');
      $stmt->execute([$user_id, $description, $amount, $category, $date]);
      $success = 'Expense added successfully.';
      // clear POST values to avoid showing them after success
      $_POST = [];
    }
  }
}
// Preview remaining for the selected/default date so user sees what's allowed
$previewDate = $_POST['date'] ?? date('Y-m-d');
try {
  $pd = new DateTime($previewDate);
  $pm = (int)$pd->format('n');
  $py = (int)$pd->format('Y');
  $stmt = $pdo->prepare('SELECT monthly_limit FROM budgets WHERE user_id = ? AND month = ? AND year = ? LIMIT 1');
  $stmt->execute([$user_id, $pm, $py]);
  $previewLimit = $stmt->fetchColumn();
  if ($previewLimit === false) $previewLimit = 5000.00;
  $stmt = $pdo->prepare('SELECT COALESCE(SUM(amount),0) FROM expenses WHERE user_id = ? AND MONTH(expense_date)=? AND YEAR(expense_date)=?');
  $stmt->execute([$user_id, $pm, $py]);
  $previewTotal = (float)$stmt->fetchColumn();
  $previewRemaining = $previewLimit - $previewTotal;
} catch (Exception $e) {
  $previewLimit = 0; $previewTotal = 0; $previewRemaining = 0;
}
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Add Expense - Smart Money</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
</head>
<body class="app-bg">
<?php include 'includes/navbar.php'; ?>
<main class="container mx-auto p-6">
  <h1 class="text-2xl font-bold mb-4">Add Expense</h1>
  <?php if ($success): ?>
    <div class="bg-green-100 p-3 mb-4 rounded"><?php echo htmlspecialchars($success); ?></div>
  <?php endif; ?>
  <?php if (!empty($errors)): ?>
    <div class="bg-red-100 p-3 mb-4 rounded">
      <ul><?php foreach($errors as $e) echo '<li>'.htmlspecialchars($e).'</li>'; ?></ul>
    </div>
  <?php endif; ?>
  <form method="post" class="glass-card p-4 rounded shadow space-y-4">
      <div>
      <label class="block text-sm">Description</label>
      <input type="text" name="description" class="w-full border p-2 rounded" required value="<?php echo htmlspecialchars($_POST['description'] ?? ''); ?>">
    </div>
    <div>
      <label class="block text-sm">Amount (Rs.)</label>
      <input name="amount" type="number" step="0.01" class="w-full border p-2 rounded" required value="<?php echo htmlspecialchars($_POST['amount'] ?? ''); ?>">
      <?php if (isset($previewLimit)): ?>
        <div class="text-sm muted mt-2">Monthly limit: <strong>Rs. <?php echo number_format($previewLimit,2); ?></strong> — Remaining: <strong>Rs. <?php echo number_format(max(0,$previewRemaining),2); ?></strong></div>
      <?php endif; ?>
    </div>
    <div>
      <label class="block text-sm">Category</label>
      <select name="category" class="w-full border p-2 rounded">
        <option <?php if(($_POST['category'] ?? '') === 'Food') echo 'selected'; ?>>Food</option>
        <option <?php if(($_POST['category'] ?? '') === 'Fun / Hanging out') echo 'selected'; ?>>Fun / Hanging out</option>
        <option <?php if(($_POST['category'] ?? '') === 'Relationship') echo 'selected'; ?>>Relationship</option>
        <option <?php if(($_POST['category'] ?? '') === 'Other' || !isset($_POST['category'])) echo 'selected'; ?>>Other</option>
      </select>
    </div>
    <div>
      <label class="block text-sm">Date</label>
      <input name="date" type="date" class="w-full border p-2 rounded" required value="<?php echo htmlspecialchars($_POST['date'] ?? date('Y-m-d')); ?>">
    </div>
    <div>
      <button class="download-btn"><img src="assets/icons/plus.svg" class="inline-icon" alt=""> Save expense</button>
    </div>
  </form>
</main>
</body>
</html>
