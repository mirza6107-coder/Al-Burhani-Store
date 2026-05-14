<?php
// ============================================================
//  AL BURHAN STORE — revenue.php (FULLY FIXED)
// ============================================================

session_start();
require_once 'config.php';

 $pdo = getDB();

 $ajax   = isset($_GET['ajax']);
 $export = $_GET['export'] ?? null;

// === FILTERS ===
 $period     = $_GET['period'] ?? 'this_month';
 $start_date = $_GET['start_date'] ?? null;
 $end_date   = $_GET['end_date'] ?? null;

 $orderDateColumn = 'orders.created_at';

 $where   = [];
 $params  = [];

// FIX: When custom date range is provided, ignore the period dropdown
if ($start_date && $end_date) {
    $where[] = "$orderDateColumn BETWEEN ? AND ?";
    $params[] = $start_date . ' 00:00:00';
    $params[] = $end_date . ' 23:59:59';
} else {
    switch($period) {
        case 'this_month':
            $where[] = "$orderDateColumn >= DATE_FORMAT(CURDATE(), '%Y-%m-01')";
            break;
        case '3months':
            $where[] = "$orderDateColumn >= DATE_SUB(CURDATE(), INTERVAL 3 MONTH)";
            break;
        case '6months':
            $where[] = "$orderDateColumn >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)";
            break;
        case 'year':
            $where[] = "$orderDateColumn >= DATE_FORMAT(CURDATE(), '%Y-01-01')";
            break;
        case 'all':
            break;
        default:
            $where[] = "$orderDateColumn >= DATE_FORMAT(CURDATE(), '%Y-%m-01')";
    }
}

 $whereClause = $where ? "WHERE " . implode(" AND ", $where) : "";

// ── Main Stats ──────────────────────────────────────────────
 $statsQuery = $pdo->prepare("
    SELECT 
        COALESCE(SUM(total), 0) AS total_revenue,
        COALESCE(SUM(CASE WHEN status = 'delivered' THEN total ELSE 0 END), 0) AS delivered_revenue,
        COALESCE(COUNT(*), 0) AS total_orders,
        COALESCE(AVG(total), 0) AS avg_order,
        COALESCE(SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END), 0) AS cancelled_orders,
        COALESCE(SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END), 0) AS pending_orders,
        COALESCE(SUM(shipping_cost), 0) AS total_shipping,
        COALESCE(SUM(vat), 0) AS total_vat,
        COALESCE(SUM(discount), 0) AS total_discount
    FROM orders $whereClause
");
 $statsQuery->execute($params);
 $stats = $statsQuery->fetch(PDO::FETCH_ASSOC);

