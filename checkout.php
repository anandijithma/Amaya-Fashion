<?php
session_start();

error_reporting(E_ALL);
ini_set('display_errors', 1);

include "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.html");
    exit();
}

if (!isset($_SESSION["user_id"])) {
    echo "<script>
            alert('Please login before placing an order.');
            window.location.href = 'login.html';
          </script>";
    exit();
}

if (!isset($_SESSION["checkout_cart"]) || count($_SESSION["checkout_cart"]) === 0) {
    echo "<script>
            alert('Checkout session expired. Please try again.');
            window.location.href = 'cart.html';
          </script>";
    exit();
}

/*
  Values coming from payment.html form
*/
$fullName = trim($_POST["full_name"] ?? "");
$phone = trim($_POST["phone"] ?? "");
$email = trim($_POST["email"] ?? "");
$shippingAddress = trim($_POST["shipping_address"] ?? "");
$paymentMethod = trim($_POST["payment_method"] ?? "");

if ($fullName === "" || $phone === "" || $email === "" || $shippingAddress === "" || $paymentMethod === "") {
    echo "<script>
            alert('Please fill all shipping and payment details.');
            window.history.back();
          </script>";
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo "<script>
            alert('Please enter a valid email address.');
            window.history.back();
          </script>";
    exit();
}

$cartItems = $_SESSION["checkout_cart"];
$notes = $_SESSION["checkout_notes"] ?? "";
$userId = intval($_SESSION["user_id"]);

/*
  Recalculate totals in backend
*/
$subtotal = 0;

foreach ($cartItems as $item) {
    $price = floatval($item["unitPrice"] ?? $item["price"] ?? 0);
    $quantity = intval($item["quantity"] ?? 1);

    if ($price > 0 && $quantity > 0) {
        $subtotal += $price * $quantity;
    }
}

$tax = $subtotal * 0.15;
$total = $subtotal + $tax;

if ($subtotal <= 0) {
    echo "<script>
            alert('Invalid order total.');
            window.location.href = 'cart.html';
          </script>";
    exit();
}


$orderSql = "INSERT INTO `orders`
            (`user_id`, `customer_name`, `customer_email`, `telephone`, `shipping_address`, `subtotal`, `tax`, `total`, `notes`, `payment_method`, `order_status`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'Pending')";

$orderStmt = mysqli_prepare($conn, $orderSql);

if (!$orderStmt) {
    die("Order prepare failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param(
    $orderStmt,
    "issssdddss",
    $userId,
    $fullName,
    $email,
    $phone,
    $shippingAddress,
    $subtotal,
    $tax,
    $total,
    $notes,
    $paymentMethod
);

if (!mysqli_stmt_execute($orderStmt)) {
    die("Order insert failed: " . mysqli_error($conn));
}

$orderId = mysqli_insert_id($conn);
mysqli_stmt_close($orderStmt);

/*
  Insert order items
*/
$itemSql = "INSERT INTO `order_items`
            (`order_id`, `product_id`, `product_name`, `color`, `size`, `price`, `quantity`, `item_total`)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

$itemStmt = mysqli_prepare($conn, $itemSql);

if (!$itemStmt) {
    die("Order item prepare failed: " . mysqli_error($conn));
}

foreach ($cartItems as $item) {
    $productId = strval($item["id"] ?? "");
    $productName = strval($item["name"] ?? "Unknown Product");
    $color = strval($item["color"] ?? $item["selectedColor"] ?? "");
    $size = strval($item["size"] ?? $item["selectedSize"] ?? "");
    $price = floatval($item["unitPrice"] ?? $item["price"] ?? 0);
    $quantity = intval($item["quantity"] ?? 1);
    $itemTotal = $price * $quantity;

    if ($price <= 0 || $quantity <= 0) {
        continue;
    }

    mysqli_stmt_bind_param(
        $itemStmt,
        "issssdid",
        $orderId,
        $productId,
        $productName,
        $color,
        $size,
        $price,
        $quantity,
        $itemTotal
    );

    if (!mysqli_stmt_execute($itemStmt)) {
        die("Order item insert failed: " . mysqli_error($conn));
    }
}

mysqli_stmt_close($itemStmt);
mysqli_close($conn);

/*
  Clear checkout session
*/
unset($_SESSION["checkout_cart"]);
unset($_SESSION["checkout_notes"]);
unset($_SESSION["checkout_subtotal"]);
unset($_SESSION["checkout_tax"]);
unset($_SESSION["checkout_total"]);

echo "<script>
        alert('Order placed successfully.');
        localStorage.removeItem('foa_cart_items');
        localStorage.removeItem('amaya_cart');
        window.location.href = 'index.html';
      </script>";
exit();
?>