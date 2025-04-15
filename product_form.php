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
    $result = $conn->query("SELECT * FROM products WHERE id = $id");
    $product = $result->fetch_assoc();
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
        $sql = "INSERT INTO products (name, description, price, category_id" . ($imageData ? ", image" : "") . ") 
                VALUES ('$name', '$description', $price, $category_id" . ($imageData ? ", '$imageData'" : "") . ")";
    } else if ($action == 'edit') {
        $sql = "UPDATE products SET name = '$name', description = '$description', price = $price, category_id = $category_id";
        if ($imageData) {
            $sql .= ", image = '$imageData'";
        }
        $sql .= " WHERE id = $id";
    }

    if ($conn->query($sql)) {
        header("Location: admin_products.php");
        exit();
    } else {
        echo "Something went wrong!";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo ucfirst($action); ?> Product - Vulnerable Demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2><?php echo ucfirst($action); ?> Product</h2>
    <form method="post" enctype="multipart/form-data">
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="<?php echo $product ? $product['name'] : ''; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Description</label>
            <textarea name="description" class="form-control" required><?php echo $product ? $product['description'] : ''; ?></textarea>
        </div>
        <div class="mb-3">
            <label class="form-label">Price</label>
            <input type="number" step="0.01" name="price" class="form-control" value="<?php echo $product ? $product['price'] : ''; ?>" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Category</label>
            <select name="category_id" class="form-control" required>
                <option value="">-- Select Category --</option>
                <?php while ($cat = $categories->fetch_assoc()): ?>
                    <option value="<?php echo $cat['id']; ?>"
                        <?php if ($product && $product['category_id'] == $cat['id']) echo 'selected'; ?>>
                        <?php echo $cat['name']; ?>
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
