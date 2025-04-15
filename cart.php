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

// Checkout: Tạo đơn hàng và order_items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['create_order'])) {
    $total = 0;

    foreach ($_SESSION['cart'] as $pid => $qty) {
        $pid = (int)$pid;
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($product = $result->fetch_assoc()) {
            $total += $product['price'] * $qty;
        }
        $stmt->close();
    }

    // Thêm order
    $stmt = $conn->prepare("INSERT INTO orders (user_id, total) VALUES (?, ?)");
    $stmt->bind_param("id", $user_id, $total);
    $stmt->execute();
    $order_id = $stmt->insert_id;
    $stmt->close();

    // Thêm các mục vào order_items
    foreach ($_SESSION['cart'] as $pid => $qty) {
        $pid = (int)$pid;
        $stmt = $conn->prepare("SELECT price FROM products WHERE id = ?");
        $stmt->bind_param("i", $pid);
        $stmt->execute();
        $result = $stmt->get_result();
        if ($product = $result->fetch_assoc()) {
            $price = $product['price'];
            $insertItem = $conn->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
            $insertItem->bind_param("iiid", $order_id, $pid, $qty, $price);
            $insertItem->execute();
            $insertItem->close();
        }
        $stmt->close();
    }

    header("Location: checkout.php?order_id=" . urlencode($order_id));
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Cart - Secure Demo</title>
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
          $pid = (int)$pid;
          $stmt = $conn->prepare("SELECT name, price FROM products WHERE id = ?");
          $stmt->bind_param("i", $pid);
          $stmt->execute();
          $result = $stmt->get_result();
          $product = $result->fetch_assoc();
          $stmt->close();
          $subtotal = $product['price'] * $qty;
          $total += $subtotal;
        ?>
        <tr>
          <td><?php echo htmlspecialchars($product['name']); ?></td>
          <td><?php echo number_format($product['price'], 2); ?></td>
          <td><?php echo (int)$qty; ?></td>
          <td><?php echo number_format($subtotal, 2); ?></td>
        </tr>
        <?php endforeach; ?>
        <tr class="table-light">
          <td colspan="3" class="text-end"><strong>Total:</strong></td>
          <td><strong>$<?php echo number_format($total, 2); ?></strong></td>
        </tr>
      </tbody>
    </table>
    <form method="post">
        <button type="submit" name="create_order" class="btn btn-primary">Proceed to Checkout</button>
    </form>
  <?php else: ?>
    <p>Your cart is empty.</p>
  <?php endif; ?>

  <hr>
  <h3>Order History</h3>
  <?php
  $stmt = $conn->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC");
  $stmt->bind_param("i", $user_id);
  $stmt->execute();
  $orders = $stmt->get_result();
  $stmt->close();

  if ($orders->num_rows > 0):
    while ($order = $orders->fetch_assoc()):
      $order_id = $order['id'];
      $stmt = $conn->prepare("SELECT oi.*, p.name FROM order_items oi 
                              JOIN products p ON oi.product_id = p.id
                              WHERE oi.order_id = ?");
      $stmt->bind_param("i", $order_id);
      $stmt->execute();
      $items = $stmt->get_result();
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
              <td><?php echo htmlspecialchars($item['name']); ?></td>
              <td>$<?php echo number_format($item['price'], 2); ?></td>
              <td><?php echo (int)$item['quantity']; ?></td>
              <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            </tr>
            <?php endwhile; ?>
          </tbody>
        </table>
        <a href="checkout.php?order_id=<?php echo urlencode($order_id); ?>" class="btn btn-success btn-sm">Pay This Order</a>
      </div>
    </div>
  <?php 
      $stmt->close();
    endwhile;
  else: ?>
    <p>No past orders found.</p>
  <?php endif; ?>
</div>
</body>
</html>
<?php $conn->close(); ?>
