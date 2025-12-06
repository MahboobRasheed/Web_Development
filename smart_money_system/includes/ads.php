<?php
// includes/ads.php - renders promotional/feature cards shown on the dashboard
?>
<section class="mt-6">
  <div class="glass-card p-4">
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 items-start">
      <div>
        <h3 class="card-header text-lg">Track your campus spending</h3>
        <p class="mt-2 text-sm text-slate-200">Keep all your food, fun, function, and relationship expenses in one simple place. Set a monthly limit and see how much you have left.</p>
        <div class="mt-4 flex gap-2">
          <a href="add_expense.php" class="download-btn">+ Add today's expense</a>
          <a href="history.php" class="px-4 py-2 rounded-md bg-slate-700 text-white">View recent expenses</a>
        </div>
      </div>
      <div>
        <div class="feature-grid">
          <div class="feature-item">
            <div class="flex items-center gap-3">
              <img src="assets/icons/food.svg" alt="Food" class="icon" aria-hidden="true">
              <div class="text-xs text-slate-300">Food tracking</div>
            </div>
            <div class="mt-2 font-semibold text-white">Quickly log hostel meals, canteen snacks, and coffee breaks.</div>
            <div class="mt-3 text-xs text-slate-400">Campus life friendly</div>
          </div>
          <div class="feature-item">
            <div class="flex items-center gap-3">
              <img src="assets/icons/fun.svg" alt="Fun" class="icon" aria-hidden="true">
              <div class="text-xs text-slate-300">Fun & functions</div>
            </div>
            <div class="mt-2 font-semibold text-white">Separate spending for parties, functions, and hangouts with friends.</div>
            <div class="mt-3 text-xs text-slate-400">Know where your money goes</div>
          </div>
          <div class="feature-item">
            <div class="flex items-center gap-3">
              <img src="assets/icons/heart.svg" alt="Relationship" class="icon" aria-hidden="true">
              <div class="text-xs text-slate-300">Relationship budget</div>
            </div>
            <div class="mt-2 font-semibold text-white">Manage gifts and dates without surprises.</div>
            <div class="mt-3 text-xs text-slate-400">Stay balanced</div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
