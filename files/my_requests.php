<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/dbconnect.php';

require_customer();

$stmt = $pdo->prepare(
    "SELECT ar.id, ar.status, ar.requested_at, ar.decided_at, ar.message,
            p.id AS pet_id, p.name AS pet_name, p.species, p.image_path
     FROM adoption_requests ar
     JOIN pets p ON p.id = ar.pet_id
     WHERE ar.customer_id = ?
     ORDER BY ar.requested_at DESC"
);
$stmt->execute([$_SESSION['user_id']]);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>My Requests - PetAdoption</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<?php include __DIR__ . '/includes/nav.php'; ?>

<div class="page">
    <h2>My Adoption Requests</h2>

    <?php if (empty($requests)): ?>
        <p>You haven't submitted any adoption requests yet. <a href="/home.php">Browse pets</a> to get started.</p>
    <?php else: ?>
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Pet</th>
                    <th>Species</th>
                    <th>Requested On</th>
                    <th>Status</th>
                    <th>Decided On</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><a href="/pet_details.php?id=<?= (int) $r['pet_id'] ?>"><?= htmlspecialchars($r['pet_name']) ?></a></td>
                        <td><?= htmlspecialchars($r['species']) ?></td>
                        <td><?= htmlspecialchars($r['requested_at']) ?></td>
                        <td>
                            <span class="status-badge status-<?= htmlspecialchars($r['status']) ?>">
                                <?= htmlspecialchars(ucfirst($r['status'])) ?>
                            </span>
                        </td>
                        <td><?= $r['decided_at'] ? htmlspecialchars($r['decided_at']) : '&mdash;' ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
