<?php
// CSRF protection: a secret "password" that only our own forms know

// Make the token once per session, then reuse it
function csrf_token(): string
{
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Hidden field to put inside each POST form
function csrf_field(): string
{
    return '<input type="hidden" name="csrf_token" value="' . h(csrf_token()) . '">';
}

// Check the token when a form is sent. Stop if it does not match
function csrf_check(): void
{
    $sent = $_POST['csrf_token'] ?? '';
    if (!hash_equals(csrf_token(), $sent)) {
        http_response_code(400);
        exit('Invalid request. Please go back and try again.');
    }
}