<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../dbconnect.php';

require_admin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// CSRF check
$token = $_POST['csrf_token'] ?? '';
if (empty($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $token)) {
    http_response_code(403);
    die('Invalid request (CSRF check failed). Go back and try again.');
}

$requestId = (int) ($_POST['request_id'] ?? 0);
$action    = $_POST['action'] ?? '';

if ($requestId <= 0 || !in_array($action, ['approve', 'reject'], true)) {
    header('Location: dashboard.php');
    exit;
}

$newStatus = $action === 'approve' ? 'approved' : 'rejected';

// Only allow acting on requests that are still pending.
$stmt = $pdo->prepare('SELECT id, pet_id, status FROM adoption_requests WHERE id = ?');
$stmt->execute([$requestId]);
$request = $stmt->fetch();

if (!$request || $request['status'] !== 'pending') {
    header('Location: dashboard.php?error=already_decided');
    exit;
}

$pdo->beginTransaction();
try {
    $stmt = $pdo->prepare(
        'UPDATE adoption_requests SET status = ?, decided_at = NOW() WHERE id = ?'
    );
    $stmt->execute([$newStatus, $requestId]);

    if ($newStatus === 'approved') {
        // Mark the pet as adopted...
        $stmt = $pdo->prepare("UPDATE pets SET status = 'adopted' WHERE id = ?");
        $stmt->execute([$request['pet_id']]);

        // ...and auto-reject any other still-pending requests for the same pet.
        $stmt = $pdo->prepare(
            "UPDATE adoption_requests
             SET status = 'rejected', decided_at = NOW()
             WHERE pet_id = ? AND id != ? AND status = 'pending'"
        );
        $stmt->execute([$request['pet_id'], $requestId]);
    }

    $pdo->commit();
} catch (Exception $e) {
    $pdo->rollBack();
    die('Something went wrong updating the request: ' . $e->getMessage());
}

header('Location: dashboard.php?updated=' . urlencode($newStatus));
exit;
