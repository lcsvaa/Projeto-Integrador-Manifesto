<?php
require_once 'config.php'; // Deve conter: $acess_token

// Recebe os dados enviados via JSON
$input = file_get_contents('php://input');
$dados = json_decode($input, true);

// Garante dados essenciais
$transactionAmount = isset($dados['total']) ? floatval($dados['total']) : 0;
$descricao = isset($dados['descricao']) ? $dados['descricao'] : 'Pedido na loja via PIX';
$email = isset($dados['email']) ? $dados['email'] : 'sem-email@teste.com';

// Monta os dados do pagamento PIX
$data = array(
    "transaction_amount" => $transactionAmount,
    "description" => $descricao,
    "payment_method_id" => "pix",
    "payer" => array(
        "email" => $email
    )
);

// Configurações de requisição
$url = "https://api.mercadopago.com/v1/payments";
$headers = array(
    "Authorization: Bearer " . $acess_token,
    "Content-Type: application/json",
    "X-Idempotency-Key: " . bin2hex(openssl_random_pseudo_bytes(16))
);

// Inicializa a requisição cURL
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Recomendado para segurança

// Adiciona tratamento de erros
if ($response === false) {
    $error = curl_error($ch);
    $response = array(
        'status' => 'error',
        'message' => 'Erro na comunicação com o Mercado Pago',
        'error_detail' => $error
    );
    http_response_code(500);
    echo json_encode($response);
    exit;
}

// Executa e trata a resposta
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Verifica se o JSON é válido
$responseData = json_decode($response, true);
if (json_last_error() !== JSON_ERROR_NONE) {
    $response = array(
        'status' => 'error',
        'message' => 'Resposta inválida do servidor',
        'raw_response' => $response
    );
    $httpCode = 500;
}

// Retorna resposta JSON ao front-end
header('Content-Type: application/json');
http_response_code($httpCode);
echo is_array($responseData) ? json_encode($responseData) : $response;
?>