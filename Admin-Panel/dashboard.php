<?php
// ============================================================
//  AL BURHAN STORE — dashboard.php (FIXED + PROFESSIONAL)
// ============================================================

session_start();
require_once 'config.php';

$pdo = getDB();

// ── Real Statistics ─────────────────────────────────────
$stats = [
    'total_products' => $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn() ?: 0,
    'total_orders'   => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn() ?: 0,
    'revenue_30d'    => $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)")->fetchColumn() ?: 0,
    'total_customers'=> $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn() ?: 0,
    'pending_orders' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn() ?: 0,
    'low_stock'      => $pdo->query("SELECT COUNT(*) FROM products WHERE stock < 10")->fetchColumn() ?: 0,
];

// ── Recent Orders (Fixed column names) ─────────────────────
$recent_orders = $pdo->query("
    SELECT 
        id, 
        CONCAT(firstname, ' ', lastname) AS customer,
        total, 
        status, 
        created_at 
    FROM orders 
    ORDER BY created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// ── Top Products with Image ────────────────────────────────────────
$top_products = $pdo->query("
    SELECT 
        p.id,
        p.name, 
        p.category, 
        p.price, 
        p.stock,
        p.image,
        COALESCE(SUM(oi.qty), 0) as total_sold
    FROM products p
    LEFT JOIN order_items oi ON p.id = oi.product_id
    GROUP BY p.id, p.name, p.category, p.price, p.stock, p.image
    ORDER BY total_sold DESC, p.stock DESC 
    LIMIT 8
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Dashboard • Al Burhan Admin</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@300;400;500&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500;600&display=swap">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    <link rel="stylesheet" href="sidebar.css"/>
    <link rel="stylesheet" href="dashboard.css"/>
    <link rel="stylesheet" href="revenue.css">
</head>
<body>

<div class="admin-container">
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <!-- Header -->
        <header class="admin-header">
            <div class="admin-header-left">
                <h2>DASHBOARD</h2>
                <p>Welcome back — Real-time Overview</p>
            </div>
            <div class="admin-header-right">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="globalSearch" placeholder="Quick search..." />
                </div>
                <button class="notif-btn"><i class="fas fa-bell"></i><span class="notif-dot"></span></button>
                <a href="add-products.php" class="btn-add"><i class="fas fa-plus"></i> Add Product</a>
            </div>
        </header>

        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-info">
                    <p>Total Products</p>
                    <h3 class="stat-number" data-target="<?= $stats['total_products'] ?>"><?= number_format($stats['total_products']) ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
                <div class="stat-info">
                    <p>Total Orders</p>
                    <h3 class="stat-number" data-target="<?= $stats['total_orders'] ?>"><?= number_format($stats['total_orders']) ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-coins"></i></div>
                <div class="stat-info">
                    <p>Revenue (30 Days)</p>
                    <h3>RS <?= number_format($stats['revenue_30d']) ?></h3>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-info">
                    <p>Customers</p>
                    <h3 class="stat-number" data-target="<?= $stats['total_customers'] ?>"><?= number_format($stats['total_customers']) ?></h3>
                </div>
            </div>
        </div>

        <div class="section">
            <div class="section-header">
                <div class="section-header-left">
                    <span class="section-gem">◆</span>
                    <div>
                        <h3>Recent Orders</h3>
                        <p>Latest customer purchases</p>
                    </div>
                </div>
                <a href="orders.php" class="view-all">View All Orders →</a>
            </div>

            <div class="products-table">
                <table>
                    <thead>
                        <tr>
                            <th>Order ID</th>
                            <th>Customer</th>
                            <th>Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recent_orders)): ?>
                            <tr><td colspan="5" style="text-align:center; padding:40px;">No recent orders</td></tr>
                        <?php else: ?>
                            <?php foreach ($recent_orders as $o): ?>
                            <tr>
                                <td><strong>#<?= $o['id'] ?></strong></td>
                                <td><?= htmlspecialchars($o['customer']) ?></td>
                                <td class="price-cell">RS <?= number_format($o['total']) ?></td>
                                <td><?= date('d M Y', strtotime($o['created_at'])) ?></td>
                                <td><?= statusBadge($o['status']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

                 <!-- Top Selling Products - Enhanced UI with Images -->
        <div class="section">
            <div class="section-header">
                <div class="section-header-left">
                    <span class="section-gem">◆</span>
                    <div>
                        <h3>Top Selling Products</h3>
                        <p>Best performers this period</p>
                    </div>
                </div>
                <a href="products.php" class="view-all">View Full Inventory →</a>
            </div>

            <div class="top-selling-grid">
                <?php if (empty($top_products)): ?>
                    <p style="grid-column: 1/-1; text-align:center; padding:60px; color:#aaa;">No sales data available yet.</p>
                <?php else: ?>
                    <?php foreach ($top_products as $index => $p): 
                        $sold = (int)$p['total_sold'];
                        $stock = (int)$p['stock'];
                        $stockPercentage = $stock > 0 ? min(100, round(($stock / max(50, $stock)) * 100)) : 0;
                        $imagePath = !empty($p['image']) ? $p['image'] : 'https://via.placeholder.com/300x200/1a1a1a/d4af37?text=' . urlencode(substr($p['name'],0,1));
                    ?>
                    <div class="top-product-card">
                        <div class="rank-badge">#<?= $index + 1 ?></div>
                        
                        <div class="top-product-img">
                            <img src="<?= htmlspecialchars($imagePath) ?>" 
                                 alt="<?= htmlspecialchars($p['name']) ?>"
                                 onerror="this.src='https://via.placeholder.com/300x200/1a1a1a/d4af37?text=<?= urlencode(substr($p['name'],0,1)) ?>';">
                        </div>
                        
                        <div class="top-product-info">
                            <h3><?= htmlspecialchars($p['name']) ?></h3>
                            <span class="category-tag"><?= htmlspecialchars($p['category'] ?? 'Premium') ?></span>
                            
                            <div class="price-row">
                                <span class="price">RS <?= number_format($p['price']) ?></span>
                                <span class="sales-count"><?= $sold ?> sold</span>
                            </div>
                            
                            <div class="stock-bar">
                                <div class="stock-fill" style="width: <?= $stockPercentage ?>%; background: <?= $stock < 15 ? '#fbbf24' : '#4ade80' ?>;"></div>
                            </div>
                            <div class="stock-text">
                                Stock: <strong><?= $stock ?></strong>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
</div>

<script src="dashboard.js"></script>
</body>
</html>

<?php
function statusBadge($status) {
    $map = [
        'pending'   => ['badge-pending', 'Pending'],
        'confirmed' => ['badge-confirmed', 'Confirmed'],
        'shipped'   => ['badge-shipped', 'Shipped'],
        'delivered' => ['badge-delivered', 'Delivered'],
        'cancelled' => ['badge-cancelled', 'Cancelled'],
    ];
    [$cls, $label] = $map[$status] ?? ['badge-pending', ucfirst($status)];
    return "<span class='status $cls'>$label</span>";
}
?>