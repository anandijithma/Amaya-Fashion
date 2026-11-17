<?php
session_start();

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: cart.html");
    exit();
}

if (!isset($_SESSION["user_id"])) {
    echo "<script>
            alert('Please login before checkout.');
            window.location.href = 'login.html';
          </script>";
    exit();
}

$cartJson = $_POST["cart_data"] ?? "";
$notes = trim($_POST["notes"] ?? "");

$cartItems = json_decode($cartJson, true);

if (!is_array($cartItems) || count($cartItems) === 0) {
    echo "<script>
            alert('Your cart is empty.');
            window.location.href = 'cart.html';
          </script>";
    exit();
}

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

$_SESSION["checkout_cart"] = $cartItems;
$_SESSION["checkout_notes"] = $notes;
$_SESSION["checkout_subtotal"] = $subtotal;
$_SESSION["checkout_tax"] = $tax;
$_SESSION["checkout_total"] = $total;

header("Location: payment.html");
exit();
?>