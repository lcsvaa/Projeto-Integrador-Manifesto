<?php
session_start();
require_once "conexao.php";

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);
$codigo = trim($data['codigo'] ?? '');

if (!$codigo) {
    echo json_encode(["status" => "erro", "mensagem" => "Código do cupom não informado."]);
    exit;
}


$stmt = $pdo->prepare("SELECT * FROM tb_cupom 
    WHERE codigo = :codigo 
    AND statusCupom = 'ativo' 
    AND dataValidade >= CURDATE()
    AND utilizados < quantidadeUso
");
$stmt->bindParam(':codigo', $codigo);
$stmt->execute();
$cupom = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cupom) {
    echo json_encode(["status" => "erro", "mensagem" => "Cupom inválido, expirado ou esgotado."]);
    exit;
}


$subtotal = 0;
if (!isset($_SESSION['carrinho']) || count($_SESSION['carrinho']) === 0) {
    echo json_encode(["status" => "erro", "mensagem" => "Carrinho vazio."]);
    exit;
}

foreach ($_SESSION['carrinho'] as $item) {
    $subtotal += $item['preco'] * $item['qtd'];
}

if ($subtotal < $cupom['valorCompraMin']) {
    echo json_encode([
        "status" => "erro",
        "mensagem" => "Valor mínimo de R$ " . number_format($cupom['valorCompraMin'], 2, ',', '.') . " não atingido."
    ]);
    exit;
}


$_SESSION['cupom'] = [
    'id' => $cupom['idCupom'],
    'codigo' => $cupom['codigo'],
    'porcentagem' => $cupom['porcentagemDesconto'],
    'descricao' => $cupom['descricaoCupom']
];

echo json_encode([
    "status" => "ok",
    "mensagem" => "Cupom aplicado com sucesso.",
    "codigo" => $cupom['codigo'],
    "porcentagem" => $cupom['porcentagemDesconto'],
    "descricao" => $cupom['descricaoCupom']
]);