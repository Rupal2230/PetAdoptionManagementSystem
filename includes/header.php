<?php if (session_status() === PHP_SESSION_NONE) session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Pet Adoption</title>
    <link rel="stylesheet" href="/PetAdoption/css/style.css">
</head>
<body>
<nav class="navbar">
    <a href="/PetAdoption/home.php" class="brand">🐾 Pet Adoption</a>
    <div class="nav-links">
        <a href="/PetAdoption/home.php">Browse Pets</a>
        <?php if (isset($_SESSION['user_id'])): ?>
            <a href="/PetAdoption/my_requests.php">My Requests</a>
            <?php if ($_SESSION['role'] === 'admin'): ?>
                <a href="/PetAdoption/admin/dashboard.php">Admin Dashboard</a>
            <?php endif; ?>
            <span class="welcome">Hi, <?= htmlspecialchars($_SESSION['full_name']) ?></span>
            <a href="/PetAdoption/logout.php">Logout</a>
        <?php else: ?>
            <a href="/PetAdoption/login.php">Login</a>
            <a href="/PetAdoption/register.php">Register</a>
        <?php endif; ?>
    </div>
</nav>
<main class="container">
