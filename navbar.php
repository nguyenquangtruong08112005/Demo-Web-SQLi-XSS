<?php
// navbar.php

?>
<nav class="navbar navbar-expand-lg navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="dashboard.php">Cell Phone Shop</a>
    <div class="collapse navbar-collapse">
      <ul class="navbar-nav me-auto mb-2 mb-lg-0">
        <?php if (isset($_SESSION['user'])): ?>
            <li class="nav-item">
              <a class="nav-link" href="dashboard.php">Dashboard</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="products.php">Products</a>
            </li>
            <?php if ($_SESSION['role'] == 'admin'): ?>
            <li class="nav-item">
              <a class="nav-link" href="admin_products.php">Manage Products</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="admin_users.php">Manage Users</a>
            </li>
            <?php endif; ?>
            <?php if ($_SESSION['role'] == 'user'): ?>
              <li class="nav-item">
              <a class="nav-link" href="cart.php">Cart</a>
            </li>
            <?php endif; ?>
            <li class="nav-item">
              <a class="nav-link" href="profile.php">Profile</a> 
            <li class="nav-item">
              <a class="nav-link" href="logout.php">Logout</a>
            </li>
        <?php else: ?>
            <li class="nav-item">
              <a class="nav-link" href="login.php">Login</a>
            </li>
            <li class="nav-item">
              <a class="nav-link" href="signup.php">Signup</a>
            </li>
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>
