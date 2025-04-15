<?php
include 'config.php';
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$role = $_SESSION['role'];
$message = "";

// Cập nhật thông tin
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    if (isset($_POST['update_profile'])) {
        $username = $_POST['username'];
        $email = $_POST['email'];
        $address = $_POST['address'];

        if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === 0 && $_FILES['avatar']['size'] < 2 * 1024 * 1024) {
            $avatarData = $conn->real_escape_string(file_get_contents($_FILES['avatar']['tmp_name']));
            $conn->query("UPDATE users SET username='$username', email='$email', address='$address', avatar='$avatarData' WHERE id=$user_id");
        } else {
            $conn->query("UPDATE users SET username='$username', email='$email', address='$address' WHERE id=$user_id");
        }

        $_SESSION['user'] = $username;
        $_SESSION['email'] = $email;
        $message = "Profile updated!";
    }

    // Đổi mật khẩu
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'];
        $new = $_POST['new_password'];
        $confirm = $_POST['confirm_password'];

        $check = $conn->query("SELECT password FROM users WHERE id=$user_id");
        $user = $check->fetch_assoc();

        if ($user['password'] !== $current) {
            $message = "Wrong current password!";
        } elseif ($new !== $confirm) {
            $message = "New passwords do not match!";
        } else {
            $conn->query("UPDATE users SET password='$new' WHERE id=$user_id");
            $message = "Password changed!";
        }
    }
}

// Lấy lại thông tin user sau cập nhật
$result = $conn->query("SELECT * FROM users WHERE id = $user_id");
$user = $result->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Profile - Vulnerable Demo</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
<!-- Chỗ này có xài bootstrap để hiển thị modal reset mật khẩu -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <style>
    .avatar-img { width: 240px; height: 240px; border-radius: 50%; object-fit: cover; }
    .profile-container { display: flex; align-items: start; gap: 30px; flex-wrap: wrap; }
  </style>
</head>
<body>
<?php include 'navbar.php'; ?>
<div class="container mt-4">
  <h2 class="text-center">My Profile</h2>
  <?php if ($message): ?>
    <div class="alert alert-info"><?php echo $message; ?></div>
  <?php endif; ?>

  <div class="profile-container">
    <div class="d-flex flex-column align-items-center">
      <?php if ($user['avatar']): ?>
        <img src="image.php?type=avatar&id=<?php echo $user_id; ?>" class="avatar-img mb-3" alt="Avatar">
      <?php else: ?>
        <img src="https://via.placeholder.com/240" class="avatar-img mb-3" alt="No Avatar">
      <?php endif; ?>
        <div>Avatar</div>
    </div>
    
    <form method="post" class="flex-grow-1">
      <div class="mb-3">
        <label class="form-label">Username</label>
        <input class="form-control" name="username" value="<?php echo $user['username'] ?? ''; ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Email</label>
        <input class="form-control" type="email" name="email" value="<?php echo $user['email'] ?? ''; ?>" required>
      </div>
      <div class="mb-3">
        <label class="form-label">Address</label>
        <input class="form-control" name="address" value="<?php echo $user['address'] ?? ''; ?>">
      </div>

      <div class="mb-3">
        <label class="form-label">Upload New Avatar (max 2MB, file .png)</label>
        <input type="file" class="form-control" name="avatar">
      </div>

      <div class="d-flex gap-2 mt-3">
            <button class="btn btn-primary" name="update_profile">Update Profile</button>
            <button class="btn btn-outline-secondary" type="button" data-bs-toggle="modal" data-bs-target="#passwordModal">Change Password</button>
        </div>
    </form>
    
  </div>
    <hr>
<!-- Khúc này là dùng modal có trong bootstrap để hiển thị form reset password cho đẹp hơn -->
<div class="modal fade" id="passwordModal" tabindex="-1" aria-labelledby="passwordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <form method="post">
        <div class="modal-header">
          <h5 class="modal-title" id="passwordModalLabel">Change Password</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body row g-3">
          <div class="col-md-4">
            <label class="form-label">Current Password</label>
            <input type="password" class="form-control" name="current_password" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">New Password</label>
            <input type="password" class="form-control" name="new_password" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Confirm New Password</label>
            <input type="password" class="form-control" name="confirm_password" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="submit" class="btn btn-success" name="change_password">Change Password</button>
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        </div>
      </form>
    </div>
  </div>
</div>

    <script>
    function togglePasswordForm() {
        const form = document.getElementById("passwordForm");
        form.style.display = (form.style.display === "none") ? "block" : "none";
    }
    </script>

</body>
</html>
<?php $conn->close(); ?>
