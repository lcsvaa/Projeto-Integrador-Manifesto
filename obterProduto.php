<?php
header('Content-Type: application/json');
require_once 'conexao.php';

if (!isset($_GET['idProduto']) || empty($_GET['idProduto'])) {
    echo json_encode(array('error' => 'ID do produto não informado'));
    exit;
}

$idProduto = intval($_GET['idProduto']);

try {
    $sql = "SELECT * FROM tb_produto WHERE idProduto = :idProduto LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':idProduto', $idProduto, PDO::PARAM_INT);
    $stmt->execute();

    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($produto) {
        echo json_encode($produto);
    } else {
        echo json_encode(array('error' => 'Produto não encontrado'));
    }
} catch (PDOException $e) {
    echo json_encode(array('error' => 'Erro no banco de dados: ' . $e->getMessage()));
}