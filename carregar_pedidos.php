<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['error' => 'Não autorizado']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    
    // Consulta para obter os pedidos (compras) do usuário
    $stmt = $pdo->prepare("
        SELECT c.id AS idPedido, 
               c.dataCompra AS dataPedido, 
               c.valorTotal, 
               c.statusCompra AS status
        FROM tb_compra c 
        WHERE c.idUsuario = ? 
        ORDER BY c.dataCompra DESC
    ");
    $stmt->execute([$user_id]);
    $pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Para cada pedido, buscar os produtos associados
    foreach ($pedidos as &$pedido) {
        $stmt = $pdo->prepare("
            SELECT ic.idProduto, 
                   pr.nomeItem AS nome, 
                   ic.valorUnitario AS preco, 
                   ic.quantidade, 
                   (SELECT linkImagem FROM tb_imagemProduto WHERE idProduto = pr.id AND statusImagem = 'principal' LIMIT 1) AS imagem,
                   ic.tamanho
            FROM tb_itemCompra ic
            JOIN tb_produto pr ON ic.idProduto = pr.id
            WHERE ic.idCompra = ?
        ");
        $stmt->execute([$pedido['idPedido']]);
        $pedido['produtos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    echo json_encode($pedidos);
} catch (PDOException $e) {
    echo json_encode(['error' => 'Erro ao carregar pedidos: ' . $e->getMessage()]);
}
?>