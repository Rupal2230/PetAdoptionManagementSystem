<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../dbconnect.php';

require_admin();

$errors  = [];
$success = false;

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrfToken = $_SESSION['csrf_token'];

// ---- Add a new pet ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'add_pet') {
    if (empty($_POST['csrf_token']) || !hash_equals($csrfToken, $_POST['csrf_token'])) {
        $errors[] = 'Invalid form submission, please try again.';
    } else {
        $name        = trim($_POST['name'] ?? '');
        $species     = trim($_POST['species'] ?? '');
        $breed       = trim($_POST['breed'] ?? '');
        $age         = $_POST['age'] !== '' ? (int) $_POST['age'] : null;
        $description = trim($_POST['description'] ?? '');
        $imagePath   = trim($_POST['image_path'] ?? ''); // e.g. images/buddy.jpg (upload handling can be added later)

        if ($name === '' || $species === '') {
            $errors[] = 'Name and species are required.';
        }

        if (empty($errors)) {
            $stmt = $pdo->prepare(
                'INSERT INTO pets (name, species, breed, age, description, image_path, status, added_by)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([
                $name, $species, $breed ?: null, $age, $description ?: null,
                $imagePath ?: null, 'available', $_SESSION['user_id'],
            ]);
            $success = true;
        }
    }
}

// ---- Delete a pet ----
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['form'] ?? '') === 'delete_pet') {
    if (!empty($_POST['csrf_token']) && hash_equals($csrfToken, $_POST['csrf_token'])) {
        $petId = (int) ($_POST['pet_id'] ?? 0);
        if ($petId > 0) {
            $stmt = $pdo->prepare('DELETE FROM pets WHERE id = ?');
            $stmt->execute([$petId]);
        }
    }
    header('Location: pets.php?deleted=1');
    exit;
}

$pets = $pdo->query('SELECT * FROM pets ORDER BY created_at DESC')->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Pets - PetAdoption</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
<?php include __DIR__ . '/../includes/nav.php'; ?>

<div class="page">
    <h2>Manage Pets</h2>

    <?php if ($success): ?>
        <div class="alert alert-success">Pet added successfully.</div>
    <?php endif; ?>
    <?php if (isset($_GET['deleted'])): ?>
        <div class="alert alert-success">Pet removed.</div>
    <?php endif; ?>
    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
        </div>
    <?php endif; ?>

    <h3>Add a New Pet</h3>
    <form method="POST" action="pets.php" class="pet-form">
        <input type="hidden" name="form" value="add_pet">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">

        <label for="name">Name</label>
        <input type="text" id="name" name="name" required>

        <label for="species">Species</label>
        <input type="text" id="species" name="species" placeholder="Dog, Cat, Bird..." required>

        <label for="breed">Breed</label>
        <input type="text" id="breed" name="breed">

        <label for="age">Age (years)</label>
        <input type="number" id="age" name="age" min="0">

        <label for="description">Description</label>
        <textarea id="description" name="description" rows="3"></textarea>

        <label for="image_path">Image path/URL (optional)</label>
        <input type="text" id="image_path" name="image_path" placeholder="images/buddy.jpg">

        <button type="submit">Add Pet</button>
    </form>

    <h3>All Pets</h3>
    <?php if (empty($pets)): ?>
        <p>No pets added yet.</p>
    <?php else: ?>
        <table class="requests-table">
            <thead>
                <tr>
                    <th>Name</th><th>Species</th><th>Breed</th><th>Age</th><th>Status</th><th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pets as $p): ?>
                    <tr>
                        <td><?= htmlspecialchars($p['name']) ?></td>
                        <td><?= htmlspecialchars($p['species']) ?></td>
                        <td><?= htmlspecialchars($p['breed'] ?: '&mdash;') ?></td>
                        <td><?= $p['age'] !== null ? (int) $p['age'] : '&mdash;' ?></td>
                        <td><span class="status-badge status-<?= htmlspecialchars($p['status']) ?>"><?= htmlspecialchars(ucfirst($p['status'])) ?></span></td>
                        <td>
                            <form method="POST" action="pets.php" class="inline-form" onsubmit="return confirm('Delete this pet?');">
                                <input type="hidden" name="form" value="delete_pet">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
                                <input type="hidden" name="pet_id" value="<?= (int) $p['id'] ?>">
                                <button type="submit" class="btn btn-reject">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>
</body>
</html>
