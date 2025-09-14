<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../conexao.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido', 405);
    }

    $id       = isset($_POST['idNovidade']) ? (int)$_POST['idNovidade'] : 0;
    $titulo   = isset($_POST['titulo']) ? trim($_POST['titulo']) : '';
    $data     = isset($_POST['data']) ? trim($_POST['data']) : '';
    $conteudo = isset($_POST['conteudo']) ? trim($_POST['conteudo']) : '';
    $file     = isset($_FILES['imagem']) ? $_FILES['imagem'] : null;

    if ($id <= 0) {
        throw new Exception('ID inválido', 422);
    }
    if ($titulo === '' || $data === '' || $conteudo === '') {
        throw new Exception('Campos obrigatórios ausentes', 422);
    }

    $stmt = $pdo->prepare('SELECT imagemNovidade FROM tb_novidades WHERE idNovidade = :id');
    $stmt->execute(array(':id' => $id));
    $row = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$row) {
        throw new Exception('Novidade não encontrada', 404);
    }
    $imagemAtual = $row['imagemNovidade'];

    $novoNome = $imagemAtual;
    if ($file && isset($file['error']) && $file['error'] !== UPLOAD_ERR_NO_FILE) {
        if (!is_uploaded_file($file['tmp_name'])) {
            throw new Exception('Upload inválido', 400);
        }
        
        // PHP 5.6 compatible mime type check
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        
        $allowedTypes = array('image/jpeg', 'image/png', 'image/gif');
        if (!in_array($mime, $allowedTypes, true)) {
            throw new Exception('Tipo de imagem não permitido', 415);
        }
        
        $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
        $novoNome = uniqid('nov_', true) . '.' . $ext;
        $dest = dirname(__FILE__) . '/../uploads/' . $novoNome;

        if (!move_uploaded_file($file['tmp_name'], $dest)) {
            throw new Exception('Falha ao mover a imagem', 500);
        }
        
        if ($imagemAtual && file_exists(dirname(__FILE__) . '/../uploads/' . $imagemAtual)) {
            @unlink(dirname(__FILE__) . '/../uploads/' . $imagemAtual);
        }
    }

    $sql = 'UPDATE tb_novidades 
               SET titulo = :titulo,
                   dataNovidade = :data,
                   imagemNovidade = :imagem,
                   conteudo = :conteudo
             WHERE idNovidade = :id';
    $stmt = $pdo->prepare($sql);
    $stmt->execute(array(
        ':titulo'  => $titulo,
        ':data'    => $data,
        ':imagem'  => $novoNome,
        ':conteudo'=> $conteudo,
        ':id'      => $id
    ));

    echo json_encode(array('success' => true));
} catch (Exception $e) {
    http_response_code($e->getCode() ? $e->getCode() : 400);
    echo json_encode(array('success' => false, 'message' => $e->getMessage()));
}