<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/dbconnect.php';

require_customer(); // only customers browse + request; admins manage via admin/

$petId = (int) ($_GET['id'] ?? 0);
if ($petId <= 0) {
    header('Location: /home.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM pets WHERE id = ?');
$stmt->execute([$petId]);
$pet = $stmt->fetch();

if (!$pet) {
    header('Location: /home.php?error=notfound');
    exit;
}

$errors  = [];
$success = false;

// Has this customer already requested this pet?
$stmt = $pdo->prepare(
    'SELECT status FROM adoption_requests WHERE customer_id = ? AND pet_id = ? ORDER BY requested_at DESC LIMIT 1'
);
$stmt->execute([$_SESSION['user_id'], $petId]);
$existingRequest = $stmt->fetch();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $pet['status'] === 'available' && !$existingRequest) {
    $message = trim($_POST['message'] ?? '');

    $stmt = $pdo->prepare(
        'INSERT INTO adoption_requests (customer_id, pet_id, message, status) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$_SESSION['user_id'], $petId, $message, 'pending']);
    $success = true;

    // refresh existing request state
    $existingRequest = ['status' => 'pending'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($pet['name']) ?> - PetAdoption</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/nav.php'; ?>

<div class="page">
    <a href="/home.php">&larr; Back to all pets</a>

    <div class="pet-detail">
        <img src="<?= htmlspecialchars($pet['image_path'] ?: 'images/placeholder.png') ?>" alt="<?= htmlspecialchars($pet['name']) ?>">
        <div>
            <h2><?= htmlspecialchars($pet['name']) ?></h2>
            <p><strong>Species:</strong> <?= htmlspecialchars($pet['species']) ?></p>
            <?php if ($pet['breed']): ?><p><strong>Breed:</strong> <?= htmlspecialchars($pet['breed']) ?></p><?php endif; ?>
            <?php if ($pet['age'] !== null): ?><p><strong>Age:</strong> <?= (int) $pet['age'] ?> yr(s)</p><?php endif; ?>
            <p><strong>Status:</strong> <?= htmlspecialchars(ucfirst($pet['status'])) ?></p>
            <p><?= nl2br(htmlspecialchars($pet['description'] ?? '')) ?></p>

            <?php if ($success): ?>
                <div class="alert alert-success">Your adoption request has been submitted!</div>
            <?php endif; ?>

            <?php if ($pet['status'] !== 'available'): ?>
                <p class="badge">This pet has already been adopted.</p>
            <?php elseif ($existingRequest): ?>
                <p class="badge">You already requested this pet &mdash; status: <strong><?= htmlspecialchars(ucfirst($existingRequest['status'])) ?></strong>.</p>
            <?php else: ?>
                <form method="POST" action="pet_details.php?id=<?= (int) $pet['id'] ?>">
                    <label for="message">Message to the shelter (optional)</label>
                    <textarea id="message" name="message" rows="4" placeholder="Tell us why you'd be a great fit for <?= htmlspecialchars($pet['name']) ?>..."></textarea>
                    <button type="submit">Submit Adoption Request</button>
                </form>
            <?php endif; ?>
        </div>
    </div>
</div>
</body>
</html>
