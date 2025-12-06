<?php
if (session_status() == PHP_SESSION_NONE) session_start();
$name = isset($_SESSION['name']) ? htmlspecialchars($_SESSION['name']) : '';
?>
<nav class="bg-gradient-to-r from-slate-900 via-slate-800 to-slate-900 text-white">
  <div class="container mx-auto flex items-center justify-between px-4 py-3">
    <div class="flex items-center gap-3">
      <a href="dashboard.php" class="flex items-center gap-3">
        <span class="inline-block h-9 w-9 rounded-full bg-gradient-to-br from-indigo-500 to-pink-600 flex items-center justify-center text-white font-bold">CB</span>
        <span class="font-semibold text-white">Campus Budget Manager</span>
      </a>
    </div>

    <!-- Desktop links -->
    <div class="hidden md:flex items-center gap-3">
      <a href="dashboard.php" class="nav-link"><img src="assets/icons/dashboard.svg" class="nav-icon" alt=""> <span>Dashboard</span></a>
      <a href="add_expense.php" class="nav-link"><img src="assets/icons/plus.svg" class="nav-icon" alt=""> <span>Add Expense</span></a>
      <a href="history.php" class="nav-link"><img src="assets/icons/history.svg" class="nav-icon" alt=""> <span>History</span></a>
      <div class="flex items-center gap-2 ml-3 nav-user">
        <img src="assets/icons/dashboard.svg" alt="user" />
        <span class="px-3 py-1 rounded-full bg-slate-700 text-sm">Hi, <?php echo $name ?: 'User'; ?></span>
        <a href="logout.php" class="nav-link"><img src="assets/icons/logout.svg" class="nav-icon" alt=""> <span>Logout</span></a>
      </div>
    </div>

    <!-- Mobile menu button -->
    <div class="md:hidden">
      <button id="nav-toggle" aria-label="Toggle menu" class="p-2 focus:outline-none">
        <svg id="hamburger" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
      </button>
    </div>
  </div>

  <!-- Mobile menu (hidden by default) -->
  <div id="mobile-menu" class="hidden md:hidden bg-slate-900 border-t border-slate-800">
    <div class="px-4 pt-3 pb-4 space-y-2">
      <a href="dashboard.php" class="block px-3 py-2 rounded hover:bg-slate-800">Dashboard</a>
      <a href="add_expense.php" class="block px-3 py-2 rounded hover:bg-slate-800">Add Expense</a>
      <a href="history.php" class="block px-3 py-2 rounded hover:bg-slate-800">History</a>
      <div class="pt-2 border-t border-slate-800">
        <div class="flex items-center justify-between mt-2">
          <div class="flex items-center gap-3">
            <span class="inline-block h-8 w-8 rounded-full bg-indigo-600 text-white flex items-center justify-center font-semibold"><?php echo strtoupper(substr($name?:'U',0,1)); ?></span>
            <div>
              <div class="text-sm font-medium"><?php echo $name ?: 'User'; ?></div>
              <div class="text-xs text-slate-400">Welcome back</div>
            </div>
          </div>
          <a href="logout.php" class="px-3 py-1 rounded bg-red-500 text-white">Logout</a>
        </div>
      </div>
    </div>
  </div>

  <script>
    (function(){
      var btn = document.getElementById('nav-toggle');
      var menu = document.getElementById('mobile-menu');
      btn && btn.addEventListener('click', function(){
        if(menu.classList.contains('hidden')) menu.classList.remove('hidden'); else menu.classList.add('hidden');
      });
    })();
  </script>
</nav>
