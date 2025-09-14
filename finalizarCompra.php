<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('status' => 'erro', 'msg' => 'Requisição inválida'));
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('status' => 'erro', 'msg' => 'Usuário não logado'));
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

$idUsuario = $_SESSION['user_id'];
$carrinho = isset($_SESSION['carrinho']) ? $_SESSION['carrinho'] : array();

if (empty($carrinho)) {
    echo json_encode(array('status' => 'erro', 'msg' => 'Carrinho vazio'));
    exit;
}

$formaPagamento = isset($input['formaPagamento']) ? $input['formaPagamento'] : null;
if (!$formaPagamento) {
    echo json_encode(array('status' => 'erro', 'msg' => 'Forma de pagamento não informada'));
    exit;
}

$frete = (isset($input['shipping']) && $input['shipping'] === 'express') ? 29.90 : 15.90;

try {
    $pdo->beginTransaction();

    // Calcular total
    $totalProdutos = 0;
    foreach ($carrinho as $item) {
        $totalProdutos += $item['preco'] * $item['qtd'];
    }
    $totalCompra = $totalProdutos + $frete;

    // Inserir compra
    $stmt = $pdo->prepare("INSERT INTO tb_compra (idUsuario, valorTotal) VALUES (?, ?)");
    $stmt->execute(array($idUsuario, $totalCompra));
    $idCompra = $pdo->lastInsertId();

    // Preparar statements
    $stmtItem = $pdo->prepare("INSERT INTO tb_itemCompra (idCompra, idProduto, quantidade, valorUnitario, tamanho) VALUES (?, ?, ?, ?, ?)");
    $stmtEstoqueTamanho = $pdo->prepare("UPDATE tb_produto_tamanho SET estoque = estoque - ? WHERE idProduto = ? AND tamanho = ?");
    $stmtEstoqueSimples = $pdo->prepare("UPDATE tb_produto SET estoqueItem = estoqueItem - ? WHERE id = ?");
    $stmtCheckTamanho = $pdo->prepare("SELECT estoque FROM tb_produto_tamanho WHERE idProduto = ? AND tamanho = ?");
    $stmtCheckSimples = $pdo->prepare("SELECT estoqueItem FROM tb_produto WHERE id = ?");

    // Verificar estoque antes de inserir
    foreach ($carrinho as $item) {
        $idProduto = $item['id'];
        $quantidade = $item['qtd'];
        $tamanho = isset($item['tamanho']) ? $item['tamanho'] : null;

        if ($tamanho && $tamanho !== 'Único') {
            // Produto com tamanho
            $stmtCheckTamanho->execute(array($idProduto, $tamanho));
            $estoque = $stmtCheckTamanho->fetchColumn();

            if ($estoque === false) {
                throw new Exception("Produto com tamanho '$tamanho' não encontrado (ID $idProduto).");
            }
            if ($estoque < $quantidade) {
                throw new Exception("Estoque insuficiente para o produto ID $idProduto, tamanho $tamanho. Disponível: $estoque, solicitado: $quantidade.");
            }
        } else {
            // Produto sem tamanho
            $stmtCheckSimples->execute(array($idProduto));
            $estoque = $stmtCheckSimples->fetchColumn();

            if ($estoque === false) {
                throw new Exception("Produto sem tamanho não encontrado (ID $idProduto).");
            }
            if ($estoque < $quantidade) {
                throw new Exception("Estoque insuficiente para o produto ID $idProduto. Disponível: $estoque, solicitado: $quantidade.");
            }
        }
    }

    // Inserir itens e atualizar estoque
    foreach ($carrinho as $item) {
        $idProduto = $item['id'];
        $quantidade = $item['qtd'];
        $valorUnitario = $item['preco'];
        $tamanho = isset($item['tamanho']) ? $item['tamanho'] : 'Único';

        // Gravar item
        $stmtItem->execute(array($idCompra, $idProduto, $quantidade, $valorUnitario, $tamanho));

        // Atualizar estoque
        if ($tamanho && $tamanho !== 'Único') {
            $stmtEstoqueTamanho->execute(array($quantidade, $idProduto, $tamanho));
        } else {
            $stmtEstoqueSimples->execute(array($quantidade, $idProduto));
        }
    }

    // Inserir pagamento
    $stmtPagamento = $pdo->prepare("INSERT INTO tb_pagamento (statusPagamento, tipoPagamento, compraid) VALUES (?, ?, ?)");
    $stmtPagamento->execute(array('Processando', $formaPagamento, $idCompra));

    $pdo->commit();
    unset($_SESSION['carrinho']);

    echo json_encode(array('status' => 'ok', 'msg' => 'Pedido realizado com sucesso!'));

} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(array(
        'status' => 'erro',
        'msg' => 'Erro ao finalizar o pedido.',
        'error' => $e->getMessage()
    ));
}