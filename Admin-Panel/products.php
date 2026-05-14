<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Products • Al Burhan Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="sidebar.css" />
    <link rel="stylesheet" href="products.css" />
</head>
<body>

<div class="admin-container">

    <!-- SIDEBAR -->
    <?php include 'sidebar.php'; ?>

    <!-- MAIN CONTENT -->
    <main class="main-content">

        <!-- Header -->
        <header class="admin-header">
            <div class="admin-header-left">
                <h2>All Products</h2>
                <p>Manage your full catalogue</p>
            </div>
            <div class="admin-header-right">
                <div class="search-bar">
                    <i class="fas fa-search"></i>
                    <input type="text" id="search-input" placeholder="Search products..." />
                </div>
                <button class="notif-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notif-dot"></span>
                </button>
                <a href="add-products.php" class="btn-add">
                    <i class="fas fa-plus"></i> New Product
                </a>
            </div>
        </header>

        <!-- Filters Bar -->
        <div class="filters-bar">
            <span class="filters-label">Filter by</span>

            <select id="category-filter" class="filter-select">
                <option value="">All Categories</option>
                <option value="Perfumes">Perfumes</option>
                <option value="Watches">Watches</option>
                <option value="Dress">Dress</option>
                <option value="Sunglasses">Sunglasses</option>
            </select>

            <span id="products-count" class="products-count">8 products</span>

            <div class="view-toggle">
                <button class="view-btn active" data-view="grid" title="Grid View">
                    <i class="fas fa-th"></i>
                </button>
                <button class="view-btn" data-view="table" title="List View">
                    <i class="fas fa-list"></i>
                </button>
            </div>
        </div>

        <!-- Products Grid -->
        <div class="products-wrapper">
            <div class="product-grid" id="product-grid">
                <!-- Populated by products.js -->
            </div>
        </div>

    </main>
</div>
<div id="edit-modal" class="modal-overlay">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Edit Product</h3>
            <button class="close-modal" onclick="closeEditModal()">&times;</button>
        </div>
        <form id="edit-form">
            <input type="hidden" id="edit-id" name="id">
            
            <div class="form-group">
                <label>Product Name</label>
                <input type="text" id="edit-name" name="name" class="form-control" required>
            </div>

            <div style="display: flex; gap: 15px;">
                <div class="form-group" style="flex: 1;">
                    <label>Category</label>
                    <select id="edit-category" name="category" class="form-control">
                        <option value="Perfumes">Perfumes</option>
                        <option value="Watches">Watches</option>
                        <option value="Dress">Dress</option>
                        <option value="Sunglasses">Sunglasses</option>
                    </select>
                </div>
                <div class="form-group" style="flex: 1;">
                    <label>Price (RS)</label>
                    <input type="number" id="edit-price" name="price" class="form-control" required>
                </div>
            </div>

            <div class="form-group">
                <label>Stock Quantity</label>
                <input type="number" id="edit-stock" name="stock" class="form-control" required>
            </div>

            <button type="submit" class="btn-save">Update Product ✦</button>
        </form>
    </div>
</div>

<script src="products.js"></script>
</body>
</html>