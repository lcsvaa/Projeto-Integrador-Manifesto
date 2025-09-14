<?php
header('Content-Type: application/json; charset=utf-8');

require_once '../conexao.php';

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido', 405);
    }

    $email = isset($_POST['email']) ? filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) : false;
    if (!$email) {
        throw new Exception('E‑mail inválido', 422);
    }

    $stmt = $pdo->prepare(
        'INSERT INTO tb_newsletter (emailNews) VALUES (:email)'
    );
    $stmt->bindValue(':email', $email);
    $stmt->execute();

    echo json_encode(array(
        'ok'      => true,
        'message' => 'E‑mail cadastrado com sucesso!'
    ));
} catch (Exception $e) {
    $status = 500;
    $message = 'Erro ao cadastrar e-mail';
    
    if ($e instanceof PDOException && isset($e->errorInfo[1]) && $e->errorInfo[1] === 1062) {
        $status = 409;
        $message = 'O E-mail inserido já está cadastrado!';
    } elseif ($e->getCode()) {
        $status = $e->getCode();
        $message = $e->getMessage();
    }

    if (function_exists('http_response_code')) {
        http_response_code($status);
    } else {
        header("HTTP/1.1 $status");
    }

    echo json_encode(array(
        'ok'      => false,
        'message' => $message
    ));
}