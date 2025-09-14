<?php 
require_once "conexao.php";
session_start();
header('Content-Type: application/json');

$codigo = isset($_GET['codigo']) ? strtoupper(trim($_GET['codigo'])) : '';

if (empty($codigo)) {
    echo json_encode(array('status' => 'error', 'msg' => 'Código do cupom não informado.'));
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM tb_cupom WHERE codigo = :codigo AND statusCupom = 'ativo' AND dataValidade >= CURDATE()");
$stmt->bindParam(':codigo', $codigo);
$stmt->execute();
$cupom = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cupom) {
    echo json_encode(array('status' => 'error', 'msg' => 'Cupom inválido, expirado ou inativo.'));
    exit;
}

if (!isset($_SESSION['user_id'])) {
    echo json_encode(array('status' => 'error', 'msg' => 'É necessário estar logado para usar cupom.'));
    exit;
}

$idUsuario = $_SESSION['user_id'];
$idCupom = $cupom['idCupom'];

$verificaUso = $pdo->prepare("
    SELECT COUNT(*) FROM tb_compra
    WHERE idUsuario = :idUsuario AND idCupom = :idCupom
");
$verificaUso->bindParam(':idUsuario', $idUsuario);
$verificaUso->bindParam(':idCupom', $idCupom);
$verificaUso->execute();
$jaUsou = $verificaUso->fetchColumn();

if ($jaUsou > 0) {
    echo json_encode(array('status' => 'error', 'msg' => 'Você já utilizou este cupom anteriormente.'));
    exit;
}

$subtotal = 0;
$carrinho = isset($_SESSION['carrinho']) ? $_SESSION['carrinho'] : array();
foreach ($carrinho as $item) {
    $subtotal += $item['preco'] * $item['qtd'];
}

if ($subtotal < $cupom['valorCompraMin']) {
    echo json_encode(array(
        'status' => 'error',
        'msg' => "Cupom válido, mas o valor mínimo de compra é R$ " . number_format($cupom['valorCompraMin'], 2, ',', '.')
    ));
    exit;
}

if ($cupom['quantidadeUso'] > 0 && $cupom['utilizados'] >= $cupom['quantidadeUso']) {
    echo json_encode(array('status' => 'error', 'msg' => 'Este cupom já atingiu o número máximo de usos.'));
    exit;
}

$_SESSION['cupom'] = array(
    'codigo' => $cupom['codigo'],
    'tipo' => $cupom['tipoCupom'],       // 'valor' ou 'porcentagem'
    'valor' => floatval($cupom['porcentagemDesconto']),
    'descricao' => isset($cupom['descricaoCupom']) ? $cupom['descricaoCupom'] : '',
    'id' => $cupom['idCupom']
);

echo json_encode(array(
    'status' => 'ok',
    'msg' => $cupom['tipoCupom'] === 'porcentagem'
        ? "Cupom aplicado com sucesso! Desconto de {$cupom['porcentagemDesconto']}%."
        : "Cupom aplicado com sucesso! Desconto de R$ " . number_format($cupom['porcentagemDesconto'], 2, ',', '.') . ".",
    'tipo' => $cupom['tipoCupom'],
    'valor' => floatval($cupom['porcentagemDesconto'])
));