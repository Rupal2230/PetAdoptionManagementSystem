<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/dbconnect.php';

require_login(); // browsing requires being logged in; adjust if you want public browsing

// Admins land here too if they navigate back manually; send them to their own dashboard.
if (is_admin()) {
    header('Location: /admin/dashboard.php');
    exit;
}

$stmt = $pdo->query(
    "SELECT id, name, species, breed, age, image_path
     FROM pets
     WHERE status = 'available'
     ORDER BY created_at DESC"
);
$pets = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Available Pets - PetAdoption</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/nav.php'; ?>

<div class="page">
    <h2>Available Pets</h2>

    <?php if (isset($_GET['welcome'])): ?>
        <div class="alert alert-success">Welcome, <?= htmlspecialchars($_SESSION['name']) ?>! Browse pets below.</div>
    <?php endif; ?>
    <?php if (isset($_GET['error']) && $_GET['error'] === 'forbidden'): ?>
        <div class="alert alert-error">You don't have access to that page.</div>
    <?php endif; ?>

    <?php if (empty($pets)): ?>
        <p>No pets are available for adoption right now. Check back soon!</p>
    <?php else: ?>
        <div class="pet-grid">
            <?php foreach ($pets as $pet): ?>
                <div class="pet-card">
                    <img src="<?= htmlspecialchars($pet['image_path'] ?: 'images/placeholder.png') ?>" alt="<?= htmlspecialchars($pet['name']) ?>">
                    <h3><?= htmlspecialchars($pet['name']) ?></h3>
                    <p><?= htmlspecialchars($pet['species']) ?><?= $pet['breed'] ? ' &middot; ' . htmlspecialchars($pet['breed']) : '' ?></p>
                    <p><?= $pet['age'] !== null ? htmlspecialchars($pet['age']) . ' yr(s) old' : '' ?></p>
                    <a class="btn" href="pet_details.php?id=<?= (int) $pet['id'] ?>">View Details</a>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
