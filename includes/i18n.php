<?php
// Which language to show: 'ja' or 'en' (saved in a cookie by lang.php)
function current_lang(): string
{
    return ($_COOKIE['lang'] ?? '') === 'en' ? 'en' : 'ja';
}

// Get a screen text, e.g. t('nav.menu')
// English texts that are missing fall back to Japanese
function t(string $key): string
{
    static $texts = null;
    if ($texts === null) {
        $texts = require __DIR__ . '/../lang/ja.php';
        if (current_lang() === 'en') {
            $texts = array_merge($texts, require __DIR__ . '/../lang/en.php');
        }
    }
    return $texts[$key] ?? $key;
}

// Pick the English or Japanese column from a DB row, e.g. localized($p, 'name')
// If the English one is empty, show the Japanese one
function localized(array $row, string $field): string
{
    $en = $row[$field . '_en'] ?? '';
    if (current_lang() === 'en' && $en !== '') {
        return $en;
    }
    return $row[$field . '_ja'] ?? '';
}


// Date for the screen: 11月20日 / Fri, Nov 20
function format_date(string $date): string
{
    $ts = strtotime($date);
    return current_lang() === 'en' ? date('D, M j', $ts) : date('n月j日', $ts);
}

// Price for the screen: 320 円 / ¥320
function price(int $yen): string
{
    return current_lang() === 'en' ? '¥' . number_format($yen) : number_format($yen) . ' 円';
}
