<?php
/**
 * includes/auth.php
 * Session bootstrap + role-based access control helpers.
 * Include this near the top of ANY page that needs to know
 * who is logged in, or that needs to restrict access:
 *
 *   require_once __DIR__ . '/includes/auth.php';
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/** True if a user is currently logged in. */
function is_logged_in(): bool
{
    return isset($_SESSION['user_id'], $_SESSION['role']);
}

/** True if the logged-in user's role is 'admin'. */
function is_admin(): bool
{
    return is_logged_in() && $_SESSION['role'] === 'admin';
}

/** True if the logged-in user's role is 'customer'. */
function is_customer(): bool
{
    return is_logged_in() && $_SESSION['role'] === 'customer';
}

/**
 * Block the page unless the visitor is logged in.
 * Sends them to login.php (with a return path) otherwise.
 */
function require_login(): void
{
    if (!is_logged_in()) {
        $return = urlencode($_SERVER['REQUEST_URI'] ?? '');
        header("Location: /login.php?redirect={$return}");
        exit;
    }
}

/**
 * Block the page unless the visitor is a logged-in admin.
 * Non-admins (including guests) are redirected away.
 * Adjust the relative paths below ($home / $login) if you move
 * this file or call it from a different folder depth.
 */
function require_admin(): void
{
    require_login();
    if (!is_admin()) {
        // A logged-in customer trying to hit admin pages -> send home.
        header('Location: /home.php?error=forbidden');
        exit;
    }
}

/**
 * Block the page unless the visitor is a logged-in customer.
 * (Useful if you ever want to keep admins out of customer-only
 * actions like submitting adoption requests.)
 */
function require_customer(): void
{
    require_login();
    if (!is_customer()) {
        header('Location: /admin/dashboard.php');
        exit;
    }
}
