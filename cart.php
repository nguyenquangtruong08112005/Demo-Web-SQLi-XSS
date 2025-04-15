<?php
session_start();
include 'config.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}
$user_id = $_SESSION['user_id'];

// Nếu form checkout được submit, tạo đơn hàng và chuyển hướng
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $total = 0;
    foreach ($_SESSION['cart'] as $pid => $qty) {
        $result = $conn->query("SELECT * FROM products WHERE id = $pid");
        if ($product = $result->fetch_assoc()) {
            $total += $product['price'] * $qty;
        }
    }
    // Tạo đơn hàng vào bảng orders
    $conn->query("INSERT INTO orders (user_id, total) VALUES ($user_id, $total)");
    $order_id = $conn->insert_id;

    // Thêm các mục trong giỏ hàng vào bảng order_items
    foreach ($_SESSION['cart'] as $pid => $qty) {
        $result = $conn->query("SELECT * FROM products WHERE id = $pid");
        if ($product = $result->fetch_assoc()) {
            $price = $product['price'];
            $conn->query("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES ($order_id, $pid, $qty, $price)");
        }
    }

    // Chuyển hướng đến checkout với order_id
    header("Location: checkout.php?order_id=$order_id");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cart - Vulnerable Demo</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h2>Your Cart</h2>

  <?php if (!empty($_SESSION['cart'])): ?>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>Product Name</th>
          <th>Price</th>
          <th>Quantity</th>
          <th>Subtotal</th>
        </tr>
      </thead>
      <tbody>
        <?php 
        $total = 0;
        foreach ($_SESSION['cart'] as $pid => $qty): 
          $result = $conn->query("SELECT * FROM products WHERE id = $pid");
          $product = $result->fetch_assoc();
          $subtotal = $product['price'] * $qty;
          $total += $subtotal;
        ?>
        <tr>
          <td><?php echo $product['name']; ?></td>
          <td><?php echo number_format($product['price'], 2); ?></td>
          <td><?php echo $qty; ?></td>
          <td><?php echo number_format($subtotal, 2); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="table-light">
          <td colspan="3" class="text-end"><strong>Total:</strong></td>
          <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
        </tr>
      </tbody>
    </table>
    <!-- Thay link Proceed to Checkout bằng form submit -->
    <form method="post">
        <button type="submit" name="create_order" class="btn btn-primary">Proceed to Checkout</button>
    </form>
  <?php else: ?>
    <p>Your cart is empty.</p>
  <?php endif; ?>

  <hr>
  <!-- Phần Order History (không thay đổi) -->
  <h3>Order History</h3>
  <?php
  $orders = $conn->query("SELECT * FROM orders WHERE user_id = $user_id ORDER BY created_at DESC");
  if ($orders->num_rows > 0):
    while ($order = $orders->fetch_assoc()):
      $order_id = $order['id'];
      $items = $conn->query("SELECT oi.*, p.name, p.price FROM order_items oi 
                             JOIN products p ON oi.product_id = p.id
                             WHERE oi.order_id = $order_id");
  ?>
    <div class="card mb-4">
      <div class="card-header">
        <strong>Order #<?php echo $order_id; ?></strong> -
        <em><?php echo $order['created_at']; ?></em> -
        <strong>Total:</strong> $<?php echo number_format($order['total'], 2); ?>
      </div>
      <div class="card-body">
        <table class="table table-sm">
          <thead>
            <tr>
              <th>Product</th><th>Price</th><th>Quantity</th><th>Subtotal</th>
            </tr>
          </thead>
          <tbody>
            <?php while ($item = $items->fetch_assoc()): ?>
            <tr>
              <td><?php echo $item['name']; ?></td>
              <td>$<?php echo number_format($item['price'], 2); ?></td>
              <td><?php echo $item['quantity']; ?></td>
              <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <a href="checkout.php?order_id=<?php echo $order_id; ?>" class="btn btn-success btn-sm">Pay This Order</a>
      </div>
    </div>
  <?php endwhile; else: ?>
    <p>No past orders found.</p>
  <?php endif; ?>
</div>
</body>
</html>
<?php $conn->close(); ?>
