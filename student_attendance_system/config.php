<?php
// Database connection. Update credentials if your MySQL settings differ.
session_start();
$host = 'db.fr-roub1.bengt.wasmernet.com';
$port = 20184;
$db   = 'user_7ed3fb3f';
$user = 'user_7ed3fb3f';
$pass = 'pw_d3nBUGubFJ05BmuhUe977z9ArR9e6Iy6';
$charset = 'utf8mb4';
$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES => false,
];
try {
    $pdo = new PDO($dsn, $user, $pass, $options, $port);
} catch (PDOException $e) {
    http_response_code(500);
    exit('Database connection failed. Import database.sql and check config.php.');
}
function h($value) { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function require_login() {
    if (empty($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }
}
function csrf_token() {
    if (empty($_SESSION['csrf_token'])) $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    return $_SESSION['csrf_token'];
}
function verify_csrf() {
    if (!hash_equals($_SESSION['csrf_token'] ?? '', $_POST['csrf_token'] ?? '')) {
        http_response_code(403); exit('Invalid request token. Go back and try again.');
    }
}
