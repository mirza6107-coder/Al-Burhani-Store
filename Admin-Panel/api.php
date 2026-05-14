<?php
/* ========================================
   AL BURHAN — PRODUCTS API
   File: Admin-Panel/api.php

   GET    api.php?action=list[&category=X]  → all / filtered products
   POST   api.php?action=add               → add product (multipart/form-data)
   POST   api.php?action=delete&id=X       → delete product
   POST   api.php?action=update&id=X       → update product fields
   ======================================== */

require_once __DIR__ . '/config.php';

header('Content-Type: application/json');

/* Allow cross-origin if front-end is on a different port */
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {

    /* -------- LIST -------- */
    case 'list':
        $category = $_GET['category'] ?? '';
        $search   = trim($_GET['search'] ?? '');
        $db       = getDB();

        $sql    = "SELECT * FROM products WHERE 1=1";
        $params = [];

        if ($category) {
            $sql .= " AND category = :category";
            $params[':category'] = $category;
        }
        if ($search) {
            $sql .= " AND (name LIKE :search OR tags LIKE :search2)";
            $params[':search']  = "%$search%";
            $params[':search2'] = "%$search%";
        }

        $sql .= " ORDER BY created_at DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        $products = $stmt->fetchAll();

        jsonResponse(['success' => true, 'data' => $products]);
        break;

    /* -------- ADD -------- */
    case 'add':
        $name        = trim($_POST['name']        ?? '');
        $category    = trim($_POST['category']    ?? '');
        $price       = floatval($_POST['price']   ?? 0);
        $stock       = intval($_POST['stock']     ?? 0);
        $status      = trim($_POST['status']      ?? 'In Stock');
        $description = trim($_POST['description'] ?? '');
        $features    = trim($_POST['features']    ?? '');
        $sku         = trim($_POST['sku']         ?? '');
        $tags        = trim($_POST['tags']        ?? '');

        /* Validate */
        if (!$name || !$category || $price <= 0) {
            jsonResponse(['success' => false, 'error' => 'Name, category and price are required.'], 400);
        }

        $allowed_categories = ['Perfumes', 'Watches', 'Dress', 'Sunglasses'];
        if (!in_array($category, $allowed_categories)) {
            jsonResponse(['success' => false, 'error' => 'Invalid category.'], 400);
        }

        /* Auto-SKU if blank */
        if (!$sku) {
            $db   = getDB();
            $stmt = $db->query("SELECT MAX(id) as max_id FROM products");
            $row  = $stmt->fetch();
            $next = ($row['max_id'] ?? 0) + 1;
            $sku  = 'AB-' . strtoupper(substr($category, 0, 3)) . '-' . str_pad($next, 3, '0', STR_PAD_LEFT);
        }

        /* Handle image upload */
        $imagePath = null;
        if (!empty($_FILES['image']['tmp_name'])) {
            $uploadDir = UPLOAD_DIR;
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

            $ext       = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            $allowed   = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
            if (!in_array($ext, $allowed)) {
                jsonResponse(['success' => false, 'error' => 'Invalid image type.'], 400);
            }

            $filename  = uniqid('product_', true) . '.' . $ext;
            $dest      = $uploadDir . $filename;

            if (!move_uploaded_file($_FILES['image']['tmp_name'], $dest)) {
                jsonResponse(['success' => false, 'error' => 'Image upload failed.'], 500);
            }
            $imagePath = UPLOAD_URL . $filename;
        }

        /* Insert */
        $db   = getDB();
        $stmt = $db->prepare("
            INSERT INTO products (name, category, price, stock, status, image, description, features, sku, tags)
            VALUES (:name, :category, :price, :stock, :status, :image, :description, :features, :sku, :tags)
        ");
        $stmt->execute([
            ':name'        => $name,
            ':category'    => $category,
            ':price'       => $price,
            ':stock'       => $stock,
            ':status'      => $status,
            ':image'       => $imagePath,
            ':description' => $description,
            ':features'    => $features,
            ':sku'         => $sku,
            ':tags'        => $tags,
        ]);

        $newId = $db->lastInsertId();
        $product = $db->query("SELECT * FROM products WHERE id = $newId")->fetch();

        jsonResponse(['success' => true, 'message' => 'Product added successfully.', 'data' => $product]);
        break;

    /* -------- DELETE -------- */
    case 'delete':
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id) jsonResponse(['success' => false, 'error' => 'Invalid ID.'], 400);

        $db   = getDB();

        /* Delete image file if it's in our uploads folder */
        $stmt = $db->prepare("SELECT image FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch();
        if ($row && $row['image'] && strpos($row['image'], UPLOAD_URL) === 0) {
            $file = __DIR__ . '/' . $row['image'];
            if (file_exists($file)) unlink($file);
        }

        $stmt = $db->prepare("DELETE FROM products WHERE id = :id");
        $stmt->execute([':id' => $id]);

        jsonResponse(['success' => true, 'message' => "Product #$id deleted."]);
        break;

    /* -------- UPDATE -------- */
    case 'update':
        $id = intval($_GET['id'] ?? $_POST['id'] ?? 0);
        if (!$id) jsonResponse(['success' => false, 'error' => 'Invalid ID.'], 400);

        $db      = getDB();
        $allowed = ['name', 'category', 'price', 'stock', 'status', 'description', 'features', 'sku', 'tags'];
        $sets    = [];
        $params  = [':id' => $id];

        foreach ($allowed as $field) {
            if (isset($_POST[$field])) {
                $sets[]          = "$field = :$field";
                $params[":$field"] = $_POST[$field];
            }
        }

        if (!$sets) jsonResponse(['success' => false, 'error' => 'No fields to update.'], 400);

        $stmt = $db->prepare("UPDATE products SET " . implode(', ', $sets) . " WHERE id = :id");
        $stmt->execute($params);

        $product = $db->query("SELECT * FROM products WHERE id = $id")->fetch();
        jsonResponse(['success' => true, 'message' => 'Product updated.', 'data' => $product]);
        break;

    default:
        jsonResponse(['success' => false, 'error' => 'Unknown action.'], 400);
        // Inside api.php...

        if ($_GET['action'] == 'update') {
            $id       = (int)$_POST['id'];
            $name     = $_POST['name'];
            $category = $_POST['category'];
            $price    = $_POST['price'];
            $stock    = $_POST['stock'];

            // Determine status automatically based on stock
            $status = 'In Stock';
            if ($stock <= 0) $status = 'Out of Stock';
            else if ($stock < 15) $status = 'Low Stock';

            $stmt = $conn->prepare("UPDATE products SET name=?, category=?, price=?, stock=?, status=? WHERE id=?");
            $stmt->bind_param("ssdisi", $name, $category, $price, $stock, $status, $id);

            if ($stmt->execute()) {
                echo json_encode(['success' => true]);
            } else {
                echo json_encode(['success' => false, 'error' => $conn->error]);
            }
            exit;
        }
}
