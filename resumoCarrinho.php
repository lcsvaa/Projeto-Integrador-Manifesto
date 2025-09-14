<?php
session_start();
header('Content-Type: application/json');

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$subtotal = 0;
$desconto = 0;
$frete = 0;

$carrinho = isset($_SESSION['carrinho']) ? $_SESSION['carrinho'] : array();

foreach ($carrinho as $item) {
    $subtotal += $item['preco'] * $item['qtd'];
}

if (!empty($_SESSION['carrinho'])) {
    $frete = 18.90;
}

if (isset($_SESSION['cupom'])) {
    $tipo = isset($_SESSION['cupom']['tipoCupom']) ? $_SESSION['cupom']['tipoCupom'] : 'porcentagem';
    $valor = isset($_SESSION['cupom']['porcentagem']) ? $_SESSION['cupom']['porcentagem'] : 0;

    if ($tipo === 'porcentagem') {
        $desconto = ($subtotal * $valor) / 100;
    } elseif ($tipo === 'fixo') {
        $desconto = $valor;
    }

    if ($desconto > $subtotal) {
        $desconto = $subtotal;
    }
}

$total = $subtotal + $frete - $desconto;

function formatarPreco($valor) {
    return number_format($valor, 2, ',', '.');
}

echo json_encode(array(
    'subtotal' => formatarPreco($subtotal),
    'frete' => formatarPreco($frete),
    'desconto' => formatarPreco($desconto),
    'total' => formatarPreco($total)
));