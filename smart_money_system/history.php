<?php
require 'db_connect.php';
require 'includes/auth_session.php';

$user_id = $_SESSION['user_id'];
$category_filter = $_GET['category'] ?? 'All';

if ($category_filter === 'All') {
    $stmt = $pdo->prepare('SELECT * FROM expenses WHERE user_id = ? ORDER BY expense_date DESC');
    $stmt->execute([$user_id]);
} else {
    $stmt = $pdo->prepare('SELECT * FROM expenses WHERE user_id = ? AND category = ? ORDER BY expense_date DESC');
    $stmt->execute([$user_id, $category_filter]);
}
$expenses = $stmt->fetchAll();
?>
<!doctype html>
<html>
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>History - Smart Money</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/styles.css">
  <script>
    function deleteExpense(id){
      if (!confirm('Delete this expense?')) return;
      fetch('delete_expense.php', {
        method: 'POST',
        headers: {'Content-Type':'application/json'},
        body: JSON.stringify({id: id})
      }).then(r=>r.json()).then(j=>{
        if (j.success) location.reload();
        else alert('Delete failed');
      });
    }
  </script>
</head>
<body class="app-bg">
<?php include 'includes/navbar.php'; ?>
<main class="container mx-auto p-6">
  <div class="flex justify-between items-center mb-4">
    <h1 class="text-2xl font-bold">Expense History</h1>
    <form>
      <select name="category" onchange="this.form.submit()" class="border p-2 rounded">
        <?php
        $cats = ['All','Food','Fun / Hanging out','Relationship','Other'];
        foreach($cats as $c) echo '<option'.(($c===$category_filter)?' selected':'').'>'.htmlspecialchars($c).'</option>';
        ?>
      </select>
    </form>
  </div>

  <div class="glass-card rounded shadow overflow-auto p-4">
    <table class="report-table w-full">
      <thead>
        <tr>
          <th class="p-2 text-left">Description</th>
          <th class="p-2">Category</th>
          <th class="p-2">Date</th>
          <th class="p-2">Amount (Rs.)</th>
          <th class="p-2">Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach($expenses as $e): ?>
          <tr class="border-t">
            <td class="p-2"><?php echo htmlspecialchars($e['description']); ?></td>
            <td class="p-2"><?php echo htmlspecialchars($e['category']); ?></td>
            <td class="p-2"><?php echo $e['expense_date']; ?></td>
            <td class="p-2">Rs. <?php echo number_format($e['amount'],2); ?></td>
            <td class="p-2">
              <a class="bg-green-500 text-white px-3 py-1 rounded" href="update_expense.php?id=<?php echo $e['id']; ?>">Edit</a>
              <button onclick="deleteExpense(<?php echo $e['id']; ?>)" class="bg-red-500 text-white px-3 py-1 rounded">Delete</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
</body>
</html>
