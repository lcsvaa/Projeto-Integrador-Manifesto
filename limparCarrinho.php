<?php
session_start();
header('Content-Type: application/json');

if (isset($_SESSION['carrinho'])) {
    unset($_SESSION['carrinho']);
}

echo json_encode(array('status' => 'ok'));
?>