<?php
header('Content-Type: application/json');
require 'conexao.php';

$id = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($id <= 0) {
    echo json_encode(array('status' => 'error', 'message' => 'ID inválido'));
    exit;
}

try {
    // Consulta principal do produto
    $sql = "SELECT 
                p.id, p.nomeItem, p.descItem, p.valorItem, p.estoqueItem, 
                p.idCategoria, p.idColecao,
                c.ctgNome AS categoria, 
                col.colecaoNome AS colecao,
                img.nomeImagem AS imagem
            FROM tb_produto p
            JOIN tb_categoria c ON p.idCategoria = c.id
            JOIN tb_colecao col ON p.idColecao = col.id
            LEFT JOIN tb_imagemProduto img 
                ON img.idProduto = p.id AND img.statusImagem = 'principal'
            WHERE p.id = :id
            LIMIT 1";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(':id' => $id));
    $produto = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$produto) {
        echo json_encode(array('status' => 'error', 'message' => 'Produto não encontrado'));
        exit;
    }

    // Consulta os estoques por tamanho (se houver)
    $stmtTamanhos = $pdo->prepare("SELECT tamanho, estoque FROM tb_produto_tamanho WHERE idProduto = ?");
    $stmtTamanhos->execute(array($produto['id']));
    
    // Processa os tamanhos manualmente (substituindo FETCH_KEY_PAIR)
    $tamanhos = array();
    while ($row = $stmtTamanhos->fetch(PDO::FETCH_ASSOC)) {
        $tamanhos[$row['tamanho']] = $row['estoque'];
    }

    // Preenche os estoques individuais (ou 0 se não existir)
    $produto['estoqueP'] = isset($tamanhos['P']) ? $tamanhos['P'] : 0;
    $produto['estoqueM'] = isset($tamanhos['M']) ? $tamanhos['M'] : 0;
    $produto['estoqueG'] = isset($tamanhos['G']) ? $tamanhos['G'] : 0;

    // Define se o produto é de tamanho único
    $produto['tamanhoUnico'] = empty($tamanhos);

    // Retorna o JSON
    echo json_encode(array('status' => 'success', 'produto' => $produto));

} catch (Exception $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Erro ao buscar produto: ' . $e->getMessage()));
}