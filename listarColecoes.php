<?php
header('Content-Type: application/json');
require 'conexao.php';

try {
    $sql = "SELECT id, colecaoNome FROM tb_colecao ORDER BY colecaoNome";
    $stmt = $pdo->query($sql);
    $colecoes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode(array('status' => 'success', 'colecoes' => $colecoes));
} catch (Exception $e) {
    echo json_encode(array('status' => 'error', 'message' => $e->getMessage()));
}