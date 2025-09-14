<?php
require 'conexao.php';

try {
    $stmt = $pdo->query("SELECT * FROM tb_cupom");
    $cupons = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cupons);
} catch (Exception $e) {
    echo json_encode(array());
}