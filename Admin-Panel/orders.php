<?php
// ============================================================
//  AL BURHAN STORE — orders.php (FULL DATABASE VERSION)
//  FIXED: Column names match actual orders + order_items tables
// ============================================================

session_start();
require_once 'config.php';

 $pdo = getDB();

// ── Handle AJAX POST Actions ───────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action   = $_POST['action'];
    $order_id = $_POST['order_id'] ?? '';

    try {
        if ($action === 'update_status' && $order_id) {
            $status = $_POST['status'] ?? '';
            $note   = $_POST['note'] ?? '';

            $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
            $stmt->execute([$status, $order_id]);

            // If note provided, update notes field
            if ($note) {
                $noteStmt = $pdo->prepare("UPDATE orders SET notes = CONCAT(IFNULL(notes,''), '\n', ?) WHERE id = ?");
                $noteStmt->execute([date('Y-m-d H:i') . ": " . $note, $order_id]);
            }

            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'delete' && $order_id) {
            // Delete order items first (FK constraint)
            $pdo->prepare("DELETE FROM order_items WHERE order_id = ?")->execute([$order_id]);
            // Then delete the order
            $pdo->prepare("DELETE FROM orders WHERE id = ?")->execute([$order_id]);
            echo json_encode(['success' => true]);
        } 
        elseif ($action === 'bulk_status' && !empty($_POST['ids'])) {
            $ids = $_POST['ids'];
            $status = $_POST['status'] ?? 'confirmed';
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $pdo->prepare("UPDATE orders SET status = ? WHERE id IN ($placeholders)")
                ->execute(array_merge([$status], $ids));
            echo json_encode(['success' => true]);
        }
        elseif ($action === 'bulk_delete' && !empty($_POST['ids'])) {
            $ids = $_POST['ids'];
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $pdo->prepare("DELETE FROM order_items WHERE order_id IN ($placeholders)")
                ->execute($ids);
            $pdo->prepare("DELETE FROM orders WHERE id IN ($placeholders)")
                ->execute($ids);
            echo json_encode(['success' => true]);
        }
        else {
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ── Filters ─────────────────────────────────────────────────
 $filter_status  = $_GET['status'] ?? 'all';
 $filter_date    = $_GET['date'] ?? '';
 $filter_payment = $_GET['payment'] ?? '';
 $search         = trim($_GET['search'] ?? '');
 $page           = max(1, (int)($_GET['page'] ?? 1));
 $per_page       = 10;

// ── Build Query ─────────────────────────────────────────────
// FIX: Use actual column names from the orders table
 $where = "WHERE 1=1";
 $params = [];

if ($filter_status !== 'all') {
    $where .= " AND o.status = ?";
    $params[] = $filter_status;
}
if ($filter_payment) {
    $where .= " AND o.payment_method = ?";
    $params[] = $filter_payment;
}
if ($search) {
    $where .= " AND (o.id LIKE ? OR o.firstname LIKE ? OR o.lastname LIKE ? OR o.email LIKE ? OR o.city LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like, $like, $like]);
}
if ($filter_date) {
    if ($filter_date === 'today') {
        $where .= " AND DATE(o.created_at) = CURDATE()";
    } elseif ($filter_date === 'week') {
        $where .= " AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
    } elseif ($filter_date === 'month') {
        $where .= " AND o.created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
    }
}

// Count total filtered
 $countSql = "SELECT COUNT(*) FROM orders o $where";
 $countStmt = $pdo->prepare($countSql);
 $countStmt->execute($params);
 $total_filtered = $countStmt->fetchColumn();

 $total_pages = max(1, ceil($total_filtered / $per_page));
 $page = min($page, $total_pages);

 $offset = ($page - 1) * $per_page;

// FIX: Fetch orders with product info from order_items
// Use a subquery to get the first product name and total qty for display
 $sql = "
    SELECT 
        o.*,
        CONCAT(o.firstname, ' ', o.lastname) AS customer,
        (
            SELECT oi.name 
            FROM order_items oi 
            WHERE oi.order_id = o.id 
            ORDER BY oi.id ASC 
            LIMIT 1
        ) AS product_name,
        (
            SELECT oi.img 
            FROM order_items oi 
            WHERE oi.order_id = o.id 
            ORDER BY oi.id ASC 
            LIMIT 1
        ) AS product_img,
        (
            SELECT SUM(oi.qty) 
            FROM order_items oi 
            WHERE oi.order_id = o.id
        ) AS total_qty,
        (
            SELECT COUNT(*) 
            FROM order_items oi 
            WHERE oi.order_id = o.id
        ) AS item_count
    FROM orders o
    $where 
    ORDER BY o.created_at DESC, o.id DESC 
    LIMIT $offset, $per_page
";

 $stmt = $pdo->prepare($sql);
 $stmt->execute($params);
 $orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Statistics ──────────────────────────────────────────────
 $stats = [
    'total'     => $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn(),
    'pending'   => $pdo->query("SELECT COUNT(*) FROM orders WHERE status='pending'")->fetchColumn(),
    'confirmed' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status='confirmed'")->fetchColumn(),
    'shipped'   => $pdo->query("SELECT COUNT(*) FROM orders WHERE status='shipped'")->fetchColumn(),
    'delivered' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status='delivered'")->fetchColumn(),
    'cancelled' => $pdo->query("SELECT COUNT(*) FROM orders WHERE status='cancelled'")->fetchColumn(),
];

 $stats['revenue'] = $pdo->query("SELECT COALESCE(SUM(total), 0) FROM orders")->fetchColumn();

// ── Status Badge Helper ─────────────────────────────────────
function statusBadge($status) {
    $map = [
        'pending'   => ['badge-pending',   'fa-clock',        'Pending'],
        'confirmed' => ['badge-confirmed', 'fa-check',        'Confirmed'],
        'shipped'   => ['badge-shipped',   'fa-truck',        'Shipped'],
        'delivered' => ['badge-delivered', 'fa-check-circle', 'Delivered'],
        'cancelled' => ['badge-cancelled', 'fa-times-circle', 'Cancelled'],
    ];
    [$cls, $ico, $label] = $map[$status] ?? ['', 'fa-question', htmlspecialchars($status)];
    return "<span class='badge $cls'><i class='fas $ico'></i> $label</span>";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Orders — Al Burhan Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <link rel="stylesheet" href="admin.css"/>
  <link rel="stylesheet" href="order.css"/>
  <link rel="stylesheet" href="sidebar.css"/>
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

  <!-- TOPBAR -->
  <div class="topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
      </button>
      <div>
        <h2>ORDERS</h2>
        <p>Manage and track all customer orders</p>
      </div>
    </div>
    <div class="topbar-right">
      <div class="topbar-date"><i class="fas fa-calendar-alt"></i> <?= date('d M Y') ?></div>
      <button class="topbar-btn" onclick="Order.exportCSV()"><i class="fas fa-download"></i> Export CSV</button>
      <button class="topbar-btn" onclick="Order.printAll()"><i class="fas fa-print"></i> Print</button>
      <button class="topbar-btn primary" onclick="Order.openCreate()"><i class="fas fa-plus"></i> New Order</button>
    </div>
  </div>

  <div class="page-content">

    <!-- STATS ROW -->
    <div class="stats-row">
      <div class="stat-card accent-gold">
        <div class="stat-icon"><i class="fas fa-coins"></i></div>
        <div class="stat-value">RS <?= number_format($stats['revenue']) ?></div>
        <div class="stat-label">Total Revenue</div>
        <div class="stat-change up"><i class="fas fa-arrow-up"></i> 22%</div>
      </div>

      <div class="stat-card" onclick="window.location='?status=all'">
        <div class="stat-icon"><i class="fas fa-shopping-bag"></i></div>
        <div class="stat-value"><?= $stats['total'] ?></div>
        <div class="stat-label">Total Orders</div>
      </div>

      <div class="stat-card" onclick="window.location='?status=pending'">
        <div class="stat-icon" style="color:var(--orange-warn);"><i class="fas fa-clock"></i></div>
        <div class="stat-value" style="color:var(--orange-warn);"><?= $stats['pending'] ?></div>
        <div class="stat-label">Pending</div>
      </div>

      <div class="stat-card" onclick="window.location='?status=shipped'">
        <div class="stat-icon" style="color:var(--blue-info);"><i class="fas fa-truck"></i></div>
        <div class="stat-value" style="color:var(--blue-info);"><?= $stats['shipped'] ?></div>
        <div class="stat-label">Shipped</div>
      </div>

      <div class="stat-card" onclick="window.location='?status=delivered'">
        <div class="stat-icon" style="color:var(--green-ok);"><i class="fas fa-check-circle"></i></div>
        <div class="stat-value" style="color:var(--green-ok);"><?= $stats['delivered'] ?></div>
        <div class="stat-label">Delivered</div>
      </div>

      <div class="stat-card" onclick="window.location='?status=cancelled'">
        <div class="stat-icon" style="color:var(--red);"><i class="fas fa-times-circle"></i></div>
        <div class="stat-value" style="color:var(--red);"><?= $stats['cancelled'] ?></div>
        <div class="stat-label">Cancelled</div>
      </div>
    </div>

    <div class="panel">

      <!-- Status Tabs -->
      <div class="status-tabs">
        <?php
        $tabs = [
          'all'       => "All ({$stats['total']})",
          'pending'   => "Pending ({$stats['pending']})",
          'confirmed' => "Confirmed ({$stats['confirmed']})",
          'shipped'   => "Shipped ({$stats['shipped']})",
          'delivered' => "Delivered ({$stats['delivered']})",
          'cancelled' => "Cancelled ({$stats['cancelled']})",
        ];
        foreach ($tabs as $key => $label):
          $active = $filter_status === $key ? 'active' : '';
        ?>
          <a href="?status=<?= $key ?>" class="status-tab <?= $active ?>"><?= $label ?></a>
        <?php endforeach; ?>
      </div>

      <!-- Filter Bar -->
      <form method="GET" class="orders-filter-bar" id="filterForm">
        <input type="hidden" name="status" value="<?= htmlspecialchars($filter_status) ?>"/>
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search order ID, customer, email, city..." 
                 value="<?= htmlspecialchars($search) ?>"/>
          <?php if($search): ?>
            <a href="?" class="clear-search"><i class="fas fa-times"></i></a>
          <?php endif; ?>
        </div>

        <select name="date" class="filter-select" onchange="this.form.submit()">
          <option value="">All Dates</option>
          <option value="today" <?= $filter_date==='today'?'selected':'' ?>>Today</option>
          <option value="week" <?= $filter_date==='week'?'selected':'' ?>>This Week</option>
          <option value="month" <?= $filter_date==='month'?'selected':'' ?>>This Month</option>
        </select>

        <select name="payment" class="filter-select" onchange="this.form.submit()">
          <option value="">All Payments</option>
          <option value="card" <?= $filter_payment==='card'?'selected':'' ?>>Credit Card</option>
          <option value="cod" <?= $filter_payment==='cod'?'selected':'' ?>>Cash on Delivery</option>
          <option value="bank" <?= $filter_payment==='bank'?'selected':'' ?>>Bank Transfer</option>
        </select>

        <button type="submit" class="topbar-btn"><i class="fas fa-filter"></i> Filter</button>
        <?php if($search || $filter_date || $filter_payment || $filter_status !== 'all'): ?>
          <a href="orders.php" class="topbar-btn" style="color:var(--red);">Clear</a>
        <?php endif; ?>
      </form>

      <!-- Bulk Bar -->
      <div class="bulk-bar" id="bulkBar" style="display:none;">
        <span class="bulk-info"><span id="bulkCount">0</span> orders selected</span>
        <div class="gap-row">
          <button class="topbar-btn" onclick="Order.bulkStatus('confirmed')">Mark Confirmed</button>
          <button class="topbar-btn" onclick="Order.bulkStatus('shipped')">Mark Shipped</button>
          <button class="topbar-btn" onclick="Order.bulkStatus('delivered')">Mark Delivered</button>
          <button class="topbar-btn" style="color:var(--red);" onclick="Order.bulkDelete()">Delete Selected</button>
        </div>
      </div>

      <!-- Results Meta -->
      <div class="results-meta">
        <span>Showing <strong><?= count($orders) ?></strong> of <strong><?= $total_filtered ?></strong> orders</span>
        <div class="sort-wrap">
          <label>Sort:</label>
          <select class="filter-select" id="sortSelect">
            <option value="date-desc">Newest First</option>
            <option value="date-asc">Oldest First</option>
            <option value="total-desc">Highest Value</option>
          </select>
        </div>
      </div>

      <!-- Table -->
      <div class="table-scroll">
        <table class="data-table orders-table" id="ordersTable">
          <thead>
            <tr>
              <th class="col-check"><input type="checkbox" id="checkAll" onchange="Order.toggleAll(this)"/></th>
              <th class="col-id">Order ID</th>
              <th class="col-customer">Customer</th>
              <th class="col-product">Product</th>
              <th class="col-total">Total</th>
              <th class="col-payment">Payment</th>
              <th class="col-date">Date</th>
              <th class="col-status">Status</th>
              <th class="col-actions">Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($orders)): ?>
              <tr>
                <td colspan="9" class="empty-state">
                  <i class="fas fa-search"></i>
                  <div>No orders found</div>
                </td>
              </tr>
            <?php else: ?>
              <?php foreach ($orders as $o): ?>
                <?php
                  // FIX: Build display values from actual columns
                  $order_ref = "ALB-2026-" . str_pad($o['id'], 5, "0", STR_PAD_LEFT);
                  $customer_name = trim(($o['firstname'] ?? '') . ' ' . ($o['lastname'] ?? ''));
                  $customer_initials = strtoupper(substr($o['firstname'] ?? '?', 0, 1) . substr($o['lastname'] ?? '', 0, 1));
                  $product_display = $o['product_name'] ?? 'N/A';
                  $item_count = intval($o['item_count'] ?? 0);
                  $total_qty = intval($o['total_qty'] ?? 0);
                  
                  // Product meta line
                  $product_meta_parts = [];
                  if ($item_count > 1) {
                      $product_meta_parts[] = $item_count . " items";
                  }
                  if ($total_qty > 1) {
                      $product_meta_parts[] = "Qty: " . $total_qty;
                  }
                  $product_meta = !empty($product_meta_parts) ? implode(' · ', $product_meta_parts) : "Qty: 1";

                  // Payment display
                  $payment_map = [
                      'card' => 'Credit Card',
                      'cod'  => 'Cash on Delivery',
                      'bank' => 'Bank Transfer',
                  ];
                  $payment_display = $payment_map[$o['payment_method'] ?? ''] ?? ucfirst($o['payment_method'] ?? 'N/A');

                  // Date
                  $date_display = !empty($o['created_at']) && $o['created_at'] !== '0000-00-00 00:00:00'
                      ? date('d M', strtotime($o['created_at']))
                      : '—';
                  $year_display = !empty($o['created_at']) && $o['created_at'] !== '0000-00-00 00:00:00'
                      ? date('Y', strtotime($o['created_at']))
                      : '';
                ?>
                <tr class="order-row" data-id="<?= htmlspecialchars($o['id']) ?>" data-status="<?= htmlspecialchars($o['status'] ?? 'pending') ?>">
                  <td class="col-check">
                    <input type="checkbox" class="row-check" value="<?= $o['id'] ?>" onchange="Order.onRowCheck()"/>
                  </td>
                  <td class="col-id"><span class="order-id"><?= htmlspecialchars($order_ref) ?></span></td>
                  <td class="col-customer">
                    <div class="customer-cell">
                      <div class="cust-avatar"><?= $customer_initials ?></div>
                      <div>
                        <div class="cust-name"><?= htmlspecialchars($customer_name) ?></div>
                        <div class="cust-email"><?= htmlspecialchars($o['email'] ?? '') ?></div>
                        <div class="cust-city"><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($o['city'] ?? '') ?></div>
                      </div>
                    </div>
                  </td>
                  <td class="col-product">
                    <div class="product-cell">
                      <div class="prod-icon">
                        <?php if (!empty($o['product_img'])): ?>
                          <img src="<?= htmlspecialchars($o['product_img']) ?>" alt="" style="width:32px;height:32px;border-radius:6px;object-fit:cover;">
                        <?php else: ?>
                          <i class="fas fa-spray-can"></i>
                        <?php endif; ?>
                      </div>
                      <div>
                        <div class="prod-name"><?= htmlspecialchars($product_display) ?></div>
                        <div class="prod-meta"><?= $product_meta ?></div>
                      </div>
                    </div>
                  </td>
                  <td class="col-total">
                    <span class="order-total">RS <?= number_format($o['total'] ?? 0, 2) ?></span>
                    <?php if (isset($o['subtotal'])): ?>
                      <div class="unit-price">Sub: RS <?= number_format($o['subtotal'], 0) ?></div>
                    <?php endif; ?>
                  </td>
                  <td class="col-payment">
                    <div class="payment-cell">
                      <i class="fas fa-credit-card"></i> <?= htmlspecialchars($payment_display) ?>
                    </div>
                  </td>
                  <td class="col-date">
                    <div class="date-cell">
                      <?= $date_display ?>
                      <span><?= $year_display ?></span>
                    </div>
                  </td>
                  <td class="col-status"><?= statusBadge($o['status'] ?? 'pending') ?></td>
                  <td class="col-actions">
                    <div class="row-actions">
                      <button class="action-btn view-btn" title="View Details"
                              onclick="Order.view(<?= $o['id'] ?>)"><i class="fas fa-eye"></i></button>
                      <button class="action-btn edit-btn" title="Edit Status"
                              onclick="Order.editStatus('<?= $o['id'] ?>','<?= $o['status'] ?? 'pending' ?>')"><i class="fas fa-edit"></i></button>
                      <button class="action-btn print-btn" title="Print Invoice"
                              onclick="Order.printInvoice(<?= $o['id'] ?>)"><i class="fas fa-print"></i></button>
                      <button class="action-btn delete-btn" title="Delete"
                              onclick="Order.delete('<?= $o['id'] ?>')"><i class="fas fa-trash"></i></button>
                    </div>
                  </td>
                </tr>
              <?php endforeach; ?>
            <?php endif; ?>
          </tbody>
        </table>
      </div>

      <!-- Pagination -->
      <?php if ($total_pages > 1): ?>
      <div class="pagination">
        <span class="page-info">Page <?= $page ?> of <?= $total_pages ?></span>
        <div class="page-btns">
          <?php if ($page > 1): ?>
            <a href="?page=<?= $page-1 ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>&date=<?= $filter_date ?>&payment=<?= $filter_payment ?>" class="page-btn"><i class="fas fa-chevron-left"></i></a>
          <?php endif; ?>

          <?php for($i = max(1, $page-2); $i <= min($total_pages, $page+2); $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>&date=<?= $filter_date ?>&payment=<?= $filter_payment ?>" class="page-btn <?= $i==$page?'active':'' ?>"><?= $i ?></a>
          <?php endfor; ?>

          <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page+1 ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>&date=<?= $filter_date ?>&payment=<?= $filter_payment ?>" class="page-btn"><i class="fas fa-chevron-right"></i></a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<!-- ════════════════════════════════════════ MODALS -->

<!-- View Order Modal -->
<div class="modal-overlay" id="viewModal" onclick="if(event.target===this)Order.closeModal('viewModal')">
  <div class="order-modal" id="viewModalBox">
    <div class="modal-head">
      <div>
        <div class="modal-order-id" id="vm_id"></div>
        <div class="modal-order-sub" id="vm_date"></div>
      </div>
      <div class="gap-row">
        <span id="vm_status"></span>
        <button class="modal-close-btn" onclick="Order.closeModal('viewModal')"><i class="fas fa-times"></i></button>
      </div>
    </div>
    <div class="modal-body" id="viewModalBody">
      <!-- Filled via AJAX -->
      <div style="text-align:center;padding:40px;"><i class="fas fa-circle-notch fa-spin" style="font-size:24px;color:var(--gold);"></i></div>
    </div>
  </div>
</div>

<!-- Edit Status Modal -->
<div class="modal-overlay" id="editModal" onclick="if(event.target===this)Order.closeModal('editModal')">
  <div class="order-modal order-modal-sm" id="editModalBox">
    <div class="modal-head">
      <div class="modal-order-id">Update Order Status</div>
      <button class="modal-close-btn" onclick="Order.closeModal('editModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <input type="hidden" id="edit_order_id"/>
      <div class="form-group" style="margin-bottom:20px;">
        <label class="form-label">Order ID</label>
        <input class="form-input" id="edit_display_id" readonly style="opacity:0.6;cursor:not-allowed;"/>
      </div>
      <div class="form-group" style="margin-bottom:24px;">
        <label class="form-label">New Status</label>
        <select class="form-select" id="edit_status_select">
          <option value="pending">Pending</option>
          <option value="confirmed">Confirmed</option>
          <option value="shipped">Shipped</option>
          <option value="delivered">Delivered</option>
          <option value="cancelled">Cancelled</option>
        </select>
      </div>
      <div class="form-group" style="margin-bottom:0;">
        <label class="form-label">Note (optional)</label>
        <textarea class="form-textarea" id="edit_note" placeholder="Add an internal note about this update…" style="min-height:70px;"></textarea>
      </div>
    </div>
    <div class="modal-foot">
      <button class="topbar-btn" onclick="Order.closeModal('editModal')">Cancel</button>
      <button class="topbar-btn primary" onclick="Order.saveStatus()"><i class="fas fa-save"></i> Save Status</button>
    </div>
  </div>
</div>

<!-- Create Order Modal -->
<div class="modal-overlay" id="createModal" onclick="if(event.target===this)Order.closeModal('createModal')">
  <div class="order-modal order-modal-lg" id="createModalBox">
    <div class="modal-head">
      <div class="modal-order-id">Create New Order</div>
      <button class="modal-close-btn" onclick="Order.closeModal('createModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body">
      <div class="section-label">Customer Details</div>
      <div class="form-grid" style="margin-bottom:20px;">
        <div class="form-group">
          <label class="form-label">Full Name</label>
          <input class="form-input" type="text" id="create_name" placeholder="Customer full name"/>
        </div>
        <div class="form-group">
          <label class="form-label">Email</label>
          <input class="form-input" type="email" id="create_email" placeholder="customer@email.com"/>
        </div>
        <div class="form-group">
          <label class="form-label">Phone</label>
          <input class="form-input" type="text" id="create_phone" placeholder="+971 50 000 0000"/>
        </div>
        <div class="form-group">
          <label class="form-label">City</label>
          <input class="form-input" type="text" id="create_city" placeholder="Dubai"/>
        </div>
      </div>
      <div class="section-label">Order Details</div>
      <div class="form-grid" style="margin-bottom:20px;">
        <div class="form-group">
          <label class="form-label">Product</label>
          <select class="form-select" id="create_product">
            <option value="">Select product…</option>
            <?php
            // FIX: Load real products from database
            try {
                $products = $pdo->query("SELECT id, name, price FROM products ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
                foreach ($products as $p) {
                    echo "<option value='{$p['id']}'>" . htmlspecialchars($p['name']) . " — RS " . number_format($p['price'], 0) . "</option>";
                }
            } catch (Exception $e) {}
            ?>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Quantity</label>
          <input class="form-input" type="number" id="create_qty" value="1" min="1"/>
        </div>
        <div class="form-group">
          <label class="form-label">Payment Method</label>
          <select class="form-select" id="create_payment">
            <option value="card">Credit Card</option>
            <option value="cod">Cash on Delivery</option>
            <option value="bank">Bank Transfer</option>
          </select>
        </div>
        <div class="form-group">
          <label class="form-label">Initial Status</label>
          <select class="form-select" id="create_status">
            <option value="pending">Pending</option>
            <option value="confirmed">Confirmed</option>
          </select>
        </div>
        <div class="form-group full">
          <label class="form-label">Notes</label>
          <textarea class="form-textarea" id="create_notes" placeholder="Delivery instructions, gift message…" style="min-height:70px;"></textarea>
        </div>
      </div>
    </div>
    <div class="modal-foot">
      <button class="topbar-btn" onclick="Order.closeModal('createModal')">Cancel</button>
      <button class="topbar-btn primary" onclick="Order.createOrder()"><i class="fas fa-plus"></i> Create Order</button>
    </div>
  </div>
</div>

<!-- Confirm Delete Modal -->
<div class="modal-overlay" id="deleteModal" onclick="if(event.target===this)Order.closeModal('deleteModal')">
  <div class="order-modal order-modal-sm" id="deleteModalBox">
    <div class="modal-head">
      <div class="modal-order-id" style="color:var(--red);">Confirm Deletion</div>
      <button class="modal-close-btn" onclick="Order.closeModal('deleteModal')"><i class="fas fa-times"></i></button>
    </div>
    <div class="modal-body" style="text-align:center;padding:32px 28px;">
      <div style="font-size:48px;color:var(--red);opacity:0.7;margin-bottom:16px;"><i class="fas fa-exclamation-triangle"></i></div>
      <div style="font-size:15px;margin-bottom:8px;">Are you sure you want to delete</div>
      <div style="font-family:var(--font-display);color:var(--gold);font-size:18px;margin-bottom:16px;" id="delete_display_id"></div>
      <div style="font-size:12px;color:var(--text-muted);">This action cannot be undone.</div>
    </div>
    <div class="modal-foot">
      <button class="topbar-btn" onclick="Order.closeModal('deleteModal')">Cancel</button>
      <button class="topbar-btn" style="background:var(--red);color:#fff;border-color:var(--red);" onclick="Order.confirmDelete()">
        <i class="fas fa-trash"></i> Delete Permanently
      </button>
    </div>
  </div>
</div>

<div class="admin-toast" id="adminToast"></div>

<script src="order.js"></script>
</body>
</html>