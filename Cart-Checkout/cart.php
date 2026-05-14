<?php
/* ========================================
   AL BURHAN STORE — CART PHP BACKEND
   Handles session cart, add/remove/update,
   promo validation, and checkout redirect
   ======================================== */

session_start();

$conn = new mysqli("localhost", "root", "", "alburhanstore");

if ($conn->connect_error) {
    die(json_encode(["success" => false, "message" => "DB connection failed"]));
}

// ---- Valid promo codes ----
$PROMO_CODES = [
    "BURHAN10" => 0.10,
    "VIP20"    => 0.20,
];

// ---- Init session cart ----
if (!isset($_SESSION["cart"])) {
    $_SESSION["cart"] = [];
}

// ---- Route by action ----
$action = $_POST["action"] ?? $_GET["action"] ?? "";

switch ($action) {

    /* ---- ADD item to cart ---- */
    case "add":
        $id    = intval($_POST["product_id"] ?? 0);
        $name  = $conn->real_escape_string($_POST["name"] ?? "");
        $price = floatval($_POST["price"] ?? 0);
        $size  = $conn->real_escape_string($_POST["size"] ?? "100ml");
        $img   = $conn->real_escape_string($_POST["img"] ?? "");
        $qty   = max(1, intval($_POST["qty"] ?? 1));

        if ($id <= 0 || $price <= 0) {
            echo json_encode(["success" => false, "message" => "Invalid product"]);
            exit;
        }

        $key = $id . "_" . $size;

        if (isset($_SESSION["cart"][$key])) {
            $_SESSION["cart"][$key]["qty"] += $qty;
        } else {
            $_SESSION["cart"][$key] = [
                "product_id" => $id,
                "name"       => $name,
                "price"      => $price,
                "size"       => $size,
                "img"        => $img,
                "qty"        => $qty,
            ];
        }

        echo json_encode([
            "success"    => true,
            "message"    => "$name added to bag",
            "cart_count" => array_sum(array_column($_SESSION["cart"], "qty")),
        ]);
        break;

    /* ---- UPDATE qty ---- */
    case "update":
        $key = $conn->real_escape_string($_POST["key"] ?? "");
        $qty = max(1, intval($_POST["qty"] ?? 1));

        if (isset($_SESSION["cart"][$key])) {
            $_SESSION["cart"][$key]["qty"] = $qty;
            echo json_encode(["success" => true, "message" => "Updated"]);
        } else {
            echo json_encode(["success" => false, "message" => "Item not found"]);
        }
        break;

    /* ---- REMOVE item ---- */
    case "remove":
        $key = $conn->real_escape_string($_POST["key"] ?? "");

        if (isset($_SESSION["cart"][$key])) {
            unset($_SESSION["cart"][$key]);
            echo json_encode([
                "success"    => true,
                "message"    => "Item removed",
                "cart_count" => array_sum(array_column($_SESSION["cart"], "qty")),
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Item not found"]);
        }
        break;

    /* ---- APPLY promo code ---- */
    case "promo":
        $code = strtoupper(trim($_POST["code"] ?? ""));

        if (isset($PROMO_CODES[$code])) {
            $_SESSION["promo_code"]     = $code;
            $_SESSION["promo_discount"] = $PROMO_CODES[$code];
            echo json_encode([
                "success"  => true,
                "discount" => $PROMO_CODES[$code],
                "message"  => "Promo applied — " . ($PROMO_CODES[$code] * 100) . "% off",
            ]);
        } else {
            echo json_encode(["success" => false, "message" => "Invalid promo code"]);
        }
        break;

    /* ---- GET cart summary (AJAX) ---- */
    case "summary":
        $cart     = $_SESSION["cart"] ?? [];
        $subtotal = 0;

        foreach ($cart as $item) {
            $subtotal += $item["price"] * $item["qty"];
        }

        $shipping = $subtotal >= 500 ? 0 : 50;
        $vat      = $subtotal * 0.05;
        $discount = $subtotal * ($_SESSION["promo_discount"] ?? 0);
        $total    = $subtotal + $shipping + $vat - $discount;

        echo json_encode([
            "success"    => true,
            "items"      => $cart,
            "cart_count" => array_sum(array_column($cart, "qty")),
            "subtotal"   => $subtotal,
            "shipping"   => $shipping,
            "vat"        => round($vat, 2),
            "discount"   => round($discount, 2),
            "total"      => round($total, 2),
        ]);
        break;

    /* ---- CLEAR cart ---- */
    case "clear":
        $_SESSION["cart"]           = [];
        $_SESSION["promo_code"]     = null;
        $_SESSION["promo_discount"] = 0;
        echo json_encode(["success" => true, "message" => "Cart cleared"]);
        break;

    /* ---- CHECKOUT — save order to DB then redirect ---- */
    
        /* ---- Default: show cart page ---- */
    default:
        // If accessed directly, redirect to HTML cart
        header("Location: carts.php");
        exit;
}

$conn->close();
