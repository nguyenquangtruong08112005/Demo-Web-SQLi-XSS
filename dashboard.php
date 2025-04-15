<?php
// dashboard.php
session_start();
if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Dashboard</h2>
    <p>Welcome, <?php echo $_SESSION['user']; ?>!</p>
    <p>Your role: <?php echo $_SESSION['role']; ?></p>
    <p>Check out our <a href="products.php">Products</a> for great deals!</p>
</div>
</body>
</html>
