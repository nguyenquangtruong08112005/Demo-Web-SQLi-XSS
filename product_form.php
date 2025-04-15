<?php
// product_form.php
include 'config.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    die("Access Denied.");
}

$action = isset($_GET['action']) ? $_GET['action'] : 'add';
$product = null;
$imageData = null;

// Load danh sách categories
$categories = $conn->query("SELECT * FROM categories");

if ($action == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    // Sử dụng prepared statement để tránh SQL Injection
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $id); // "i" là kiểu dữ liệu integer
    $stmt->execute();
    $result = $stmt->get_result();
    $product = $result->fetch_assoc();
    $stmt->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST["name"];
    $description = $_POST["description"];
    $price = $_POST["price"];
    $category_id = $_POST["category_id"];

    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $imageData = file_get_contents($_FILES['image']['tmp_name']);
        $imageData = $conn->real_escape_string($imageData);
    }

    if ($action == 'add') {
        // Sử dụng prepared statement để tránh SQL Injection
        $stmt = $conn->prepare("INSERT INTO products (name, description, price, category_id" . ($imageData ? ", image" : "") . ") 
                VALUES (?, ?, ?, ?" . ($imageData ? ", ?" : "") . ")");
        if ($imageData) {
            $stmt->bind_param("ssdis", $name, $description, $price, $category_id, $imageData); 
        } else {
            $stmt->bind_param("ssdi", $name, $description, $price, $category_id); 
        }
        $stmt->execute();
        $stmt->close();
    } else if ($action == 'edit') {
        $stmt = $conn->prepare("UPDATE products SET name = ?, description = ?, price = ?, category_id = ?" . ($imageData ? ", image = ?" : "") . " WHERE id = ?");
        if ($imageData) {
            $stmt->bind_param("ssdisi", $name, $description, $price, $category_id, $imageData, $id); 
        } else {
            $stmt->bind_param("ssdi", $name, $description, $price, $category_id, $id); 
        }
        $stmt->execute();
        $stmt->close();
    }

    header("Location: admin_products.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo ucfirst($action); ?> Product</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2><?php echo ucfirst($action); ?> Product</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo $product ? htmlspecialchars($product['name']) : ''; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" required><?php echo $product ? htmlspecialchars($product['description']) : ''; ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product ? htmlspecialchars($product['price']) : ''; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-control" required>
                <option value="">-- Select Category --</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>"
                        <?php if ($product && $product['category_id'] == $cat['id']) echo 'selected'; ?>>
                        <?php echo htmlspecialchars($cat['name']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="mb-3">
            <label class="form-label">Image Upload</label>
            <input type="file" name="image" class="form-control">
            <?php if ($product && $product['image']): ?>
                <p class="mt-2">Current image: <img src="image.php?id=<?php echo $product['id']; ?>" height="100"></p>
            <?php endif; ?>
        </div>
        <button type="submit" class="btn btn-success"><?php echo ucfirst($action); ?></button>
    </form>
</div>
</body>
</html>
<?php $conn->close(); ?>
