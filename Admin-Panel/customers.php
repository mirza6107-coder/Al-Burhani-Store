<?php
// ============================================================
//  AL BURHAN STORE — customers.php
// ============================================================

session_start();
require_once 'config.php';

$pdo = getDB();

// ── Handle AJAX Actions ─────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    header('Content-Type: application/json');
    $action = $_POST['action'];
    $id = $_POST['id'] ?? '';

    try {
        if ($action === 'update_status' && $id) {
            $status = $_POST['status'] ?? 'active';
            $stmt = $pdo->prepare("UPDATE customers SET status = ? WHERE id = ?");
            $stmt->execute([$status, $id]);
            echo json_encode(['success' => true]);
        } elseif ($action === 'delete' && $id) {
            $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success' => true]);
        }
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'message' => $e->getMessage()]);
    }
    exit;
}

// ── Filters ─────────────────────────────────────────────────
$search       = trim($_GET['search'] ?? '');
$filter_status = $_GET['status'] ?? 'all';
$page         = max(1, (int)($_GET['page'] ?? 1));
$per_page     = 12;

// ── Query ───────────────────────────────────────────────────
$where = "WHERE 1=1";
$params = [];

if ($filter_status !== 'all') {
    $where .= " AND status = ?";
    $params[] = $filter_status;
}
if ($search) {
    $where .= " AND (name LIKE ? OR email LIKE ? OR customer_id LIKE ?)";
    $like = "%$search%";
    $params = array_merge($params, [$like, $like, $like]);
}

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM customers $where");
$countStmt->execute($params);
$total_filtered = $countStmt->fetchColumn();

$total_pages = max(1, ceil($total_filtered / $per_page));
$page = min($page, $total_pages);

$limit = " LIMIT " . (($page-1)*$per_page) . ", $per_page";

