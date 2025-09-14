<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../conexao.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido', 405);
    }

    $id = isset($_POST['idNovidade']) ? (int)$_POST['idNovidade'] : 0;
    if ($id <= 0) {
        throw new Exception('ID inválido', 422);
    }

    // Get the image filename
    $stmt = $pdo->prepare('SELECT imagemNovidade FROM tb_novidades WHERE idNovidade = :id');
    $stmt->execute(array(':id' => $id));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new Exception('Novidade não encontrada', 404);
    }

    // Delete from database
    $del = $pdo->prepare('DELETE FROM tb_novidades WHERE idNovidade = :id');
    $del->execute(array(':id' => $id));

    // Delete image file if exists
    $imagePath = dirname(__FILE__) . '/../uploads/' . $row['imagemNovidade'];
    if ($row['imagemNovidade'] && file_exists($imagePath)) {
        @unlink($imagePath);
    }

    echo json_encode(array('success' => true));
} catch (Exception $e) {
    $code = $e->getCode() ? $e->getCode() : 400;
    if (function_exists('http_response_code')) {
        http_response_code($code);
    } else {
        header("HTTP/1.1 $code");
    }
    echo json_encode(array('success' => false, 'message' => $e->getMessage()));
}