<?php
// inc/db.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host    = 'localhost';          // your DB host
$db      = 'recipe_web';         // your DB name
$user    = 'root';               // your DB user
$pass    = '';                   // your DB password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (PDOException $e) {
    die('Database connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES));
}
