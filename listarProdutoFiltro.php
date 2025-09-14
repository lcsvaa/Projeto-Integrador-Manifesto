<?php
require_once 'conexao.php';

$condicoes = array();
$params = array();

if (!empty($_GET['idCategoria'])) {
    $condicoes[] = "p.idCategoria = :idCategoria";
    $params[':idCategoria'] = intval($_GET['idCategoria']);
}

$where = '';
if (!empty($condicoes)) {
    $where = "WHERE " . implode(' AND ', $condicoes);
}

$sql = "SELECT p.id, p.nomeItem, p.valorItem, p.estoqueItem, c.ctgNome as categoria, col.colecaoNome as colecao,
               img.nomeImagem as imagem
        FROM tb_produto p
        JOIN tb_categoria c ON p.idCategoria = c.id
        JOIN tb_colecao col ON p.idColecao = col.id
        LEFT JOIN tb_imagemProduto img ON img.idProduto = p.id AND img.statusImagem = 'principal'
        $where
        ORDER BY p.nomeItem";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode(array('status' => 'success', 'produtos' => $produtos));