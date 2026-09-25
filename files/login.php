<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/dbconnect.php';

// Already logged in? Bounce straight to the right dashboard.
if (is_logged_in()) {
    header('Location: ' . (is_admin() ? '/admin/dashboard.php' : '/home.php'));
    exit;
}

$errors  = [];
$email   = '';
$redirect = $_GET['redirect'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        $errors[] = 'Email and password are required.';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare('SELECT id, name, password, role FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if (!$user || !password_verify($password, $user['password'])) {
            $errors[] = 'Invalid email or password.';
        } else {
            // Regenerate session ID on login to prevent fixation.
            session_regenerate_id(true);

            // Store role and user_id in the session for access control.
            $_SESSION['user_id'] = (int) $user['id'];
            $_SESSION['name']    = $user['name'];
            $_SESSION['role']    = $user['role'];

            // Role-based redirect.
            if ($user['role'] === 'admin') {
                header('Location: /admin/dashboard.php');
            } else {
                $target = $redirect !== '' ? urldecode($redirect) : '/home.php';
                header('Location: ' . $target);
            }
            exit;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login - PetAdoption</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
<div class="auth-container">
    <h2>Login</h2>

    <?php if (!empty($errors)): ?>
        <div class="alert alert-error">
            <ul>
                <?php foreach ($errors as $err): ?>
                    <li><?= htmlspecialchars($err) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form method="POST" action="login.php<?= $redirect !== '' ? '?redirect=' . htmlspecialchars($redirect) : '' ?>">
        <label for="email">Email</label>
        <input type="email" id="email" name="email" value="<?= htmlspecialchars($email) ?>" required>

        <label for="password">Password</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">Log In</button>
    </form>

    <p>Don't have an account? <a href="register.php">Register here</a>.</p>
</div>
</body>
</html>
