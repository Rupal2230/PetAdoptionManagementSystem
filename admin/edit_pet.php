<?php
require "admin_check.php";
require "../dbconnect.php";
$error = "";
$success = "";

$pet_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $species = trim($_POST['species']);
    $breed = trim($_POST['breed']);
    $age = (int)$_POST['age'];
    $gender = $_POST['gender'];
    $description = trim($_POST['description']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE pets SET name=?, species=?, breed=?, age=?, gender=?, description=?, status=? WHERE pet_id=?");
    $stmt->bind_param("sssisssi", $name, $species, $breed, $age, $gender, $description, $status, $pet_id);
    if ($stmt->execute()) {
        $success = "Pet updated successfully!";
    } else {
        $error = "Something went wrong.";
    }
    $stmt->close();
}

$stmt = $conn->prepare("SELECT * FROM pets WHERE pet_id = ?");
$stmt->bind_param("i", $pet_id);
$stmt->execute();
$pet = $stmt->get_result()->fetch_assoc();

if (!$pet) {
    require "../includes/header.php";
    echo "<p>Pet not found.</p>";
    require "../includes/footer.php";
    exit;
}

require "../includes/header.php";
?>

<h2>Edit Pet</h2>
<?php if ($error): ?><p class="alert error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="alert success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

<form method="POST" class="form-card">
    <label>Name</label>
    <input type="text" name="name" value="<?= htmlspecialchars($pet['name']) ?>" required>

    <label>Species</label>
    <input type="text" name="species" value="<?= htmlspecialchars($pet['species']) ?>" required>

    <label>Breed</label>
    <input type="text" name="breed" value="<?= htmlspecialchars($pet['breed']) ?>">

    <label>Age</label>
    <input type="number" name="age" value="<?= (int)$pet['age'] ?>" min="0">

    <label>Gender</label>
    <select name="gender">
        <option value="Male" <?= $pet['gender'] === 'Male' ? 'selected' : '' ?>>Male</option>
        <option value="Female" <?= $pet['gender'] === 'Female' ? 'selected' : '' ?>>Female</option>
    </select>

    <label>Description</label>
    <textarea name="description" rows="4"><?= htmlspecialchars($pet['description']) ?></textarea>

    <label>Status</label>
    <select name="status">
        <option value="available" <?= $pet['status'] === 'available' ? 'selected' : '' ?>>Available</option>
        <option value="pending" <?= $pet['status'] === 'pending' ? 'selected' : '' ?>>Pending</option>
        <option value="adopted" <?= $pet['status'] === 'adopted' ? 'selected' : '' ?>>Adopted</option>
    </select>

    <button type="submit">Save Changes</button>
</form>
<p><a href="dashboard.php">&larr; Back to Dashboard</a></p>

<?php require "../includes/footer.php"; ?>
