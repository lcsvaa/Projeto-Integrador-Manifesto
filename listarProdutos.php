<?php
require_once 'conexao.php';
header('Content-Type: application/json');

try {
    $stmt = $pdo->query("
        SELECT 
            p.id, p.nomeItem, p.valorItem, p.estoqueItem,
            c.ctgNome AS categoria,
            co.colecaoNome AS colecao,
            img.nomeImagem AS imagem
        FROM tb_produto p
        JOIN tb_categoria c ON p.idCategoria = c.id
        JOIN tb_colecao co ON p.idColecao = co.id
        LEFT JOIN tb_imagemProduto img ON img.idProduto = p.id AND img.statusImagem = 'principal'
        ORDER BY p.nomeItem
    ");

    $produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

    foreach ($produtos as &$produto) {
        $stmtTamanhos = $pdo->prepare("SELECT tamanho, estoque FROM tb_produto_tamanho WHERE idProduto = ?");
        $stmtTamanhos->execute(array($produto['id']));
        $estoquesTamanhos = $stmtTamanhos->fetchAll(PDO::FETCH_ASSOC);

        $produto['estoquesTamanhos'] = $estoquesTamanhos;
    }

    echo json_encode(array('status' => 'success', 'produtos' => $produtos));

} catch (PDOException $e) {
    echo json_encode(array('status' => 'error', 'message' => 'Erro: ' . $e->getMessage()));
}