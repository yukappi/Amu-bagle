<?php
// Escape text before showing it in HTML (prevents XSS)
function h(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

// Build a URL inside this site, e.g. url('menu.php')
function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}

// Mark the link of the page we are on (for the nav)
function current_page(string $path): string
{
    return $_SERVER['SCRIPT_NAME'] === url($path) ? ' aria-current="page"' : '';
}