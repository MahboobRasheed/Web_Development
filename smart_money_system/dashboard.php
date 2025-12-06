<?php
require 'db_connect.php';
require 'includes/auth_session.php';

$user_id = $_SESSION['user_id'];

// Determine month/year (allow override via GET)
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Handle saving monthly limit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $posted = $_POST['limit'] ?? '';
    // normalize numeric input (allow comma separators)
    $newLimit = (float)str_replace(',', '', $posted);
    if ($newLimit <= 0) $newLimit = 0.0;

    // upsert into budgets for this user/month/year
    $stmt = $pdo->prepare('SELECT id FROM budgets WHERE user_id = ? AND month = ? AND year = ? LIMIT 1');
    $stmt->execute([$user_id, $month, $year]);
    $exists = $stmt->fetchColumn();
    if ($exists) {
      $stmt = $pdo->prepare('UPDATE budgets SET monthly_limit = ? WHERE user_id = ? AND month = ? AND year = ?');
      $stmt->execute([$newLimit, $user_id, $month, $year]);
    } else {
      $stmt = $pdo->prepare('INSERT INTO budgets (user_id, monthly_limit, month, year) VALUES (?, ?, ?, ?)');
      $stmt->execute([$user_id, $newLimit, $month, $year]);
    }

    // Redirect to the same view to avoid form re-submit and show updated values
    header('Location: dashboard.php?month=' . $month . '&year=' . $year);
    exit;
}

// Total spent this month
$stmt = $pdo->prepare('SELECT COALESCE(SUM(amount),0) as total FROM expenses WHERE user_id = ? AND MONTH(expense_date)=? AND YEAR(expense_date)=?');
$stmt->execute([$user_id, $month, $year]);
$total = (float)$stmt->fetchColumn();

// Monthly limit
$stmt = $pdo->prepare('SELECT monthly_limit FROM budgets WHERE user_id = ? AND month = ? AND year = ? LIMIT 1');
$stmt->execute([$user_id, $month, $year]);
$limit = $stmt->fetchColumn();
if ($limit === false) $limit = 5000.00;

$remaining = $limit - $total;
// Do not show negative remaining — when spent exceeds limit show zero remaining
if ($remaining < 0) $remaining = 0.00;

// recent expenses (all categories)
$stmt = $pdo->prepare('SELECT * FROM expenses WHERE user_id = ? ORDER BY expense_date DESC LIMIT 5');
$stmt->execute([$user_id]);
$recent_expenses = $stmt->fetchAll();

function monthName($m){ return date('F', mktime(0,0,0,$m,1)); }
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <title>Dashboard - Smart Money</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
  <meta name="viewport" content="width=device-width,initial-scale=1">
</head>
<body class="app-bg">
<?php include 'includes/navbar.php'; ?>
<main class="container mx-auto p-6">
  <!-- Hero moved to includes/ads.php to avoid duplication -->

  <div class="flex justify-between items-center mb-6">
    <h1 class="text-2xl font-bold">Dashboard</h1>
    <form method="get" class="flex space-x-2 items-center">
      <select name="month" class="border p-2 rounded">
        <?php for($i=1;$i<=12;$i++): ?>
          <option value="<?php echo $i; ?>" <?php if($i==$month) echo 'selected'; ?>><?php echo monthName($i); ?></option>
        <?php endfor; ?>
      </select>
      <select name="year" class="border p-2 rounded">
        <?php for($y = date('Y')-2; $y <= date('Y')+1; $y++): ?>
          <option value="<?php echo $y; ?>" <?php if($y==$year) echo 'selected'; ?>><?php echo $y; ?></option>
        <?php endfor; ?>
      </select>
      <button class="bg-sky-600 text-white p-2 rounded">Filter</button>
    </form>
  </div>

  <div class="panel-row mb-6">
    <div class="glass-card p-4">
      <div class="text-sm muted">Total Spent (<?php echo monthName($month)." ".$year; ?>)</div>
      <div class="text-2xl font-bold">Rs. <?php echo number_format($total,2); ?></div>
        <div class="mt-3">
        <a href="generate_pdf.php?month=<?php echo $month; ?>&year=<?php echo $year; ?>" class="download-btn"><img src="assets/icons/download.svg" class="inline-icon" alt=""> Download report (PDF)</a>
      </div>
    </div>
    <div class="glass-card p-4">
      <div class="text-sm muted">Monthly Limit</div>
      <div class="text-2xl font-bold">Rs. <?php echo number_format($limit,2); ?></div>
      <form method="post" action="">
        <div class="mt-3">
          <input type="number" name="limit" value="<?php echo htmlspecialchars($limit); ?>" class="w-full border p-2 rounded" placeholder="5000">
        </div>
        <div class="mt-3"><button class="small-btn">Save limit</button></div>
      </form>
    </div>
    <div class="glass-card p-4">
      <div class="text-sm muted">Remaining</div>
      <div class="text-2xl font-bold">Rs. <?php echo number_format($remaining,2); ?></div>
    </div>
  </div>

  <?php if (isset($_GET['debug']) && $_GET['debug']=='1'): ?>
    <div class="glass-card p-4 mb-4">
      <div><strong>Debug info</strong></div>
      <div>User id (session): <?php echo htmlspecialchars($_SESSION['user_id'] ?? ''); ?></div>
      <?php
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM expenses WHERE user_id = ?');
        $stmt->execute([$_SESSION['user_id'] ?? 0]);
        $userCount = $stmt->fetchColumn();
        $stmt = $pdo->query('SELECT COUNT(*) FROM expenses');
        $totalCount = $stmt->fetchColumn();
      ?>
      <div>Expenses for this user: <?php echo (int)$userCount; ?></div>
      <div>Total expenses in DB: <?php echo (int)$totalCount; ?></div>
    </div>
  <?php endif; ?>

  <!-- removed duplicate Download button (kept the primary one in the overview panel) -->

  <section>
    <h2 class="text-xl font-semibold mb-3">Recent Expenses</h2>
    <div class="glass-card p-4 rounded shadow">
      <?php if (empty($recent_expenses)): ?>
        <div class="text-sm">No recent expenses.</div>
      <?php else: ?>
        <ul>
          <?php foreach($recent_expenses as $r): ?>
            <li class="flex justify-between py-2 border-b">
              <div class="text-sm" style="color:var(--text, #dbeafe)"><?php echo htmlspecialchars($r['description']); ?> <span class="text-xs muted"> (<?php echo $r['expense_date']; ?>)</span></div>
              <div class="text-sm">Rs. <?php echo number_format($r['amount'],2); ?></div>
            </li>
          <?php endforeach; ?>
        </ul>
      <?php endif; ?>
    </div>
  </section>

  <!-- Promotional / feature cards (like screenshots) -->
  <?php include 'includes/ads.php'; ?>
</main>
</body>
</html>
