<?php
// search.php
include 'config.php';
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$q = isset($_GET['q']) ? $_GET['q'] : '';

// Bảo vệ chống SQL Injection với prepared statements
$stmt = $conn->prepare("SELECT * FROM products WHERE name LIKE ?");
$searchTerm = "%" . $q . "%";  // Thêm dấu % cho phép tìm kiếm chứa chuỗi
$stmt->bind_param("s", $searchTerm);  
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Search</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Search Results</h2>
    <!-- Reflected XSS: Hiển thị dữ liệu tìm kiếm đã escape -->
    <?php if ($q != ""): ?>
      <div class="alert alert-info">You searched for: <?php echo htmlspecialchars($q); ?></div>
    <?php endif; ?>
    <div class="row">
    <?php while ($product = $result->fetch_assoc()): ?>
        <div class="col-md-4">
          <div class="card mb-4">
            <?php if (!empty($product['image_url'])): ?>
              <img src="<?php echo htmlspecialchars($product['image_url']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($product['name']); ?>">
            <?php else: ?>
              <img src="https://via.placeholder.com/150" class="card-img-top" alt="No Image">
            <?php endif; ?>
            <div class="card-body">
              <h5 class="card-title"><?php echo htmlspecialchars($product['name']); ?></h5>
              <p class="card-text"><?php echo htmlspecialchars($product['description']); ?></p>
              <p class="card-text"><strong>Price: $<?php echo htmlspecialchars($product['price']); ?></strong></p>
              <a href="product.php?id=<?php echo $product['id']; ?>" class="btn btn-primary">View Details</a>
            </div>
          </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>
</body>
</html>

<?php
$stmt->close(); 
$conn->close(); 
?>
