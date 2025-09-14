<?php
require_once 'conexao.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('status' => 'error', 'message' => 'Requisição inválida.'));
    exit;
}

$id = isset($_POST['id']) ? $_POST['id'] : null;

if (!$id) {
    echo json_encode(array('status' => 'error', 'message' => 'ID do produto não foi enviado.'));
    exit;
}

try {
    $pdo->beginTransaction();

    // Buscar imagens para apagar arquivos fisicos
    $stmtImagens = $pdo->prepare("SELECT nomeImagem FROM tb_imagemProduto WHERE idProduto = :id");
    $stmtImagens->execute(array(':id' => $id));
    $imagens = $stmtImagens->fetchAll(PDO::FETCH_ASSOC);

    // Apagar registros de tamanhos vinculados
    $stmtDeleteTamanhos = $pdo->prepare("DELETE FROM tb_produto_tamanho WHERE idProduto = :id");
    $stmtDeleteTamanhos->execute(array(':id' => $id));

    // Apagar registros de imagens vinculadas
    $stmtDeleteImagens = $pdo->prepare("DELETE FROM tb_imagemProduto WHERE idProduto = :id");
    $stmtDeleteImagens->execute(array(':id' => $id));

    // Apagar produto
    $stmtDeleteProduto = $pdo->prepare("DELETE FROM tb_produto WHERE id = :id");
    $stmtDeleteProduto->execute(array(':id' => $id));

    $pdo->commit();

    // Apagar arquivos fisicos após commit
    foreach ($imagens as $img) {
        $path = __DIR__ . '/uploads/produtos/' . $img['nomeImagem'];
        if (file_exists($path)) {
            unlink($path);
        }
    }

    echo json_encode(array('status' => 'success', 'message' => 'Produto removido com sucesso.'));
} catch (PDOException $e) {
    $pdo->rollBack();
    echo json_encode(array('status' => 'error', 'message' => 'Erro no banco: ' . $e->getMessage()));
}