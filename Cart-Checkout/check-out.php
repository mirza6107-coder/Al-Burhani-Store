<?php
/* ========================================
   AL BURHAN STORE — CHECKOUT PHP BACKEND
   FIXED VERSION WITH CUSTOMER SAVE
   ======================================== */

session_start();

 $conn = new mysqli("localhost", "root", "", "alburhanstore");
 $conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die("DB connection failed: " . $conn->connect_error);
}

/* ============ HELPERS ============ */
function clean($conn, $val) {
    return $conn->real_escape_string(htmlspecialchars(strip_tags(trim($val ?? ""))));
}

function redirectError($url, $msg) {
    $_SESSION["checkout_error"] = $msg;
    header("Location: $url");
    exit;
}

/* ============ ONLY POST ============ */
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: check-out.html");
    exit;
}

/* ============ 1. FORM INPUTS ============ */
 $firstname       = clean($conn, $_POST["firstname"] ?? "");
 $lastname        = clean($conn, $_POST["lastname"] ?? "");
 $email           = filter_var(trim($_POST["email"] ?? ""), FILTER_SANITIZE_EMAIL);
 $phone           = clean($conn, $_POST["phone"] ?? "");
 $address         = clean($conn, $_POST["address"] ?? "");
 $apartment       = clean($conn, $_POST["apartment"] ?? "");
 $city            = clean($conn, $_POST["city"] ?? "");
 $country         = clean($conn, $_POST["country"] ?? "");
 $postal          = clean($conn, $_POST["postal"] ?? "");
 $state           = clean($conn, $_POST["state"] ?? "");
 $delivery_method = clean($conn, $_POST["delivery"] ?? "standard");
 $payment_method  = clean($conn, $_POST["payment_method"] ?? "");
 $notes           = clean($conn, $_POST["notes"] ?? "");

/* ============ 2. CART ============ */
 $cart_json = $_POST['cart_json'] ?? '';
 $cart = json_decode($cart_json, true);

if (empty($cart) && !empty($_SESSION['cart'])) {
    $cart = $_SESSION['cart'];
}

if (empty($cart) || !is_array($cart)) {
    redirectError("check-out.html", "Your cart is empty.");
}

/* ============ 3. VALIDATION ============ */
 $errors = [];
if (empty($firstname))                          $errors[] = "First name is required.";
if (empty($lastname))                           $errors[] = "Last name is required.";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Valid email is required.";
if (empty($phone))                              $errors[] = "Phone number is required.";
if (empty($address))                            $errors[] = "Address is required.";
if (empty($city))                               $errors[] = "City is required.";
if (empty($country))                            $errors[] = "Country is required.";
if (empty($postal))                             $errors[] = "Postal code is required.";
if (empty($payment_method))                     $errors[] = "Payment method is required.";

if (!empty($errors)) {
    $_SESSION["checkout_errors"] = $errors;
    redirectError("check-out.html", implode("<br>", $errors));
}

/* ============ 4. RECALCULATE TOTALS ============ */
 $calc_subtotal = 0;
foreach ($cart as $item) {
    $price = floatval($item["price"] ?? 0);
    $qty   = max(1, intval($item["qty"] ?? 1));
    $calc_subtotal += round($price * $qty, 2);
}

 $delivery_costs = ["standard" => 0, "express" => 35, "sameday" => 75];
 $calc_shipping  = $delivery_costs[$delivery_method] ?? 0;

if ($delivery_method === "standard" && $calc_subtotal >= 500) {
    $calc_shipping = 0;
}

 $calc_vat      = round($calc_subtotal * 0.05, 2);
 $promo_rate    = floatval($_SESSION["promo_discount"] ?? 0);
 $calc_discount = round($calc_subtotal * $promo_rate, 2);
 $calc_total    = round($calc_subtotal + $calc_shipping + $calc_vat - $calc_discount, 2);

 $promo_code = clean($conn, $_SESSION["promo_code"] ?? "");
 $user_id    = $_SESSION["user_id"] ?? null;

/* ============================================
   4B. SAVE / UPDATE CUSTOMER IN customers TABLE
   ============================================ */

 $full_name = trim($firstname . " " . $lastname);

// ── Step 1: Check if customer already exists by email ──
 $check_stmt = $conn->prepare("SELECT id, customer_id FROM customers WHERE email = ? LIMIT 1");
 $check_stmt->bind_param("s", $email);
 $check_stmt->execute();
 $check_result = $check_stmt->get_result();

