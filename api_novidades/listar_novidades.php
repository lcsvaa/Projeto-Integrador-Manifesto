<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../conexao.php';

$result = $pdo->query('SELECT * FROM tb_novidades ORDER BY dataNovidade DESC');
$data = $result->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($data);