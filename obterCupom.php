<?php
header('Content-Type: application/json');

require_once 'conexao.php'; // ajuste o caminho conforme sua estrutura

if (!isset($_GET['idCupom']) || empty($_GET['idCupom'])) {
    echo json_encode(array('error' => 'ID do cupom não informado'));
    exit;
}

$idCupom = intval($_GET['idCupom']);

try {
    $sql = "SELECT idCupom, codigo, porcentagemDesconto, tipoCupom, dataValidade, quantidadeUso, valorCompraMin, descricaoCupom, statusCupom 
            FROM tb_cupom WHERE idCupom = :idCupom LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->bindValue(':idCupom', $idCupom, PDO::PARAM_INT);
    $stmt->execute();

    $cupom = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($cupom) {
        echo json_encode($cupom);
    } else {
        echo json_encode(array('error' => 'Cupom não encontrado'));
    }
} catch (PDOException $e) {
    echo json_encode(array('error' => 'Erro no banco de dados: ' . $e->getMessage()));
}