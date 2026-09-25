<?php
require "admin_check.php";
require "../dbconnect.php";
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $species = trim($_POST['species']);
    $breed = trim($_POST['breed']);
    $age = (int)$_POST['age'];
    $gender = $_POST['gender'];
    $description = trim($_POST['description']);
    $added_by = $_SESSION['user_id'];

    if ($name === "" || $species === "") {
        $error = "Name and species are required.";
    } else {
        $stmt = $conn->prepare("INSERT INTO pets (name, species, breed, age, gender, description, added_by) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssissi", $name, $species, $breed, $age, $gender, $description, $added_by);
        if ($stmt->execute()) {
            $success = "Pet added successfully!";
        } else {
            $error = "Something went wrong.";
        }
        $stmt->close();
    }
}

require "../includes/header.php";
?>

<h2>Add New Pet</h2>
<?php if ($error): ?><p class="alert error"><?= htmlspecialchars($error) ?></p><?php endif; ?>
<?php if ($success): ?><p class="alert success"><?= htmlspecialchars($success) ?></p><?php endif; ?>

<form method="POST" class="form-card">
    <label>Name *</label>
    <input type="text" name="name" required>

    <label>Species * (Dog, Cat, Bird...)</label>
    <input type="text" name="species" required>

    <label>Breed</label>
    <input type="text" name="breed">

    <label>Age (years)</label>
    <input type="number" name="age" min="0">

    <label>Gender</label>
    <select name="gender">
        <option value="Male">Male</option>
        <option value="Female">Female</option>
    </select>

    <label>Description</label>
    <textarea name="description" rows="4"></textarea>

    <button type="submit">Add Pet</button>
</form>
<p><a href="dashboard.php">&larr; Back to Dashboard</a></p>

<?php require "../includes/footer.php"; ?>
