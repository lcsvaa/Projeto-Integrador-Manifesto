<?php
header('Content-Type: application/json');
require 'conexao.php';

$termo = $_GET['termo'] ?? '';
$termo = trim($termo);

try {
    // Consulta que traz produto, imagem principal e tamanhos (se houver)
    $sql = "SELECT 
                p.id, p.nomeItem, p.valorItem, p.estoqueItem, 
                c.ctgNome AS categoria,
                col.colecaoNome AS colecao,
                img.nomeImagem AS imagem,
                pt.tamanho,
                pt.estoque AS estoqueTamanho
            FROM tb_produto p
            JOIN tb_categoria c ON p.idCategoria = c.id
            JOIN tb_colecao col ON p.idColecao = col.id
            LEFT JOIN tb_imagemProduto img ON img.idProduto = p.id 
            LEFT JOIN tb_produto_tamanho pt ON pt.idProduto = p.id
            WHERE LOWER(p.nomeItem) LIKE LOWER(?)
            ORDER BY p.nomeItem";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(["%$termo%"]);
    $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $produtos = [];

    foreach ($rows as $row) {
        $id = $row['id'];

        // Se o produto ainda não estiver no array, inicializa
        if (!isset($produtos[$id])) {
            $produtos[$id] = [
                'id' => $id,
                'nomeItem' => $row['nomeItem'],
                'valorItem' => $row['valorItem'],
                'estoqueItem' => $row['estoqueItem'],
                'categoria' => $row['categoria'],
                'colecao' => $row['colecao'],
                'imagem' => $row['imagem'] ?? null,
                'estoquesTamanhos' => []
            ];
        }

        // Se tiver tamanho (não nulo), adiciona ao array de tamanhos
        if ($row['tamanho'] !== null) {
            $produtos[$id]['estoquesTamanhos'][] = [
                'tamanho' => $row['tamanho'],
                'estoque' => (int)$row['estoqueTamanho']
            ];
        }
    }

    // Reindexar array para ser numérico no JSON
    $produtos = array_values($produtos);

    echo json_encode(['status' => 'success', 'produtos' => $produtos]);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}