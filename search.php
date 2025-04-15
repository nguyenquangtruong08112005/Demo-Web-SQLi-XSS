<?php
// search.php
include 'config.php';
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}
$q = isset($_GET['q']) ? $_GET['q'] : '';
// VULNERABLE: Query nối trực tiếp
$sql = "SELECT * FROM products WHERE name LIKE '%$q%'";
$result = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search - Vulnerable Demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Search Results</h2>
    <!-- Reflected XSS: Hiển thị dữ liệu tìm kiếm không escape -->
    <?php if ($q != ""): ?>
      <div class="alert alert-info">You searched for: <?php echo $q; ?></div>
    <?php endif; ?>
    <div class="row">
    <?php while ($product = $result->fetch_assoc()): ?>
        <div class="col-md-4">
          <div class="card mb-4">
            <?php if (!empty($product['image_url'])): ?>
              <img src="<?php echo $product['image_url']; ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
              <img src="https://via.placeholder.com/150" class="card-img-top" alt="No Image">
            <?php endif; ?>
            <div class="card-body">
              <h5 class="card-title"><?php echo $product['name']; ?></h5>
              <!-- Stored XSS: Output description không escape -->
              <p class="card-text"><?php echo $product['description']; ?></p>
              <p class="card-text"><strong>Price: $<?php echo $product['price']; ?></strong></p>
              <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
            </div>
          </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>
</body>
</html>
