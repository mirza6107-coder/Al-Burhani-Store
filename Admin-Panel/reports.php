<?php
// ============================================================
//  AL BURHAN STORE — reports.php (FIXED + PROFESSIONAL)
// ============================================================

session_start();
require_once 'config.php';

$pdo = getDB();

// ── Key Metrics ─────────────────────────────────────────────
$reports = [
    'total_revenue'    => $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders")->fetchColumn() ?: 0,
    'this_month'       => $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE created_at >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetchColumn() ?: 0,
    'total_orders'     => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0,
    'avg_order_value'  => $pdo->query("SELECT COALESCE(AVG(total), 0) FROM orders")->fetchColumn() ?: 0,
    'total_customers'  => $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn() ?: 0,
    'repeat_customers' => $pdo->query("
        SELECT COUNT(DISTINCT CONCAT(firstname, ' ', lastname)) 
        FROM orders 
        GROUP BY CONCAT(firstname, ' ', lastname) 
        HAVING COUNT(*) > 1
    ")->rowCount() ?: 0,
];

// ── Monthly Report ─────────────────────────────────────────
$monthly_report = $pdo->query("
    SELECT 
        DATE_FORMAT(created_at, '%Y-%m') as month,
        COUNT(*) as orders,
        COALESCE(SUM(total), 0) as revenue,
        COALESCE(AVG(total), 0) as avg_order
    FROM orders 
    WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
    GROUP BY DATE_FORMAT(created_at, '%Y-%m')
    ORDER BY month DESC
")->fetchAll(PDO::FETCH_ASSOC);

// ── Top Products ───────────────────────────────────────────
$top_products = $pdo->query("
    SELECT 
        p.name, 
        p.category, 
        p.price,
        p.image,
        COALESCE(SUM(oi.qty), 0) as units_sold,
        COALESCE(SUM(oi.line_total), 0) as revenue
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    GROUP BY p.id, p.name, p.category, p.price, p.image
    ORDER BY revenue DESC 
    LIMIT 10
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Reports • Al Burhan Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="sidebar.css"/>
    <link rel="stylesheet" href="dashboard.css"/>
    <link rel="stylesheet" href="revenue.css"/>
    <link rel="stylesheet" href="reports.css"/>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
</head>
<body>

<div class="admin-container">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <!-- Topbar -->
        <header class="admin-header">
            <div class="admin-header-left">
                <h2>REPORTS & ANALYTICS</h2>
                <p>Business Performance Overview</p>
            </div>
            <div class="admin-header-right">
                <button class="topbar-btn" onclick="exportAllReports()"><i class="fas fa-download"></i> Export All</button>
                <button class="topbar-btn primary" onclick="window.print()"><i class="fas fa-print"></i> Print Report</button>
            </div>
        </header>

        <!-- Stats Overview -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-coins"></i></div>
                <div class="stat-info">
                    <p>Total Revenue</p>
                    <h3>RS <?= number_format($reports['total_revenue']) ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-info">
                    <p>Total Orders</p>
                    <h3><?= number_format($reports['total_orders']) ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <p>Customers</p>
                    <h3><?= number_format($reports['total_customers']) ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-repeat"></i></div>
                <div class="stat-info">
                    <p>Repeat Customers</p>
                    <h3><?= number_format($reports['repeat_customers']) ?></h3>
                </div>
            </div>
        </div>

        <!-- Monthly Trend -->
        <div class="section">
            <div class="section-header">
                <div class="section-header-left">
                    <span class="section-gem">◆</span>
                    <h3>Monthly Performance Trend</h3>
                </div>
            </div>
            <div style="height: 380px; background:var(--panel); padding:20px; border-radius:4px; border:1px solid var(--gold-border);">
                <canvas id="monthlyChart"></canvas>
            </div>
        </div>

        <!-- Top Products -->
        <div class="section">
            <div class="section-header">
                <div class="section-header-left">
                    <span class="section-gem">◆</span>
                    <h3>Top Performing Products</h3>
                </div>
                <a href="products.php" class="view-all">View All Products →</a>
            </div>

            <div class="top-selling-grid">
                <?php if (empty($top_products)): ?>
                    <p style="grid-column: 1/-1; text-align:center; padding:60px; color:#aaa;">No sales data available yet.</p>
                <?php else: ?>
                    <?php foreach ($top_products as $i => $p): ?>
                    <div class="top-product-card">
                        <div class="rank-badge">#<?= $i+1 ?></div>
                        
                        <div class="top-product-img">
                            <img src="<?= !empty($p['image']) ? htmlspecialchars($p['image']) : 'https://via.placeholder.com/300x200/1a1a1a/d4af37?text='.substr($p['name'],0,1) ?>" 
                                 alt="<?= htmlspecialchars($p['name']) ?>">
                        </div>
                        
                        <div class="top-product-info">
                            <h3><?= htmlspecialchars($p['name']) ?></h3>
                            <span class="category-tag"><?= htmlspecialchars($p['category'] ?? 'Premium') ?></span>
                            <div class="price-row">
                                <span class="price">RS <?= number_format($p['price']) ?></span>
                                <span class="sales-count"><?= $p['units_sold'] ?> sold</span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script src="reports.js"></script>
</body>
</html>