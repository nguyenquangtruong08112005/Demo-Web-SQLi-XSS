<?php
// checkout.php
session_start();
include 'config.php';

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$message = "";
$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : null;
$user_id = $_SESSION['user_id'];

$order_info = null;
$order_items = [];

if ($order_id) {
    $check = $conn->query("SELECT * FROM orders WHERE id = $order_id AND user_id = $user_id");
    if ($check->num_rows > 0) {
        $order_info = $check->fetch_assoc();
        $order_items = $conn->query("
            SELECT oi.*, p.name, p.price
            FROM order_items oi
            JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = $order_id
        ");
    }
}

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['confirm_payment'])) {
    $shipping_address = $_POST["shipping_address"];
    $recipient = $_POST["recipient"];
    $confirm_pass = $_POST["confirm_pass"];

    // Check from database if password matches
    $check = $conn->query("SELECT password FROM users WHERE id = $user_id");
    $user = $check->fetch_assoc();

    if ($confirm_pass !== $user['password']) {
        $message = "Password confirmation failed!";
    } else {
        $message .= "<h5>Order Confirmation</h5>";
        $message .= "Shipping to: <strong>$shipping_address</strong><br>";
        $message .= "Recipient: <strong>$recipient</strong><br><br>";
        $qrCode = '<img src="https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=PAY-ORDER-' . $order_id . '" alt="QR Code">';
        $message .= "<div class='mt-3'><strong>Scan this QR to complete payment:</strong><br>$qrCode</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Checkout - Vulnerable Demo</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h2>Checkout</h2>

  <?php if ($message): ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
  <?php elseif ($order_info): ?>
    <h5>Order #<?php echo $order_info['id']; ?> - Total: $<?php echo number_format($order_info['total'], 2); ?></h5>
    <table class="table table-bordered">
      <thead><tr><th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th></tr></thead>
      <tbody>
      <?php while ($item = $order_items->fetch_assoc()): ?>
        <tr>
          <td><?php echo $item['name']; ?></td>
          <td>$<?php echo number_format($item['price'], 2); ?></td>
          <td><?php echo $item['quantity']; ?></td>
          <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
        </tr>
      <?php endwhile; ?>
      </tbody>
    </table>

    <!-- Form xác nhận thanh toán -->
    <form method="post">
      <div class="mb-3">
        <label class="form-label">Shipping Address:</label>
        <input type="text" class="form-control" name="shipping_address" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Recipient Name:</label>
        <input type="text" class="form-control" name="recipient" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Confirm Password:</label>
        <input type="password" class="form-control" name="confirm_pass" required>
      </div>
      <button type="submit" class="btn btn-primary" name="confirm_payment">Confirm and Pay</button>
    </form>

  <?php else: ?>
    <p class="text-danger">No order selected, or you don't have permission to access this order.</p>
  <?php endif; ?>
</div>
</body>
</html>
<?php $conn->close(); ?>
