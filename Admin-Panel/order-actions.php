<?php
// ============================================================
//  AL BURHAN STORE — order-actions.php (AJAX Endpoints)
// ============================================================

session_start();
require_once 'config.php';

header('Content-Type: application/json');

 $pdo = getDB();

 $action = $_GET['action'] ?? $_POST['action'] ?? '';

try {
    // ── GET ORDER DETAILS (for View Modal) ──
    if ($action === 'get_order' && isset($_GET['id'])) {
        $order_id = (int)$_GET['id'];

        // Fetch order
        $stmt = $pdo->prepare("
            SELECT o.*, CONCAT(o.firstname, ' ', o.lastname) AS customer
            FROM orders o 
            WHERE o.id = ?
        ");
        $stmt->execute([$order_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$order) {
            echo json_encode(['success' => false, 'message' => 'Order not found']);
            exit;
        }

        // Fetch order items
        $itemStmt = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ? ORDER BY id");
        $itemStmt->execute([$order_id]);
        $items = $itemStmt->fetchAll(PDO::FETCH_ASSOC);

        // Payment display map
        $paymentMap = [
            'card' => 'Credit Card',
            'cod'  => 'Cash on Delivery',
            'bank' => 'Bank Transfer',
        ];

        // Build response
        $order_ref = "ALB-2026-" . str_pad($order['id'], 5, "0", STR_PAD_LEFT);
        
        echo json_encode([
            'success' => true,
            'order'   => [
                'id'             => $order['id'],
                'order_ref'      => $order_ref,
                'customer'       => $order['customer'],
                'firstname'      => $order['firstname'],
                'lastname'       => $order['lastname'],
                'email'          => $order['email'],
                'phone'          => $order['phone'],
                'address'        => $order['address'],
                'apartment'      => $order['apartment'],
                'city'           => $order['city'],
                'country'        => $order['country'],
                'postal'         => $order['postal'],
                'state'          => $order['state'],
                'delivery_method' => $order['delivery_method'],
                'payment_method' => $paymentMap[$order['payment_method']] ?? ucfirst($order['payment_method'] ?? ''),
                'payment_raw'    => $order['payment_method'],
                'notes'          => $order['notes'],
                'subtotal'       => floatval($order['subtotal'] ?? 0),
                'shipping_cost'  => floatval($order['shipping_cost'] ?? 0),
                'vat'            => floatval($order['vat'] ?? 0),
                'discount'       => floatval($order['discount'] ?? 0),
                'total'          => floatval($order['total'] ?? 0),
                'promo_code'     => $order['promo_code'],
                'status'         => $order['status'],
                'created_at'     => $order['created_at'],
            ],
            'items'   => array_map(function($item) {
                return [
                    'id'         => $item['id'],
                    'product_id' => $item['product_id'],
                    'name'       => $item['name'],
                    'price'      => floatval($item['price']),
                    'qty'        => intval($item['qty']),
                    'size'       => $item['size'] ?? '',
                    'img'        => $item['img'] ?? '',
                    'line_total' => floatval($item['line_total']),
                ];
            }, $items),
        ]);
        exit;
    }

    // ── CREATE ORDER ──
    if ($action === 'create_order' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $name    = trim($_POST['name'] ?? '');
        $email   = trim($_POST['email'] ?? '');
        $phone   = trim($_POST['phone'] ?? '');
        $city    = trim($_POST['city'] ?? '');
        $product_id   = (int)($_POST['product_id'] ?? 0);
        $qty          = max(1, (int)($_POST['qty'] ?? 1));
        $payment      = $_POST['payment'] ?? 'cod';
        $status       = $_POST['status'] ?? 'pending';
        $notes        = trim($_POST['notes'] ?? '');

        // Split name
        $parts = explode(' ', $name, 2);
        $firstname = $parts[0] ?? '';
        $lastname  = $parts[1] ?? '';

        // Get product price
        $prodStmt = $pdo->prepare("SELECT id, name, price, image FROM products WHERE id = ?");
        $prodStmt->execute([$product_id]);
        $product = $prodStmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            echo json_encode(['success' => false, 'message' => 'Product not found']);
            exit;
        }

        $subtotal = $product['price'] * $qty;
        $shipping = 0;
        $vat      = round($subtotal * 0.05, 2);
        $discount = 0;
        $total    = round($subtotal + $shipping + $vat - $discount, 2);

        // Insert order
        $stmt = $pdo->prepare("
            INSERT INTO orders 
            (user_id, firstname, lastname, email, phone, address, apartment, city, country, 
             postal, state, delivery_method, payment_method, notes, subtotal, shipping_cost, 
             vat, discount, total, promo_code, status, created_at)
            VALUES (NULL, ?, ?, ?, ?, '', '', ?, '', '', '', 'standard', ?, ?, ?, ?, ?, ?, '', ?, NOW())
        ");
        $stmt->execute([
            $firstname, $lastname, $email, $phone, $city,
            $payment, $notes, $subtotal, $shipping, $vat, $discount, $total, $status
        ]);
        $order_id = $pdo->lastInsertId();

        // Insert order item
        $itemStmt = $pdo->prepare("
            INSERT INTO order_items (order_id, product_id, name, price, qty, size, img, line_total)
            VALUES (?, ?, ?, ?, ?, '', '', ?)
        ");
        $itemStmt->execute([
            $order_id, $product_id, $product['name'], $product['price'],
            $qty, $product['price'] * $qty
        ]);

        echo json_encode(['success' => true, 'order_id' => $order_id]);
        exit;
    }

    // ── EXPORT CSV ──
    if ($action === 'export_csv') {
        $sql = "
            SELECT 
                o.id,
                CONCAT(o.firstname, ' ', o.lastname) AS customer,
                o.email, o.phone, o.city, o.country,
                o.payment_method, o.subtotal, o.shipping_cost, o.vat, o.discount, o.total,
                o.status, o.created_at,
                (SELECT GROUP_CONCAT(oi.name SEPARATOR ', ') FROM order_items oi WHERE oi.order_id = o.id) AS products
            FROM orders o
            ORDER BY o.created_at DESC
        ";
        $orders = $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC);

        $filename = "orders_export_" . date('Y-m-d_His') . ".csv";
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');

        $output = fopen('php://output', 'w');
        // BOM for Excel UTF-8
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        fputcsv($output, ['Order ID', 'Reference', 'Customer', 'Email', 'Phone', 'City', 'Country', 'Payment', 'Subtotal', 'Shipping', 'VAT', 'Discount', 'Total', 'Status', 'Products', 'Date']);

        foreach ($orders as $o) {
            fputcsv($output, [
                $o['id'],
                'ALB-2026-' . str_pad($o['id'], 5, '0', STR_PAD_LEFT),
                $o['customer'],
                $o['email'],
                $o['phone'],
                $o['city'],
                $o['country'],
                $o['payment_method'],
                $o['subtotal'],
                $o['shipping_cost'],
                $o['vat'],
                $o['discount'],
                $o['total'],
                $o['status'],
                $o['products'],
                $o['created_at'],
            ]);
        }
        fclose($output);
        exit;
    }

    echo json_encode(['success' => false, 'message' => 'Unknown action']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}