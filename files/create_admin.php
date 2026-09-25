<?php
/**
 * create_admin.php
 * One-time setup script to seed the FIRST admin account.
 *
 * Safety measures:
 *   1. Preferred usage is the command line (php create_admin.php),
 *      which is not reachable by outside visitors at all.
 *   2. If it IS run over the web, it requires a secret setup key
 *      (SETUP_KEY below) passed as ?key=... and it refuses to run
 *      if an admin account already exists.
 *   3. Delete this file (or at least change SETUP_KEY) once you've
 *      created your admin account.
 *
 * CLI usage:
 *   php create_admin.php "Admin Name" "admin@example.com" "StrongPassword123"
 *
 * Web usage (only if CLI isn't available to you):
 *   https://yoursite.com/create_admin.php?key=CHANGE_ME_SECRET&name=Admin&email=admin@example.com&password=StrongPassword123
 */

require_once __DIR__ . '/dbconnect.php';

// CHANGE THIS before deploying, and remove/rename the file after use.
const SETUP_KEY = 'CHANGE_ME_SECRET';

function create_admin_account(PDO $pdo, string $name, string $email, string $password): string
{
    if ($name === '' || $email === '' || $password === '') {
        return 'ERROR: name, email, and password are all required.';
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return 'ERROR: invalid email address.';
    }
    if (strlen($password) < 8) {
        return 'ERROR: password should be at least 8 characters.';
    }

    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return "ERROR: a user with email {$email} already exists.";
    }

    $hashed = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare(
        'INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)'
    );
    $stmt->execute([$name, $email, $hashed, 'admin']);

    return "SUCCESS: admin account created for {$email} (id " . $pdo->lastInsertId() . ").";
}

function admin_already_exists(PDO $pdo): bool
{
    $stmt = $pdo->query("SELECT COUNT(*) AS c FROM users WHERE role = 'admin'");
    return (int) $stmt->fetch()['c'] > 0;
}

if (PHP_SAPI === 'cli') {
    // ---- Command-line usage ----
    $name     = $argv[1] ?? null;
    $email    = $argv[2] ?? null;
    $password = $argv[3] ?? null;

    if (!$name || !$email || !$password) {
        fwrite(STDERR, "Usage: php create_admin.php \"Admin Name\" \"admin@example.com\" \"StrongPassword123\"\n");
        exit(1);
    }

    echo create_admin_account($pdo, $name, $email, $password) . PHP_EOL;
    exit(0);
}

// ---- Web usage (fallback, guarded) ----
header('Content-Type: text/plain');

$key = $_GET['key'] ?? '';
if (!hash_equals(SETUP_KEY, $key)) {
    http_response_code(403);
    echo "Forbidden.";
    exit;
}

if (admin_already_exists($pdo)) {
    http_response_code(403);
    echo "An admin account already exists. Refusing to run again for safety.\n";
    echo "If you really need another admin, create it directly via SQL or temporarily edit this script.";
    exit;
}

$name     = trim($_GET['name'] ?? '');
$email    = trim($_GET['email'] ?? '');
$password = $_GET['password'] ?? '';

echo create_admin_account($pdo, $name, $email, $password);
