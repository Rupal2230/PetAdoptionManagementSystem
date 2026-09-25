<?php
// Expects auth.php to already be included/session started by the caller.
?>
<nav class="navbar">
    <a class="brand" href="/home.php">PetAdoption</a>
    <div class="nav-links">
        <?php if (is_logged_in()): ?>
            <?php if (is_admin()): ?>
                <a href="/admin/dashboard.php">Admin Dashboard</a>
                <a href="/admin/pets.php">Manage Pets</a>
            <?php else: ?>
                <a href="/home.php">Browse Pets</a>
                <a href="/my_requests.php">My Requests</a>
            <?php endif; ?>
            <span class="nav-user">Hi, <?= htmlspecialchars($_SESSION['name'] ?? '') ?></span>
            <a href="/logout.php">Logout</a>
        <?php else: ?>
            <a href="/login.php">Login</a>
            <a href="/register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>