if ($check_result->num_rows > 0) {
    // ── Customer EXISTS → Update their info ──
    $existing       = $check_result->fetch_assoc();
    $customer_db_id = intval($existing["id"]);
    $customer_id    = $existing["customer_id"];

    $update_stmt = $conn->prepare("
        UPDATE customers 
        SET name = ?, phone = ?, address = ?, apartment = ?, 
            city = ?, country = ?, postal = ?, state = ?
        WHERE id = ?
    ");
    $update_stmt->bind_param(
        "ssssssssi",
        $full_name, $phone, $address, $apartment,
        $city, $country, $postal, $state,
        $customer_db_id
    );

    if (!$update_stmt->execute()) {
        error_log("Failed to update customer: " . $update_stmt->error);
    }
    $update_stmt->close();

} else {
    // ── Customer does NOT exist → Insert new ──
    $cust_id_result = $conn->query("SELECT MAX(id) AS max_id FROM customers");
    $cust_id_row    = $cust_id_result->fetch_assoc();
    $next_id        = intval($cust_id_row["max_id"] ?? 0) + 1;
    $customer_id    = "CUS-" . str_pad($next_id, 5, "0", STR_PAD_LEFT);
    $cust_id_result->free();

    $insert_cust = $conn->prepare("
        INSERT INTO customers 
        (customer_id, name, email, phone, address, apartment, 
         city, country, postal, state, created_at)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())
    ");
    $insert_cust->bind_param(
        "ssssssssss",
        $customer_id, $full_name, $email, $phone, $address,
        $apartment, $city, $country, $postal, $state
    );

    if (!$insert_cust->execute()) {
        error_log("Failed to insert customer: " . $insert_cust->error);
    } else {
        $customer_db_id = $conn->insert_id;
    }
    $insert_cust->close();
}

 $check_stmt->close();

// Store in session for future use
 $_SESSION["customer_id"] = $customer_id ?? "";

/* ============ 5. INSERT ORDER ============ */
 $stmt = $conn->prepare("
    INSERT INTO `orders` 
    (user_id, firstname, lastname, email, phone, address, apartment, city, country, 
     postal, state, delivery_method, payment_method, notes, subtotal, shipping_cost, 
     vat, discount, total, promo_code, status, created_at)
    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', NOW())
");

 $stmt->bind_param(
    "isssssssssssssddddds",
    $user_id, $firstname, $lastname, $email, $phone,
    $address, $apartment, $city, $country, $postal, $state,
    $delivery_method, $payment_method, $notes,
    $calc_subtotal, $calc_shipping, $calc_vat, $calc_discount, $calc_total,
    $promo_code
);

if (!$stmt->execute()) {
    redirectError("check-out.html", "Failed to save order: " . $stmt->error);
}

 $order_id = $conn->insert_id;
 $stmt->close();

/* ============ 6. INSERT ORDER ITEMS (CLEAN FIX) ============ */
 $valid_product_ids = [];
 $pid_result = $conn->query("SELECT id FROM products");
if ($pid_result) {
    while ($row = $pid_result->fetch_assoc()) {
        $valid_product_ids[] = intval($row["id"]);
    }
    $pid_result->free();
}

foreach ($cart as $item) {
    $raw_pid = !empty($item["product_id"]) ? intval($item["product_id"]) : 0;

    if ($raw_pid > 0 && in_array($raw_pid, $valid_product_ids, true)) {
        $pid_sql = $raw_pid;
    } else {
        $pid_sql = "NULL";
    }

    $name  = clean($conn, $item["name"] ?? "Unknown Product");
    $price = round(floatval($item["price"] ?? 0), 2);
    $qty   = max(1, intval($item["qty"] ?? 1));
    $size  = clean($conn, $item["size"] ?? "");
    $img   = clean($conn, $item["img"] ?? "");
    $line  = round($price * $qty, 2);

    $sql = "INSERT INTO `order_items` 
            (order_id, product_id, name, price, qty, size, img, line_total)
            VALUES ($order_id, $pid_sql, '$name', $price, $qty, '$size', '$img', $line)";

    if (!$conn->query($sql)) {
        error_log("Failed to insert item for order $order_id: " . $conn->error);
    }
}

/* ============ 7. PROMO & CLEANUP ============ */
if (!empty($promo_code)) {
    $promo_stmt = $conn->prepare("UPDATE promo_codes SET used_count = used_count + 1 WHERE code = ? LIMIT 1");
    $promo_stmt->bind_param("s", $promo_code);
    $promo_stmt->execute();
    $promo_stmt->close();
}

 $_SESSION["cart"] = [];
 $_SESSION["promo_code"] = null;
 $_SESSION["promo_discount"] = 0;
unset($_SESSION["checkout_errors"], $_SESSION["checkout_error"]);

/* ============ 8. REDIRECT ============ */
 $order_ref = "ALB-2026-" . str_pad($order_id, 5, "0", STR_PAD_LEFT);
 $total_fmt = "RS " . number_format($calc_total, 2);

header("Location: confirmation.html?order=" . urlencode($order_ref) 
       . "&name=" . urlencode($firstname) 
       . "&total=" . urlencode($total_fmt));
exit;

 $conn->close();