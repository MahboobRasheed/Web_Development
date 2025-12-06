<?php
// generate_pdf.php
// Uses Dompdf (HTML -> PDF) to create a styled monthly expense report.
// If Dompdf is not installed, the page will show instructions to install it via Composer.
// Usage: generate_pdf.php?month=11&year=2025

require 'db_connect.php';
require 'includes/auth_session.php';

$user_id = $_SESSION['user_id'];
$month = isset($_GET['month']) ? (int)$_GET['month'] : (int)date('n');
$year  = isset($_GET['year']) ? (int)$_GET['year'] : (int)date('Y');

// Fetch expenses for the month
$stmt = $pdo->prepare('SELECT description, category, amount, expense_date FROM expenses WHERE user_id = ? AND MONTH(expense_date)=? AND YEAR(expense_date)=? ORDER BY expense_date');
$stmt->execute([$user_id, $month, $year]);
$rows = $stmt->fetchAll();

// If Dompdf autoload isn't available, render a printable HTML fallback with a Print button
if (!file_exists(__DIR__ . '/vendor/autoload.php')) {
        // compute total and month name for the printable view
        $total = 0;
        foreach ($rows as $r) $total += (float)$r['amount'];
        $monthLabel = date('F', mktime(0,0,0,$month,1)) . ' ' . $year;
        ?>
        <!doctype html>
        <html>
        <head>
                <meta charset="utf-8">
                <title>Printable Report - <?php echo htmlspecialchars($monthLabel); ?></title>
                <meta name="viewport" content="width=device-width,initial-scale=1">
                <link rel="stylesheet" href="assets/styles.css">
                <style>
                    body{padding:1rem}
                    .print-actions{display:flex;gap:.5rem;align-items:center;margin-bottom:1rem}
                    .print-btn{background:#10b981;color:#fff;padding:10px 14px;border-radius:8px;border:0;font-weight:700}
                    .install-note{background:rgba(255,255,255,0.03);padding:12px;border-radius:8px;border:1px solid rgba(255,255,255,0.04);margin-bottom:1rem}
                    @media print{.no-print{display:none}}

                    /* Printable report table styling: fixed layout and explicit column widths
                         to avoid browser/print quirks that push cells onto new lines. */
                    .report-table{width:100%;border-collapse:collapse;table-layout:fixed;font-size:13px}
                    .report-table thead th{padding:12px 10px;text-align:left;color:#cfe7ff}
                    .report-table tbody td{padding:12px 10px;color:#dbeafe;vertical-align:middle}
                    .report-table tbody tr + tr td{border-top:1px solid rgba(255,255,255,0.03)}
                    .report-table td.right, .report-table th.right{text-align:right}
                      .report-table th:first-child, .report-table td:first-child{width:14%}
                      .report-table th:nth-child(2), .report-table td:nth-child(2){width:36%}
                      .report-table th:nth-child(3), .report-table td:nth-child(3){width:26%}
                      .report-table th:nth-child(4), .report-table td:nth-child(4){width:24%}
                    .total-row td{font-weight:700;border-top:2px solid rgba(255,255,255,0.06);color:#dbeafe}
                </style>
        </head>
        <body class="app-bg">
            <main class="container mx-auto p-4">
                <div class="no-print print-actions">
                    <button class="print-btn" onclick="window.print()">Print / Save as PDF</button>
                    <a class="small-btn" href="javascript:location.reload()">Reload</a>
                </div>

                <div class="glass-card p-4">
                    <h2 class="card-header">Monthly Expense Report — <?php echo htmlspecialchars($monthLabel); ?></h2>
                    <div class="muted">User: <?php echo htmlspecialchars($_SESSION['name'] ?? ''); ?></div>
                    <table class="report-table" style="margin-top:12px">
                        <thead>
                            <tr>
                                <th style="width:14%">Date</th>
                                <th>Description</th>
                                <th style="width:20%">Category</th>
                                <th style="width:14%">Amount (Rs.)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($rows as $r): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($r['expense_date']); ?></td>
                                    <td><?php echo htmlspecialchars($r['description']); ?></td>
                                    <td><?php echo htmlspecialchars($r['category']); ?></td>
                                    <td class="right"><?php echo number_format($r['amount'],2); ?></td>
                                </tr>
                            <?php endforeach; ?>
                            <tr class="total-row"><td colspan="3" style="text-align:right">Total</td><td class="right"><?php echo number_format($total,2); ?></td></tr>
                        </tbody>
                    </table>
                </div>
            </main>
        </body>
        </html>
        <?php
        exit;
}

require_once __DIR__ . '/vendor/autoload.php';
use Dompdf\Dompdf;

// Build HTML for the PDF (inline styles for Dompdf)
function monthName($m){ return date('F', mktime(0,0,0,$m,1)); }

$total = 0;
foreach ($rows as $r) $total += (float)$r['amount'];

$html = '<!doctype html><html><head><meta charset="utf-8"><style>
body{font-family: DejaVu Sans, Arial, Helvetica, sans-serif; color:#111; margin:0; padding:0}
.header{background:#4CAF50;color:#fff;padding:10px;text-align:center;font-size:24px;font-weight:bold}
.footer{background:#f1f1f1;color:#555;padding:8px;text-align:center;font-size:12px;position:fixed;bottom:0;width:100%}
.report{width:100%;margin:20px auto;padding:10px}
.title{text-align:center;margin-bottom:18px;font-size:20px;font-weight:bold;color:#333}
.meta{margin-bottom:12px;font-size:14px;color:#555}
table{width:100%;border-collapse:collapse;font-size:12px}
th,td{border:1px solid #777;padding:8px}
th{background:#eee;text-align:left}
.right{text-align:right}
.total-row td{font-weight:700;border-top:2px solid #444}
</style></head><body>
<div class="header">Smart Money System</div>
<div class="report">
    <h2 class="title">Monthly Expense Report</h2>
    <div class="meta"><strong>Month:</strong> ' . htmlspecialchars(monthName($month) . ' ' . $year) . '&nbsp;&nbsp;&nbsp; <strong>User:</strong> ' . htmlspecialchars($_SESSION['name'] ?? '') . '</div>
    <table>
        <thead><tr><th style="width:18%">Date</th><th>Description</th><th style="width:22%">Category</th><th style="width:16%">Amount (Rs.)</th></tr></thead>
        <tbody>';

foreach ($rows as $r) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($r['expense_date']) . '</td>';
        $html .= '<td>' . htmlspecialchars($r['description']) . '</td>';
        $html .= '<td>' . htmlspecialchars($r['category']) . '</td>';
        $html .= '<td class="right">' . number_format($r['amount'],2) . '</td>';
        $html .= '</tr>';
}

$html .= '<tr class="total-row"><td colspan="3" style="text-align:right">Total</td><td class="right">' . number_format($total,2) . '</td></tr>';

$html .= '</tbody></table></div><div class="footer">&copy; ' . date('Y') . ' Smart Money System. All rights reserved.</div></body></html>';

$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

$filename = 'monthly-report-' . $year . '-' . str_pad($month,2,'0',STR_PAD_LEFT) . '.pdf';
$dompdf->stream($filename, ['Attachment' => true]);
exit;
?>