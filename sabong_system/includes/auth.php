<?php
// includes/auth.php
if (session_status() === PHP_SESSION_NONE) session_start();
function requireAdmin() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: ../admin/login.php'); exit;
    }
}
function isAdmin() { return !empty($_SESSION['admin_id']); }