// FIX: This-month stat is always calculated (not affected by filters)
 $thisMonthQuery = $pdo->query("
    SELECT COALESCE(SUM(total), 0) AS this_month
    FROM orders 
    WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')
");
 $stats['this_month'] = $thisMonthQuery->fetchColumn();

// ── Payment Methods ─────────────────────────────────────────
 $paymentQuery = $pdo->prepare("
    SELECT payment_method, COUNT(*) AS count, SUM(total) AS revenue 
    FROM orders $whereClause 
    GROUP BY payment_method 
    ORDER BY revenue DESC
");
 $paymentQuery->execute($params);
 $payment_data = $paymentQuery->fetchAll(PDO::FETCH_ASSOC);

// ── Top Products ────────────────────────────────────────────
 $topQuery = $pdo->prepare("
    SELECT 
        order_items.name AS product, 
        p.category, 
        SUM(order_items.qty) AS units, 
        SUM(order_items.line_total) AS revenue 
    FROM order_items 
    JOIN orders ON orders.id = order_items.order_id
    LEFT JOIN products p ON p.id = order_items.product_id
    $whereClause 
    GROUP BY order_items.name, p.category 
    ORDER BY revenue DESC 
    LIMIT 10
");
 $topQuery->execute($params);
 $top_products = $topQuery->fetchAll(PDO::FETCH_ASSOC);

// ── Monthly Trend (Last 12 Months — always full 12 months) ──
 $monthlyQuery = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') AS month, 
        SUM(total) AS revenue,
        COUNT(*) AS orders
    FROM orders 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m') 
    ORDER BY month ASC
");
 $monthly_trend = $monthlyQuery->fetchAll(PDO::FETCH_ASSOC);

// ── Export CSV ──────────────────────────────────────────────
if ($export === 'csv') {
    $filename = "alburhan-revenue-" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $out = fopen('php://output', 'w');
    // BOM for Excel UTF-8
    fprintf($out, chr(0xEF).chr(0xBB).chr(0xBF));

    fputcsv($out, ['--- REVENUE SUMMARY ---']);
    fputcsv($out, ['Total Revenue', 'RS ' . number_format($stats['total_revenue'], 2)]);
    fputcsv($out, ['Total Orders', $stats['total_orders']]);
    fputcsv($out, ['Avg Order Value', 'RS ' . number_format($stats['avg_order'], 2)]);
    fputcsv($out, []);

    fputcsv($out, ['--- MONTHLY TREND ---']);
    fputcsv($out, ['Month', 'Revenue (RS)', 'Orders']);
    foreach ($monthly_trend as $row) {
        fputcsv($out, [$row['month'], number_format($row['revenue'], 2), $row['orders']]);
    }
    fputcsv($out, []);

    fputcsv($out, ['--- TOP PRODUCTS ---']);
    fputcsv($out, ['Product', 'Category', 'Units Sold', 'Revenue (RS)']);
    foreach ($top_products as $p) {
        fputcsv($out, [
            $p['product'], 
            $p['category'] ?? 'N/A', 
            $p['units'], 
            number_format($p['revenue'], 2)
        ]);
    }
    fputcsv($out, []);

    fputcsv($out, ['--- PAYMENT METHODS ---']);
    fputcsv($out, ['Method', 'Count', 'Revenue (RS)']);
    foreach ($payment_data as $pm) {
        fputcsv($out, [
            $pm['payment_method'], 
            $pm['count'], 
            number_format($pm['revenue'], 2)
        ]);
    }

    fclose($out);
    exit;
}

// ── AJAX Response ───────────────────────────────────────────
if ($ajax) {
    header('Content-Type: application/json');
    echo json_encode([
        'stats'         => $stats,
        'monthly'       => $monthly_trend,
        'top_products'  => $top_products,
        'payment_data'  => $payment_data,
    ]);
    exit;
}

// FIX: Encode PHP data for JS initial load
 $initialData = json_encode([
    'stats'         => $stats,
    'monthly'       => $monthly_trend,
    'top_products'  => $top_products,
    'payment_data'  => $payment_data,
]);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Revenue • Al Burhan Admin</title>
  <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500;600&display=swap">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
  <link rel="stylesheet" href="admin.css"/>
  <link rel="stylesheet" href="order.css"/>
  <link rel="stylesheet" href="sidebar.css"/>
  <link rel="stylesheet" href="revenue.css"/>
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">
  <div class="topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle" onclick="Revenue.toggleSidebar()"><i class="fas fa-bars"></i></button>
      <div>
        <h2>REVENUE DASHBOARD</h2>
        <p>Real-time Financial Performance</p>
      </div>
    </div>
    <div class="topbar-right">
      <div class="topbar-date"><i class="fas fa-calendar"></i> <?= date('d M Y') ?></div>
      <button class="topbar-btn" onclick="Revenue.exportRevenueCSV()"><i class="fas fa-download"></i> Export</button>
      <button class="topbar-btn" onclick="window.print()"><i class="fas fa-print"></i> Print</button>
    </div>
  </div>

  <div class="page-content">

    <!-- FILTER BAR -->
    <div class="filter-bar">
      <select id="periodFilter">
        <option value="this_month" <?= $period==='this_month'?'selected':'' ?>>This Month</option>
        <option value="3months" <?= $period==='3months'?'selected':'' ?>>Last 3 Months</option>
        <option value="6months" <?= $period==='6months'?'selected':'' ?>>Last 6 Months</option>
        <option value="year" <?= $period==='year'?'selected':'' ?>>This Year</option>
        <option value="all" <?= $period==='all'?'selected':'' ?>>All Time</option>
      </select>
      
      <input type="date" id="dateStart" value="<?= htmlspecialchars($start_date ?? '') ?>">
      <span style="color:var(--text-muted);">to</span>
      <input type="date" id="dateEnd" value="<?= htmlspecialchars($end_date ?? '') ?>">
      <button id="applyDateFilter" class="topbar-btn"><i class="fas fa-filter"></i> Apply Range</button>
      <button class="topbar-btn" onclick="Revenue.clearFilters()" style="color:var(--red);"><i class="fas fa-times"></i> Clear</button>

      <input type="text" id="productSearch" placeholder="🔍 Search products..." class="filter-search">
    </div>

    <!-- Stats Cards -->
    <div class="stats-row">
      <div class="stat-card accent-gold">
        <div class="stat-icon"><i class="fas fa-coins"></i></div>
        <!-- FIX: data-key NOW on .stat-value -->
        <div class="stat-value" data-key="total_revenue">RS <?= number_format($stats['total_revenue'] ?? 0, 2) ?></div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-sub">
          <span style="color:var(--green-ok);"><i class="fas fa-truck"></i> Delivered: RS <?= number_format($stats['delivered_revenue'] ?? 0, 0) ?></span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="color:#00cc88"><i class="fas fa-calendar-month"></i></div>
        <div class="stat-value" data-key="this_month">RS <?= number_format($stats['this_month'] ?? 0, 2) ?></div>
        <div class="stat-label">This Month</div>
        <div class="stat-sub">Always current month</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-value" data-key="total_orders"><?= number_format($stats['total_orders'] ?? 0) ?></div>
        <div class="stat-label">Total Orders</div>
        <div class="stat-sub">
          <span style="color:var(--orange-warn);"><i class="fas fa-clock"></i> <?= $stats['pending_orders'] ?? 0 ?> pending</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-chart-line"></i></div>
        <div class="stat-value" data-key="avg_order">RS <?= number_format($stats['avg_order'] ?? 0, 2) ?></div>
        <div class="stat-label">Avg Order Value</div>
        <div class="stat-sub">
          <span style="color:var(--red);"><i class="fas fa-times-circle"></i> <?= $stats['cancelled_orders'] ?? 0 ?> cancelled</span>
        </div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="color:#7c8cf8;"><i class="fas fa-shipping-fast"></i></div>
        <div class="stat-value" data-key="total_shipping">RS <?= number_format($stats['total_shipping'] ?? 0, 2) ?></div>
        <div class="stat-label">Total Shipping</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="color:#f39c12;"><i class="fas fa-percentage"></i></div>
        <div class="stat-value" data-key="total_vat">RS <?= number_format($stats['total_vat'] ?? 0, 2) ?></div>
        <div class="stat-label">Total VAT Collected</div>
      </div>

      <div class="stat-card">
        <div class="stat-icon" style="color:#e05c5c;"><i class="fas fa-tags"></i></div>
        <div class="stat-value" data-key="total_discount">RS <?= number_format($stats['total_discount'] ?? 0, 2) ?></div>
        <div class="stat-label">Total Discounts</div>
      </div>
    </div>

    <!-- Charts Section -->
    <div class="charts-grid">
      <div class="panel chart-panel">
        <div class="section-label">Revenue Trend (Last 12 Months)</div>
        <div style="height: 380px; position:relative;">
          <canvas id="monthlyTrendChart"></canvas>
        </div>
      </div>

      <div class="panel chart-panel chart-panel-sm">
        <div class="section-label">Payment Methods</div>
        <div style="height: 380px; position:relative; display:flex; align-items:center; justify-content:center;">
          <canvas id="paymentChart"></canvas>
        </div>
      </div>
    </div>

    <!-- Top Products Table -->
    <div class="panel" style="margin-top:24px;">
      <div class="section-label">Top Selling Products</div>
      <div class="table-scroll">
        <table class="data-table orders-table" id="topProductsTable">
          <thead>
            <tr>
              <th data-sort="string" data-col="0">#</th>
              <th data-sort="string" data-col="1">Product</th>
              <th data-sort="string" data-col="2">Category</th>
              <th data-sort="number" data-col="3" style="text-align:center">Units Sold</th>
              <th data-sort="number" data-col="4" style="text-align:right">Revenue</th>
              <th style="text-align:right">Share</th>
            </tr>
          </thead>
          <tbody id="topProductsBody">
            <?php 
            $totalRevenue = $stats['total_revenue'] ?? 1;
            foreach ($top_products as $idx => $p): 
              $share = $totalRevenue > 0 ? (($p['revenue'] / $totalRevenue) * 100) : 0;
            ?>
            <tr>
              <td style="color:var(--gold); font-weight:600;"><?= $idx + 1 ?></td>
              <td><?= htmlspecialchars($p['product'] ?? 'N/A') ?></td>
              <td><span class="cat-badge"><?= htmlspecialchars($p['category'] ?? 'N/A') ?></span></td>
              <td style="text-align:center"><?= number_format($p['units'] ?? 0) ?></td>
              <td style="text-align:right; color:#d4af37; font-weight:600;">RS <?= number_format($p['revenue'] ?? 0, 2) ?></td>
              <td style="text-align:right;">
                <div class="share-bar-wrap">
                  <div class="share-bar" style="width:<?= min(100, $share) ?>%"></div>
                  <span class="share-text"><?= number_format($share, 1) ?>%</span>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($top_products)): ?>
            <tr>
              <td colspan="6" style="text-align:center; padding:40px; color:var(--text-muted);">
                <i class="fas fa-box-open" style="font-size:24px; display:block; margin-bottom:10px;"></i>
                No product data for this period
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</div>

<!-- FIX: Pass initial data from PHP to JS -->
<script>
  window.__revenueData = <?= $initialData ?>;
</script>
<script src="revenue.js"></script>
<div class="admin-toast" id="adminToast"></div>

</body>
</html>