<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../conexao.php';

try {
    // Verify request method
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido', 405);
    }

    // Validate email
    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : false;
    if (!$email) {
        throw new Exception('E‑mail inválido', 422);
    }

    // Delete from newsletter
    $stmt = $pdo->prepare(
        'DELETE FROM tb_newsletter WHERE emailNews = :email'
    );
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    // Check if email was actually removed
    if ($stmt->rowCount() === 0) {
        throw new Exception('E‑mail não encontrado', 404);
    }

    // Success response
    echo json_encode(array(
        'ok'      => true,
        'message' => 'E‑mail removido com sucesso!'
    ));

} catch (Exception $e) {
    // Error handling
    $status = $e->getCode() ? $e->getCode() : 500;
    
    // HTTP status code fallback for PHP < 5.4
    if (function_exists('http_response_code')) {
        http_response_code($status);
    } else {
        header("HTTP/1.1 $status");
    }

    // Error response
    echo json_encode(array(
        'ok'      => false,
        'message' => $e->getMessage()
    ));
}