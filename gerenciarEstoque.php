<?php
require_once 'conexao.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('status' => 'error', 'message' => 'Método inválido'));
    exit;
}

$idProduto = isset($_POST['idProduto']) ? $_POST['idProduto'] : null;
$acao = isset($_POST['acao']) ? $_POST['acao'] : null;
$tamanho = isset($_POST['tamanho']) ? $_POST['tamanho'] : null;

if (!$idProduto || !$acao) {
    echo json_encode(array('status' => 'error', 'message' => 'Parâmetros insuficientes'));
    exit;
}

try {
    // Verifica se há tamanhos cadastrados para o produto
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_produto_tamanho WHERE idProduto = ?");
    $stmt->execute(array($idProduto));
    $temTamanhos = $stmt->fetchColumn() > 0;

    if ($temTamanhos) {
        // Produto COM tamanhos - é obrigatório informar o tamanho
        if (!$tamanho || !in_array($tamanho, array('P', 'M', 'G'))) {
            echo json_encode(array('status' => 'error', 'message' => 'Tamanho inválido ou ausente'));
            exit;
        }

        // Busca estoque atual do tamanho
        $stmt = $pdo->prepare("SELECT estoque FROM tb_produto_tamanho WHERE idProduto = ? AND tamanho = ?");
        $stmt->execute(array($idProduto, $tamanho));
        $dados = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$dados) {
            echo json_encode(array('status' => 'error', 'message' => 'Tamanho não encontrado para o produto'));
            exit;
        }

        $estoqueAtual = (int)$dados['estoque'];
        $novoEstoque = ($acao === 'incrementar') ? $estoqueAtual + 1 : max(0, $estoqueAtual - 1);

        // Atualiza o estoque do tamanho
        $stmt = $pdo->prepare("UPDATE tb_produto_tamanho SET estoque = ? WHERE idProduto = ? AND tamanho = ?");
        $stmt->execute(array($novoEstoque, $idProduto, $tamanho));

        // Recalcula o estoque total somando todos os tamanhos
        $stmt = $pdo->prepare("SELECT SUM(estoque) AS total FROM tb_produto_tamanho WHERE idProduto = ?");
        $stmt->execute(array($idProduto));
        $total = (int)$stmt->fetchColumn();

        // Atualiza o estoque geral na tb_produto
        $stmt = $pdo->prepare("UPDATE tb_produto SET estoqueItem = ? WHERE id = ?");
        $stmt->execute(array($total, $idProduto));

        echo json_encode(array(
            'status' => 'success',
            'message' => "Estoque do tamanho $tamanho atualizado",
            'novoEstoqueTamanho' => $novoEstoque,
            'novoEstoqueTotal' => $total
        ));
        exit;

    } else {
        // Produto SEM tamanhos
        $stmt = $pdo->prepare("SELECT estoqueItem FROM tb_produto WHERE id = ?");
        $stmt->execute(array($idProduto));
        $produto = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$produto) {
            echo json_encode(array('status' => 'error', 'message' => 'Produto não encontrado'));
            exit;
        }

        $estoqueAtual = (int)$produto['estoqueItem'];
        $novoEstoque = ($acao === 'incrementar') ? $estoqueAtual + 1 : max(0, $estoqueAtual - 1);

        $stmt = $pdo->prepare("UPDATE tb_produto SET estoqueItem = ? WHERE id = ?");
        $stmt->execute(array($novoEstoque, $idProduto));

        echo json_encode(array(
            'status' => 'success',
            'message' => 'Estoque do produto atualizado',
            'novoEstoque' => $novoEstoque
        ));
        exit;
    }

} catch (PDOException $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Erro no banco: ' . $e->getMessage()));
    exit;
}
