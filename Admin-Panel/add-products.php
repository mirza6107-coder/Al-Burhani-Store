<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Add Product • Al Burhan Admin</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,300;0,400;0,500;0,600;1,300;1,400&family=Cinzel:wght@400;500;600&family=Raleway:wght@300;400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <link rel="stylesheet" href="sidebar.css" />
    <link rel="stylesheet" href="add-products.css" />
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
                <h2>Add New Product</h2>
                <p>Fill in the details to list a new item in the catalogue</p>
            </div>
            <div class="admin-header-right">
                <a href="dashboard.php" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Back
                </a>
                <button class="notif-btn" title="Notifications">
                    <i class="fas fa-bell"></i>
                    <span class="notif-dot"></span>
                </button>
            </div>
        </header>

        <!-- Form -->
        <div class="form-wrapper">
            <form id="productForm" novalidate>
                <div class="form-grid">

                    <!-- LEFT PANEL — Core Details -->
                    <div class="form-panel">
                        <div class="form-panel-title">
                            <i class="fas fa-box"></i>
                            Product Details
                        </div>

                        <div class="form-group">
                            <label>Product Name <span class="required">*</span></label>
                            <input type="text" id="name" placeholder="e.g. Oud Majesty Eau de Parfum" />
                            <div class="field-error"></div>
                        </div>

                        <div class="form-group">
                            <label>Category <span class="required">*</span></label>
                            <select id="category">
                                <option value="">Select a category</option>
                                <option value="Perfumes">Perfumes</option>
                                <option value="Watches">Watches</option>
                                <option value="Dress">Dress</option>
                                <option value="Sunglasses">Sunglasses</option>
                            </select>
                            <div class="field-error"></div>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label>Price (RS) <span class="required">*</span></label>
                                <div class="input-prefix-wrap">
                                    <span class="input-prefix">RS</span>
                                    <input type="number" id="price" placeholder="0.00" step="0.01" min="0" />
                                </div>
                                <div class="field-error"></div>
                            </div>
                            <div class="form-group">
                                <label>Stock Qty <span class="required">*</span></label>
                                <input type="number" id="stock" placeholder="0" min="0" value="50" />
                                <div class="field-error"></div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Status</label>
                            <div class="status-toggle">
                                <div class="status-opt selected">In Stock</div>
                                <div class="status-opt">Low Stock</div>
                                <div class="status-opt">Out of Stock</div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Featured Image</label>
                            <div class="upload-zone">
                                <input type="file" id="image" accept="image/*" />
                                <div class="upload-icon"><i class="fas fa-cloud-upload-alt"></i></div>
                                <p>Click or drag image here</p>
                                <span>JPG, PNG, WEBP — max 5MB</span>
                            </div>
                            <div id="image-preview" class="image-preview">
                                <span class="preview-label">Preview</span>
                                <p>Image preview will appear here</p>
                            </div>
                        </div>

                    </div>

                    <!-- RIGHT PANEL — Description & Actions -->
                    <div class="form-panel">
                        <div class="form-panel-title">
                            <i class="fas fa-align-left"></i>
                            Description & Notes
                        </div>

                        <div class="form-group">
                            <label>Product Description</label>
                            <textarea id="description" rows="5" placeholder="Describe the product — materials, origin, occasion…"></textarea>
                            <div class="input-hint">Shown on the product detail page</div>
                        </div>

                        <div class="form-group">
                            <label>Key Features</label>
                            <textarea id="features" rows="5" placeholder="One feature per line&#10;e.g. Swiss-made movement&#10;Sapphire crystal glass&#10;Water resistant to 100m"></textarea>
                            <div class="input-hint">Each line becomes a bullet point</div>
                        </div>

                        <div class="form-group">
                            <label>SKU / Product Code</label>
                            <input type="text" id="sku" placeholder="e.g. AB-PERF-001" />
                            <div class="input-hint">Leave blank to auto-generate</div>
                        </div>

                        <div class="form-group">
                            <label>Tags</label>
                            <input type="text" id="tags" placeholder="e.g. oud, luxury, gift, new-arrival" />
                            <div class="input-hint">Comma-separated for search visibility</div>
                        </div>

                        <div class="form-actions">
                            <button type="button" onclick="history.back()" class="btn-cancel">
                                Cancel
                            </button>
                            <button type="submit" class="btn-save">
                                <i class="fas fa-check"></i> Save Product
                            </button>
                        </div>

                    </div>

                </div>
            </form>
        </div>

    </main>
</div>

<script src="add-products.js"></script>
</body>
</html>