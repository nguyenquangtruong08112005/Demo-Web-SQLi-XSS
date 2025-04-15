<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
$type = $_GET['type'] ?? 'product';

if ($type === 'avatar') {
    $query = "SELECT avatar AS image FROM users WHERE id = $id";
} else {
    $query = "SELECT image FROM products WHERE id = $id";
}

$result = $conn->query($query);
$row = $result->fetch_assoc();

if ($row && !empty($row['image'])) {
    header("Content-Type: image/png"); 
    echo $row['image'];
} else {
    header("Content-Type: image/png");
    readfile("https://via.placeholder.com/150");
}
