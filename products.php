<?php
// products.php
include 'config.php';
session_start();
if (!isset($_SESSION['user'])) {
  header("Location: login.php");
  exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['product_id'])) {
  $pid = $_POST['product_id'];
  if (!isset($_SESSION['cart'])) $_SESSION['cart'] = [];
  if (isset($_SESSION['cart'][$pid])) {
      $_SESSION['cart'][$pid]++;
  } else {
      $_SESSION['cart'][$pid] = 1;
  }
}

// Lọc từ khóa tìm kiếm và sử dụng prepared statement để tránh SQL Injection
$search = isset($_GET['search']) ? $_GET['search'] : '';
if ($search != "") {
    // Prepared statement để bảo vệ chống SQL Injection
    $stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
    $searchTerm = "%$search%";
    $stmt->bind_param("s", $searchTerm); 
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();
} else {
    $result = $conn->query("SELECT * FROM products");
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Products</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>

<div class="container mt-4">
    <h1>Search</h1>
    <form method="GET" class="mb-4">
      <div class="input-group">
        <input type="text" class="form-control" name="search" placeholder="Search by product name" value="<?php echo htmlspecialchars($search); ?>">
        <button class="btn btn-outline-secondary" type="submit">Search</button>
      </div>
    </form>

    <h2>Products</h2>
  
    <div class="row">
    <?php while ($product = $result->fetch_assoc()): ?>
        <div class="col-md-4">
          <div class="card mb-4">
            <?php if (!empty($product['image'])): ?>
              <img src="image.php?id=<?php echo $product['id']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
              <img src="https://via.placeholder.com/150" class="card-img-top" alt="No Image">
            <?php endif; ?>
            <div class="card-body">
              <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
              <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
              <p class="card-text"><strong>Price: $<?php echo htmlspecialchars($product['price']); ?></strong></p>
              <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
              <?php if ($_SESSION['role'] == 'user'): ?>
                <form method="post" class="d-inline">
                    <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
                    <button type="submit" class="btn btn-success btn-sm">Add to Cart</button>
                </form>
              <?php endif; ?>
            </div>
          </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>
