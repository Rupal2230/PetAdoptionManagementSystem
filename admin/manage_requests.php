<?php
require "admin_check.php";
require "../dbconnect.php";

// Handle approve/reject action
if (isset($_GET['action'], $_GET['id'])) {
    $req_id = (int)$_GET['id'];
    $action = $_GET['action'];

    if ($action === 'approve' || $action === 'reject') {
        $new_status = $action === 'approve' ? 'approved' : 'rejected';

        $stmt = $conn->prepare("UPDATE adoption_requests SET status = ? WHERE request_id = ?");
        $stmt->bind_param("si", $new_status, $req_id);
        $stmt->execute();

        // Get the pet_id tied to this request
        $get_pet = $conn->prepare("SELECT pet_id FROM adoption_requests WHERE request_id = ?");
        $get_pet->bind_param("i", $req_id);
        $get_pet->execute();
        $pet_row = $get_pet->get_result()->fetch_assoc();

        if ($pet_row) {
            $pet_status = $action === 'approve' ? 'adopted' : 'available';
            $update_pet = $conn->prepare("UPDATE pets SET status = ? WHERE pet_id = ?");
            $update_pet->bind_param("si", $pet_status, $pet_row['pet_id']);
            $update_pet->execute();
        }
    }
    header("Location: manage_requests.php");
    exit;
}

$requests = $conn->query("
    SELECT ar.request_id, ar.message, ar.status, ar.request_date,
           p.name AS pet_name, u.full_name, u.email
    FROM adoption_requests ar
    JOIN pets p ON ar.pet_id = p.pet_id
    JOIN users u ON ar.user_id = u.user_id
    ORDER BY ar.request_date DESC
");

require "../includes/header.php";
?>

<h2>Manage Adoption Requests</h2>

<table class="data-table">
    <tr>
        <th>Pet</th>
        <th>Requested By</th>
        <th>Message</th>
        <th>Status</th>
        <th>Date</th>
        <th>Actions</th>
    </tr>
    <?php while ($req = $requests->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($req['pet_name']) ?></td>
            <td><?= htmlspecialchars($req['full_name']) ?><br><small><?= htmlspecialchars($req['email']) ?></small></td>
            <td><?= htmlspecialchars($req['message']) ?></td>
            <td><span class="status-<?= htmlspecialchars($req['status']) ?>"><?= htmlspecialchars(ucfirst($req['status'])) ?></span></td>
            <td><?= htmlspecialchars($req['request_date']) ?></td>
            <td>
                <?php if ($req['status'] === 'pending'): ?>
                    <a href="manage_requests.php?action=approve&id=<?= (int)$req['request_id'] ?>">Approve</a> |
                    <a href="manage_requests.php?action=reject&id=<?= (int)$req['request_id'] ?>">Reject</a>
                <?php else: ?>
                    &mdash;
                <?php endif; ?>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<p><a href="dashboard.php">&larr; Back to Dashboard</a></p>

<?php require "../includes/footer.php"; ?>
