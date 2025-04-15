<?php
session_start();
// product.php
include 'config.php';
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$id = isset($_GET['id']) ? $_GET['id'] : '';

// Sử dụng prepared statement để bảo vệ chống SQL Injection
$stmt = $conn->prepare("
    SELECT products.*, categories.name AS category_name
    FROM products
    LEFT JOIN categories ON products.category_id = categories.id
    WHERE products.id = ?");
$stmt->bind_param("i", $id); 
$stmt->execute();
$result = $stmt->get_result();
$product = $result->fetch_assoc();
$stmt->close();

if (!$product) {
    die("Product not found!");
}
$conn->close();
?>
<?php include 'config.php'; 
    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $product_id = $_POST['product_id'];
        $comment = $_POST['comment'];

        // Sử dụng prepared statement để bảo vệ chống SQL Injection
        $stmt = $conn->prepare("INSERT INTO comments (product_id, text, user_id) VALUES (?, ?, ?)");
        $stmt->bind_param("isi", $product_id, $comment, $_SESSION['user_id']); 
        $stmt->execute();
        $stmt->close();
    }
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($product['name']); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2><?php echo htmlspecialchars($product['name']); ?></h2>
    <?php if (!empty($product['image'])): ?>
      <img src="image.php?id=<?php echo $product['id']; ?>" class="img-fluid mb-3" alt="<?php echo htmlspecialchars($product['name']); ?>">
    <?php endif; ?>
    <p><?php echo htmlspecialchars($product['description']); ?></p>
    <p><strong>Price: $<?php echo htmlspecialchars($product['price']); ?></strong></p>
    <p>Category: <?php echo htmlspecialchars($product['category_name']); ?></p>
    <a href="products.php" class="btn btn-secondary">Back to Products</a>
    <hr>
    <!-- Hiển thị bình luận cho sản phẩm -->
    <?php
    include 'config.php';
    $stmt = $conn->prepare("SELECT * FROM comments WHERE product_id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result_comments = $stmt->get_result();
    ?>
    <h4>Comments</h4>
    <form method="post" action="product.php?id=<?php echo $id; ?>">
        <input type="hidden" name="product_id" value="<?php echo $id; ?>">
        <div class="mb-3">
            <textarea name="comment" class="form-control" placeholder="Enter comment" required></textarea>
        </div>
        <button type="submit" class="btn btn-primary">Post Comment</button>
    </form>
    <br>
    <?php while ($row = $result_comments->fetch_assoc()): ?>
        <p><?php echo htmlspecialchars($_SESSION['user']); ?>: <?php echo htmlspecialchars($row['text']); ?></p>
    <?php endwhile; ?>
</div>
</body>
</html>
<?php $conn->close(); ?>
