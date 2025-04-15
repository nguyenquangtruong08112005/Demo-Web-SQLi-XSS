<?php
// admin_users.php
include 'config.php';
session_start();

if (!isset($_SESSION['user']) || $_SESSION['role'] != 'admin') {
    die("Access Denied: You don't have permission to view this page.");
}
$result = $conn->query("SELECT id, username, role FROM users");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Vulnerable Demo</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
    <h2>Manage Users</h2>
    <table class="table table-bordered">
      <thead>
        <tr>
          <th>ID</th>
          <th>Username</th>
          <th>Role</th>
          <th>Actions</th>
        </tr>
      </thead>
      <tbody>
        <?php while ($user = $result->fetch_assoc()): ?>
        <tr>
          <td><?php echo $user['id']; ?></td>
          <td><?php echo $user['username']; ?></td>
          <td><?php echo $user['role']; ?></td>
          <td>
             <a href="user_form.php?action=edit&id=<?php echo $user['id']; ?>" class="btn btn-primary btn-sm">Edit</a>
          </td>
        </tr>
        <?php endwhile; ?>
      </tbody>
    </table>
</div>
<?php

?>
</body>
</html>
<?php $conn->close(); ?>