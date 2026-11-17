<?php
session_start();

header("Content-Type: application/json");

if (!isset($_SESSION["checkout_cart"]) || count($_SESSION["checkout_cart"]) === 0) {
    echo json_encode([
        "success" => false,
        "message" => "No checkout data found."
    ]);
    exit();
}

echo json_encode([
    "success" => true,
    "subtotal" => $_SESSION["checkout_subtotal"],
    "tax" => $_SESSION["checkout_tax"],
    "total" => $_SESSION["checkout_total"],
    "items" => $_SESSION["checkout_cart"]
]);
exit();
?>