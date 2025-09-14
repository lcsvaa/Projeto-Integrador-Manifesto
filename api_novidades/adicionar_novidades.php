<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../conexao.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new RuntimeException('Método não permitido', 405);
    }

    $titulo   = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $data     = isset($_POST['data']) ? trim($_POST['data']) : '';
    $conteudo = isset($_POST['conteudo']) ? trim($_POST['conteudo']) : '';
    $file     = isset($_FILES['imagem']) ? $_FILES['imagem'] : null;

    if ($titulo === '' || $data === '' || $conteudo === '' || !$file) {
        throw new RuntimeException('Campos obrigatórios ausentes', 422);
    }

    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        throw new RuntimeException('Upload inválido', 400);
    }

    $mimeTypes = array('image/jpeg', 'image/png', 'image/gif');
    $fileType = mime_content_type($file['tmp_name']);
    if (!in_array($fileType, $mimeTypes, true)) {
        throw new RuntimeException('Tipo de imagem não permitido', 415);
    }

    $destDir = dirname(__FILE__) . '/../uploads/novidades/';
    if (!is_dir($destDir)) {
        if (!mkdir($destDir, 0755, true) && !is_dir($destDir)) {
            throw new RuntimeException('Falha ao criar a pasta de imagens', 500);
        }
    }

    $fileInfo = pathinfo($file['name']);
    $ext = isset($fileInfo['extension']) ? $fileInfo['extension'] : '';
    $nome = uniqid('nov_', true) . '.' . $ext;
    $dest = $destDir . $nome;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        throw new RuntimeException('Falha ao mover a imagem', 500);
    }

    $sql = 'INSERT INTO tb_novidades (titulo, dataNovidade, imagemNovidade, conteudo)
            VALUES (:titulo, :data, :imagem, :conteudo)';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':titulo'   => $titulo,
        ':data'     => $data,
        ':imagem'   => $nome,
        ':conteudo' => $conteudo
    ));

    echo json_encode(array(
        'success' => true,
        'id'      => $pdo->lastInsertId()
    ));
} catch (Exception $e) {
    $code = $e->getCode() ? (int)$e->getCode() : 400;
    http_response_code($code);
    echo json_encode(array(
        'success' => false,
        'message' => $e->getMessage()
    ));
}