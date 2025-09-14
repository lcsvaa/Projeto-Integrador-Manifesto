<?php
require_once 'conexao.php';

header('Content-Type: application/json');

session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

$userId = $_SESSION['user_id'];

try {
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $stmt = $pdo->prepare("SELECT 
        idEndereco, 
        apelidoEndereco, 
        cep, 
        rua, 
        numero, 
        complemento, 
        bairro, 
        cidade
        FROM tb_endereco 
        WHERE idUsuario = ? 
        ORDER BY apelidoEndereco LIKE '(Principal)%' DESC, idEndereco DESC");
        
    $stmt->execute([$userId]);
    
    if ($stmt->rowCount() === 0) {
        echo json_encode([]);
        exit;
    }
    
    $enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($enderecos);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro no banco de dados',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'error' => 'Erro no servidor',
        'details' => $e->getMessage()
    ]);
}