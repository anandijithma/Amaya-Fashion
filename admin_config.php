<?php
$ADMIN_TABLES = [
    "user" => [
        "label" => "Users",
        "primary" => "id",
        "display" => ["id", "name", "username", "email", "telephone", "userole"],
        "editable" => ["name", "username", "email", "telephone", "userole"],
        "search" => ["id", "name", "username", "email", "telephone", "userole"]
    ],

    "orders" => [
        "label" => "Orders",
        "primary" => "id",
        "display" => ["id", "user_id", "customer_name", "customer_email", "telephone", "shipping_address", "subtotal", "tax", "total", "notes", "payment_method", "order_status", "created_at"],
        "editable" => ["customer_name", "customer_email", "telephone", "shipping_address", "notes", "payment_method", "order_status"],
        "search" => ["id", "customer_name", "customer_email", "telephone", "shipping_address", "payment_method", "order_status", "created_at"]
    ],

   "order_items" => [
    "label" => "Order Items",
    "primary" => "id",
    "display" => [
        "id",
        "order_id",
        "customer_email",
        "product_id",
        "product_name",
        "color",
        "size",
        "price",
        "quantity",
        "item_total"
    ],
    "editable" => [
        "product_name",
        "color",
        "size",
        "price",
        "quantity",
        "item_total"
    ],
    "search" => [
        "id",
        "order_id",
        "customer_email",
        "product_id",
        "product_name",
        "color",
        "size"
    ]
],

    "amaya_messages" => [
        "label" => "Messages",
        "primary" => "id",
        "display" => ["id", "name", "email", "subject", "message", "status", "created_at"],
        "editable" => ["name", "email", "subject", "message", "status"],
        "search" => ["id", "name", "email", "subject", "message", "status", "created_at"]
    ]
];


function getTableConfig($tableName) {
    global $ADMIN_TABLES;
    return $ADMIN_TABLES[$tableName] ?? null;
}

function bt($value) {
    return "`" . str_replace("`", "``", $value) . "`";
}
?>