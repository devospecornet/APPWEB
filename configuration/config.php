<?php

demarrerSession();

define('APP_NAME', 'GSB Future');
define('APP_BASE_URL', '');
define('SESSION_TIMEOUT', 15 * 60);
define('STOCKAGE_PATH', __DIR__ . '/../stockage/justificatifs');

/*
|--------------------------------------------------------------------------
| Configuration base de données
|--------------------------------------------------------------------------
*/
define('DB_HOST', getenv('GSB_DB_HOST') ?: 'ecornezbrisingr.mysql.db');
define('DB_NAME', getenv('GSB_DB_NAME') ?: 'ecornezbrisingr');
define('DB_PORT', getenv('GSB_DB_PORT') ?: '3306');
define('DB_USER', getenv('GSB_DB_USER') ?: 'ecornezbrisingr');
define('DB_PASS', getenv('GSB_DB_PASS') ?: '');

/*
|--------------------------------------------------------------------------
| Session
|--------------------------------------------------------------------------
*/
function demarrerSession(): void
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_name('gsb_future_session');
        session_start();
    }
}


function determinerBaseUrlApplication(): string
{
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $base = str_replace('\\', '/', dirname($scriptName));

    if ($base === '/' || $base === '\\' || $base === '.') {
        return '';
    }

    return rtrim($base, '/');
}

if (!function_exists('asset_url')) {
    function asset_url(string $chemin = ''): string
    {
        $chemin = ltrim($chemin, '/');
        return APP_BASE_URL . ($chemin !== '' ? '/' . $chemin : '');
    }
}

require_once __DIR__ . '/../includes/helpers.php';