$stmt = $pdo->prepare("SELECT * FROM customers $where ORDER BY joined_date DESC, name ASC $limit");
$stmt->execute($params);
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// ── Stats ───────────────────────────────────────────────────
$stats = [
    'total'     => $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn(),
    'active'    => $pdo->query("SELECT COUNT(*) FROM customers WHERE status='active'")->fetchColumn(),
    'new_this_month' => $pdo->query("SELECT COUNT(*) FROM customers WHERE joined_date >= DATE_FORMAT(CURDATE(), '%Y-%m-01')")->fetchColumn(),
    'total_spent' => $pdo->query("SELECT COALESCE(SUM(total_spent), 0) FROM customers")->fetchColumn(),
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8"/>
  <meta name="viewport" content="width=device-width,initial-scale=1.0"/>
  <title>Customers — Al Burhan Admin</title>
  <link rel="preconnect" href="https://fonts.googleapis.com"/>
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
  <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;1,300&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500;600&display=swap" rel="stylesheet"/>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css"/>
  <link rel="stylesheet" href="admin.css"/>
  <link rel="stylesheet" href="order.css"/>
  <link rel="stylesheet" href="sidebar.css">
</head>
<body>

<?php include 'sidebar.php'; ?>

<div class="main-content">

  <div class="topbar">
    <div class="topbar-left">
      <button class="sidebar-toggle" id="sidebarToggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
      </button>
      <div>
        <h2>CUSTOMERS</h2>
        <p>Manage customer database</p>
      </div>
    </div>
    <div class="topbar-right">
      <div class="topbar-date"><i class="fas fa-calendar-alt"></i> <?= date('d M Y') ?></div>
      <button class="topbar-btn" onclick="exportCustomersCSV()"><i class="fas fa-download"></i> Export CSV</button>
      <button class="topbar-btn primary" onclick="openCreateCustomer()"><i class="fas fa-plus"></i> New Customer</button>
    </div>
  </div>

  <div class="page-content">

    <!-- Stats -->
    <div class="stats-row">
      <div class="stat-card accent-gold">
        <div class="stat-icon"><i class="fas fa-users"></i></div>
        <div class="stat-value"><?= $stats['total'] ?></div>
        <div class="stat-label">Total Customers</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon" style="color:var(--green-ok);"><i class="fas fa-user-check"></i></div>
        <div class="stat-value"><?= $stats['active'] ?></div>
        <div class="stat-label">Active Customers</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-user-plus"></i></div>
        <div class="stat-value"><?= $stats['new_this_month'] ?></div>
        <div class="stat-label">New This Month</div>
      </div>
      <div class="stat-card">
        <div class="stat-icon"><i class="fas fa-hand-holding-dollar"></i></div>
        <div class="stat-value">RS <?= number_format($stats['total_spent']) ?></div>
        <div class="stat-label">Total Spent</div>
      </div>
    </div>

    <div class="panel">

      <!-- Filters -->
      <form method="GET" class="orders-filter-bar" id="filterForm">
        <div class="search-box">
          <i class="fas fa-search"></i>
          <input type="text" name="search" placeholder="Search by name, email or ID..." value="<?= htmlspecialchars($search) ?>"/>
        </div>

        <select name="status" class="filter-select" onchange="this.form.submit()">
          <option value="all" <?= $filter_status==='all'?'selected':'' ?>>All Status</option>
          <option value="active" <?= $filter_status==='active'?'selected':'' ?>>Active</option>
          <option value="inactive" <?= $filter_status==='inactive'?'selected':'' ?>>Inactive</option>
          <option value="blocked" <?= $filter_status==='blocked'?'selected':'' ?>>Blocked</option>
        </select>

        <button type="submit" class="topbar-btn"><i class="fas fa-filter"></i> Filter</button>
        <?php if($search || $filter_status !== 'all'): ?>
          <a href="customers.php" class="topbar-btn" style="color:var(--red);">Clear</a>
        <?php endif; ?>
      </form>

      <!-- Table -->
      <div class="table-scroll">
        <table class="data-table orders-table" id="customersTable">
          <thead>
            <tr>
              <th>Customer ID</th>
              <th>Customer</th>
              <th>Contact</th>
              <th>City</th>
              <th>Orders</th>
              <th>Total Spent</th>
              <th>Joined</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php if (empty($customers)): ?>
              <tr><td colspan="9" class="empty-state">No customers found</td></tr>
            <?php else: ?>
              <?php foreach ($customers as $c): ?>
                <tr class="order-row" data-id="<?= $c['id'] ?>">
                  <td><span class="order-id"><?= htmlspecialchars($c['customer_id']) ?></span></td>
                  <td>
                    <div class="customer-cell">
                      <div class="cust-avatar"><?= strtoupper(substr($c['name'],0,2)) ?></div>
                      <div>
                        <div class="cust-name"><?= htmlspecialchars($c['name']) ?></div>
                        <div class="cust-email"><?= htmlspecialchars($c['email']) ?></div>
                      </div>
                    </div>
                  </td>
                  <td><?= htmlspecialchars($c['phone']) ?></td>
                  <td><?= htmlspecialchars($c['city']) ?></td>
                  <td style="text-align:center; font-weight:500;"><?= $c['total_orders'] ?></td>
                  <td style="color:var(--gold); font-weight:500;">RS <?= number_format($c['total_spent']) ?></td>
                  <td><?= date('d M Y', strtotime($c['joined_date'])) ?></td>
                  <td><?= getStatusBadge($c['status']) ?></td>
                  <td>
                    <div class="row-actions">
                      <button class="action-btn view-btn" onclick="viewCustomer(<?= htmlspecialchars(json_encode($c)) ?>)"><i class="fas fa-eye"></i></button>
                      <button class="action-btn edit-btn" onclick="editCustomerStatus(<?= $c['id'] ?>, '<?= $c['status'] ?>')"><i class="fas fa-edit"></i></button>
                      <button class="action-btn delete-btn" onclick="deleteCustomer(<?= $c['id'] ?>)"><i class="fas fa-trash"></i></button>
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
            <a href="?page=<?= $page-1 ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>" class="page-btn">‹</a>
          <?php endif; ?>
          <?php for($i = max(1,$page-2); $i <= min($total_pages,$page+2); $i++): ?>
            <a href="?page=<?= $i ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>" class="page-btn <?= $i==$page?'active':'' ?>"><?= $i ?></a>
          <?php endfor; ?>
          <?php if ($page < $total_pages): ?>
            <a href="?page=<?= $page+1 ?>&status=<?= $filter_status ?>&search=<?= urlencode($search) ?>" class="page-btn">›</a>
          <?php endif; ?>
        </div>
      </div>
      <?php endif; ?>

    </div>
  </div>
</div>

<!-- Modals will be added in next step if needed -->
<div class="admin-toast" id="adminToast"></div>

<script src="customers.js"></script>

<?php
function getStatusBadge($status) {
    $map = [
        'active'  => ['badge-delivered', 'fa-check-circle', 'Active'],
        'inactive'=> ['badge-pending',   'fa-clock',        'Inactive'],
        'blocked' => ['badge-cancelled', 'fa-ban',          'Blocked'],
    ];
    [$cls, $ico, $label] = $map[$status] ?? ['', 'fa-question', $status];
    return "<span class='badge $cls'><i class='fas $ico'></i> $label</span>";
}
?>
</body>
</html>