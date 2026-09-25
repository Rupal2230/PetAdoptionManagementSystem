<?php
require "admin_check.php";
require "../dbconnect.php";

// Handle delete pet
if (isset($_GET['delete'])) {
    $del_id = (int)$_GET['delete'];
    $stmt = $conn->prepare("DELETE FROM pets WHERE pet_id = ?");
    $stmt->bind_param("i", $del_id);
    $stmt->execute();
    header("Location: dashboard.php");
    exit;
}

$pets = $conn->query("SELECT * FROM pets ORDER BY created_at DESC");
$pending_count = $conn->query("SELECT COUNT(*) AS c FROM adoption_requests WHERE status = 'pending'")->fetch_assoc()['c'];

require "../includes/header.php";
?>

<h2>Admin Dashboard</h2>

<div class="admin-links">
    <a href="add_pet.php" class="btn">+ Add New Pet</a>
    <a href="manage_requests.php" class="btn">Manage Adoption Requests (<?= (int)$pending_count ?> pending)</a>
</div>

<h3>All Pets</h3>
<table class="data-table">
    <tr>
        <th>Name</th>
        <th>Species</th>
        <th>Breed</th>
        <th>Status</th>
        <th>Actions</th>
    </tr>
    <?php while ($pet = $pets->fetch_assoc()): ?>
        <tr>
            <td><?= htmlspecialchars($pet['name']) ?></td>
            <td><?= htmlspecialchars($pet['species']) ?></td>
            <td><?= htmlspecialchars($pet['breed']) ?></td>
            <td><span class="status-<?= htmlspecialchars($pet['status']) ?>"><?= htmlspecialchars(ucfirst($pet['status'])) ?></span></td>
            <td>
                <a href="edit_pet.php?id=<?= (int)$pet['pet_id'] ?>">Edit</a> |
                <a href="dashboard.php?delete=<?= (int)$pet['pet_id'] ?>" onclick="return confirm('Delete this pet?')">Delete</a>
            </td>
        </tr>
    <?php endwhile; ?>
</table>

<?php require "../includes/footer.php"; ?>
