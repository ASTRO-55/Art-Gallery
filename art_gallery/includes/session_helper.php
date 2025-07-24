<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Redirects logged-in users based on their role to the specified page.
 *
 * @param string $redirect_page The page to redirect to.
 * @param string $role Role to check ('artist', 'user', 'admin').
 */
function redirect_if_logged_in($redirect_page, $role) {
    switch ($role) {
        case 'artist':
            if (isset($_SESSION['artist_id'])) {
                header("Location: $redirect_page");
                exit();
            }
            break;
        case 'user':
            if (isset($_SESSION['user_id'])) {
                header("Location: $redirect_page");
                exit();
            }
            break;
        case 'admin':
            if (isset($_SESSION['admin_id'])) {
                header("Location: $redirect_page");
                exit();
            }
            break;
    }
}

/**
 * Protects pages by verifying user is logged in for a specific role.
 * If not logged in, redirects to the provided login page.
 *
 * @param string $login_page The login page URL to redirect to if not logged in.
 * @param string $role Role to check ('artist', 'user', 'admin').
 */
function protect_page($login_page, $role) {
    switch ($role) {
        case 'artist':
            if (!isset($_SESSION['artist_id'])) {
                header("Location: $login_page");
                exit();
            }
            break;
        case 'user':
            if (!isset($_SESSION['user_id'])) {
                header("Location: $login_page");
                exit();
            }
            break;
        case 'admin':
            if (!isset($_SESSION['admin_id'])) {
                header("Location: $login_page");
                exit();
            }
            break;
    }
}
