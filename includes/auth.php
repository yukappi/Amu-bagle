<?php
// admin login helpers

function is_admin(): bool
{
    return !empty($_SESSION['admin_id']);
}

// call this at the top of every admin page
function require_admin(): void
{
    if (!is_admin()) {
        header('Location: ' . url('admin/login.php'));
        exit;
    }
}