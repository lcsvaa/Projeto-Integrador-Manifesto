<?php
session_start();
require_once 'conexao.php'; // Arquivo com sua conexão ao banco

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(array('success' => false, 'message' => 'Método não permitido'));
    exit;
}

// Verifica CSRF token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    echo json_encode(array('success' => false, 'message' => 'Token inválido'));
    exit;
}

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('success' => false, 'message' => 'Usuário não autenticado'));
    exit;
}

$userId = $_SESSION['user_id'];

// Verifica se o usuário existe antes de começar a transação
try {
    $stmt = $pdo->prepare("SELECT id FROM tb_usuario WHERE id = ?");
    $stmt->execute(array($userId));
    if (!$stmt->fetch()) {
        echo json_encode(array('success' => false, 'message' => 'Usuário não encontrado'));
        exit;
    }
} catch (PDOException $e) {
    echo json_encode(array(
        'success' => false, 
        'message' => 'Erro ao verificar usuário',
        'error_details' => $e->getMessage()
    ));
    exit;
}

try {
    // Inicia transação para deletar dados relacionados
    $pdo->beginTransaction();
    
    // 1. Primeiro deleta endereços
    $stmt = $pdo->prepare("DELETE FROM tb_endereco WHERE idUsuario = ?");
    $stmt->execute(array($userId));
    
    // 2. Verifica se deletou os endereços antes de deletar o usuário
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_endereco WHERE idUsuario = ?");
    $stmt->execute(array($userId));
    $remainingAddresses = $stmt->fetchColumn();
    
    if ($remainingAddresses > 0) {
        throw new Exception("Falha ao remover todos os endereços");
    }
    
    // 3. Depois deleta o usuário
    $stmt = $pdo->prepare("DELETE FROM tb_usuario WHERE id = ?");
    $stmt->execute(array($userId));
    
    // Verifica se deletou o usuário
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM tb_usuario WHERE id = ?");
    $stmt->execute(array($userId));
    $userExists = $stmt->fetchColumn();
    
    if ($userExists > 0) {
        throw new Exception("Falha ao remover o usuário");
    }
    
    $pdo->commit();
    
    // Limpa a sessão completamente
    $_SESSION = array();
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    
    echo json_encode(array(
        'success' => true, 
        'redirect' => 'index.php',
        'message' => 'Conta removida com sucesso'
    ));
    
} catch (Exception $e) {
    $pdo->rollBack();
    echo json_encode(array(
        'success' => false, 
        'message' => 'Erro ao deletar conta: ' . $e->getMessage(),
        'error_details' => $e->getMessage()
    ));
}
?>