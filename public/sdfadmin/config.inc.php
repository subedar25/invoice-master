<?php

declare(strict_types=1);

/**
 * phpMyAdmin runtime config for this project.
 * Reads DB credentials from project .env so local/prod can use their own values.
 */

$cfg['blowfish_secret'] = 'invoice-master-change-this-32-char-secret-key';
$cfg['PmaAbsoluteUri'] = '';

$envFile = dirname(__DIR__, 2) . '/.env';
$env = [];

if (is_file($envFile) && is_readable($envFile)) {
    $parsed = parse_ini_file($envFile, false, INI_SCANNER_RAW);
    if (is_array($parsed)) {
        $env = $parsed;
    }
}

$dbHost = $env['DB_HOST'] ?? '127.0.0.1';
$dbPort = $env['DB_PORT'] ?? '3306';
$dbName = $env['DB_DATABASE'] ?? '';
$dbUser = $env['DB_USERNAME'] ?? '';
$dbPass = $env['DB_PASSWORD'] ?? '';

$i = 0;
$i++;
$cfg['Servers'][$i]['auth_type'] = 'cookie';
$cfg['Servers'][$i]['host'] = trim((string) $dbHost, "\"'");
$cfg['Servers'][$i]['port'] = (string) trim((string) $dbPort, "\"'");
$cfg['Servers'][$i]['compress'] = false;
$cfg['Servers'][$i]['AllowNoPassword'] = false;

/**
 * Optional prefill; actual login still uses cookie auth screen.
 */
$cfg['Servers'][$i]['user'] = trim((string) $dbUser, "\"'");
$cfg['Servers'][$i]['password'] = trim((string) $dbPass, "\"'");

$cfg['UploadDir'] = '';
$cfg['SaveDir'] = '';
