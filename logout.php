<?php
// logout.php
session_start();
include 'config.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (isset($_SESSION['cart']) && !empty($_SESSION['cart']) && isset($_SESSION['user'])) {
    $user = $_SESSION['user'];
    $cart = $_SESSION['cart'];
    $user_id = $_SESSION['user_id'];
    $total = 0;

    // Tính tổng
    foreach ($cart as $pid => $qty) {
        $result = $conn->query("SELECT price FROM products WHERE id = $pid");
        if ($product = $result->fetch_assoc()) {
            $total += $product['price'] * $qty;
        }
    }

    // Thêm vào bảng orders
    $conn->query("INSERT INTO orders (user_id, total) VALUES ('$user_id', $total)");
    $order_id = $conn->insert_id;

    // Thêm từng món vào order_items
    foreach ($cart as $pid => $qty) {
        $result = $conn->query("SELECT price FROM products WHERE id = $pid");
        $product = $result->fetch_assoc();
        $price = $product['price'];
        $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $pid, $qty, $price)");
    }
}

session_destroy();
header("Location: login.php");
exit();
?>
