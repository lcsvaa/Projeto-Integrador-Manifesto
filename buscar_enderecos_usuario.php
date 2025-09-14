<?php
session_start();
require_once 'conexao.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'erro', 'msg' => 'Usuário não autenticado.']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("
    SELECT 
        u.nomeUser, u.email, u.telefone,
        e.idEndereco,
        e.cep, e.rua AS endereco, e.numero, e.complemento, e.bairro, e.cidade
    FROM tb_endereco e
    INNER JOIN tb_usuario u ON e.idUsuario = u.id
    WHERE e.idUsuario = :id
    ");
    $stmt->execute([':id' => $userId]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($resultados) {
        echo json_encode(['status' => 'ok', 'enderecos' => $resultados]);
    } else {
        echo json_encode(['status' => 'vazio']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'erro', 'msg' => 'Erro ao buscar endereço', 'erro' => $e->getMessage()]);
}