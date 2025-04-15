<?php
// user_form.php
include 'config.php';
session_start();
if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    die("Access Denied.");
}

$action = isset($_GET['action']) ? $_GET['action'] : 'edit';
$userData = null;
if ($action == 'edit' && isset($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM users WHERE id = $id");
    $userData = $result->fetch_assoc();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $role = $_POST["role"];
    $conn->query("UPDATE users SET role = '$role' WHERE id = $id");    
    header("Location: admin_users.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?php echo ucfirst($action); ?> User - Vulnerable Demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2><?php echo ucfirst($action); ?> User</h2>
    <form method="post">
        <div class="mb-3">
            <label class="form-label">Username</label>
            <input type="text" name="username" class="form-control" value="<?php echo $userData ? $userData['username'] : ''; ?>" disabled>
        </div>
        <div class="mb-3">
            <label class="form-label">Role</label>
            <select name="role" class="form-control">
                <option value="user" <?php if($userData && $userData['role']=='user') echo 'selected'; ?>>User</option>
                <option value="admin" <?php if($userData && $userData['role']=='admin') echo 'selected'; ?>>Admin</option>
            </select>
        </div>
        <button type="submit" class="btn btn-success"><?php echo ucfirst($action); ?></button>
    </form>
</div>
</body>
</html>
<?php $conn->close(); ?>