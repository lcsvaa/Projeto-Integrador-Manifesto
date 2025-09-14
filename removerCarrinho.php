<?php
session_start();
unset($_SESSION['carrinho']);
unset($_SESSION['cupom']);
echo json_encode(array('status' => 'ok'));