<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../dbconnect.php';

require_admin(); // blocks non-admins and guests

// CSRF token for the accept/reject forms below.
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

$stmt = $pdo->query(
    "SELECT ar.id, ar.status, ar.requested_at, ar.message,
            p.id AS pet_id, p.name AS pet_name, p.species,
            u.id AS customer_id, u.name AS customer_name, u.email AS customer_email
     FROM adoption_requests ar
     JOIN pets p ON p.id = ar.pet_id
     JOIN users u ON u.id = ar.customer_id
     ORDER BY (ar.status = 'pending') DESC, ar.requested_at DESC"
);
$requests = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Admin Dashboard - PetAdoption</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/nav.php'; ?>

<div class="page">
    <h2>Adoption Requests</h2>

    <?php if (isset($_GET['updated'])): ?>
        <div class="alert alert-success">Request <?= htmlspecialchars($_GET['updated']) ?>.</div>
    <?php endif; ?>

    <?php if (empty($requests)): ?>
        <p>No adoption requests have been submitted yet.</p>
    <?php else: ?>
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Pet</th>
                    <th>Customer</th>
                    <th>Message</th>
                    <th>Requested On</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($requests as $r): ?>
                    <tr>
                        <td><?= htmlspecialchars($r['pet_name']) ?> (<?= htmlspecialchars($r['species']) ?>)</td>
                        <td><?= htmlspecialchars($r['customer_name']) ?><br><small><?= htmlspecialchars($r['customer_email']) ?></small></td>
                        <td><?= htmlspecialchars($r['message'] ?: '&mdash;') ?></td>
                        <td><?= htmlspecialchars($r['requested_at']) ?></td>
                        <td>
                            <span class="status-badge status-<?= htmlspecialchars($r['status']) ?>">
                                <?= htmlspecialchars(ucfirst($r['status'])) ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($r['status'] === 'pending'): ?>
                                <form method="POST" action="update_request.php" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                                    <input type="hidden" name="action" value="approve">
                                    <button type="submit" class="btn btn-accept">Accept</button>
                                </form>
                                <form method="POST" action="update_request.php" class="inline-form">
                                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                    <input type="hidden" name="request_id" value="<?= (int) $r['id'] ?>">
                                    <input type="hidden" name="action" value="reject">
                                    <button type="submit" class="btn btn-reject">Reject</button>
                                </form>
                            <?php else: ?>
                                &mdash;
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
