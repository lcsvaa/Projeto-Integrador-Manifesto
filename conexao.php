<?php
$host = 'localhost';
$db   = 'senactito_db_manifesto';
$user = 'senactito_manifesto'; // altere se seu user for diferente
$pass = '@turma10manifesto';     // altere se tiver senha
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";

$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
    die('Erro ao conectar: ' . $e->getMessage());
}
