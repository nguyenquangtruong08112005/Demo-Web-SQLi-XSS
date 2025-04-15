<?php
// admin_products.php
include 'config.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    die("Access Denied: You don't have permission to view this page.");
}
$sql = "SELECT products.*, categories.name AS category_name 
        FROM products 
        LEFT JOIN categories ON products.category_id = categories.id";
$result = $conn->query($sql);
?>
<?php
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $pid = $_GET['id'];
    $conn->query("DELETE FROM products WHERE id = $pid");
    header("Location: admin_products.php");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Products - Vulnerable Demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Manage Products</h2>
    <a href="product_form.php?action=add" class="btn btn-success mb-3">Add New Product</a>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Name</th>
          <th>Price</th>
          <th>Category</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($product = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo $product['id']; ?></td>
          <td><?php echo $product['name']; ?></td>
          <td><?php echo $product['price']; ?></td>
          <td><?php echo $product['category_name']; ?></td>
          <td>
             <a href="product_form.php?action=edit&id=<?php echo $product['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
             <form action="admin_products.php?action=delete&id=<?php echo $product['id']; ?>" method="post" style="display:inline-block;" onsubmit="return confirm('Are you sure?');">
                 <button type="submit" class="btn btn-danger btn-sm">Delete</button>
             </form>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
</div>

</body>
</html>
<?php $conn->close(); ?>