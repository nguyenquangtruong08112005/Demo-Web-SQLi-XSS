<?php
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
        // Prepared statement để chống SQL Injection
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->bind_param("i", $pid); // i: kiểu integer
        $stmt->execute();
        $result = $stmt->get_result();
        if ($product = $result->fetch_assoc()) {
            $total += $product['price'] * $qty;
        }
        $stmt->close();
    }

    // Thêm vào bảng orders với prepared statement
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
    $stmt->bind_param("id", $user_id, $total); // i: integer, d: double
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Thêm từng món vào order_items với prepared statement
    foreach ($cart as $pid => $qty) {
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($product = $result->fetch_assoc()) {
            $price = $product['price'];
            $stmt_insert = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $stmt_insert->bind_param("iiid", $order_id, $pid, $qty, $price); // iiid: integer, double
            $stmt_insert->execute();
            $stmt_insert->close();
        }
        $stmt->close();
    }
}

session_destroy();
header("Location: login.php");
exit();
?>
