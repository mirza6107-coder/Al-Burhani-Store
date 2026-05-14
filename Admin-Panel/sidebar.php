<!-- sidebar.php — AL BURHAN Admin Sidebar -->
<aside class="sidebar">

    <div class="sidebar-logo">
        <div class="sidebar-logo-ornament">✦ ◆ ✦</div>
        <h1>AL BURHAN</h1>
        <div class="sidebar-logo-sub">Store</div>
        <span class="sidebar-logo-badge">Admin Panel</span>
    </div>

    <nav class="admin-nav">

        <div class="nav-section-label">Main</div>
        <a href="dashboard.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'dashboard.php' ? 'active' : '' ?>">
            <i class="fas fa-th-large"></i>
            Dashboard
        </a>
        <a href="products.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'products.php' ? 'active' : '' ?>">
            <i class="fas fa-box"></i>
            Products
        </a>
        <a href="add-products.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'add-products.php' ? 'active' : '' ?>">
            <i class="fas fa-plus-circle"></i>
            Add Product
        </a>

        <div class="nav-section-label">Store</div>
        <a href="orders.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'orders.php' ? 'active' : '' ?>">
            <i class="fas fa-shopping-bag"></i>
            Orders
        </a>
        <a href="customers.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'customers.php' ? 'active' : '' ?>">
            <i class="fas fa-users"></i>
            Customers
        </a>
        

        <div class="nav-section-label">Analytics</div>
        <a href="revenue.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'revenue.php' ? 'active' : '' ?>">
            <i class="fas fa-chart-line"></i>
            Revenue
        </a>
        <a href="reports.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'reports.php' ? 'active' : '' ?>">
            <i class="fas fa-file-alt"></i>
            Reports
        </a>

        <div class="nav-section-label">System</div>
        <a href="messages.php" class="nav-item <?= basename($_SERVER['PHP_SELF']) == 'messages.php' ? 'active' : '' ?>">
            <i class="fas fa-envelope"></i>
            Messages
        </a>
        
        <a href="../login/logout.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            Logout
        </a>
        <a href="../Home/burhani.php" class="nav-item">
            <i class="fas fa-sign-out-alt"></i>
            Back To Home
        </a>

    </nav>

    <div class="sidebar-footer">
        <div class="sidebar-user">
            <div class="sidebar-avatar">AB</div>
            <div class="sidebar-user-info">
                <p>Admin Burhan</p>
                <span>admin@alburhan.com</span>
            </div>
        </div>
    </div>

</aside>