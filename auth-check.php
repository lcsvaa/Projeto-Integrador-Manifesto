<?php
session_start();

// Configurações de segurança
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');

// Verifica se a sessão é válida
function is_valid_session() {
    // Adiciona verificação adicional de segurança da sessão
    if (isset($_SESSION['user_ip']) && $_SESSION['user_ip'] !== $_SERVER['REMOTE_ADDR']) {
        return false;
    }
    if (isset($_SESSION['user_agent']) && $_SESSION['user_agent'] !== $_SERVER['HTTP_USER_AGENT']) {
        return false;
    }
    return true;
}

// Verifica se é admin com sessão válida
if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] === true && is_valid_session()) {
    return; // Permite acesso para admin
}

// Verifica se é usuário comum com sessão válida
if (isset($_SESSION['user_id']) && is_valid_session()) {
    return; // Permite acesso para usuário comum
}

// Se não for nenhum dos dois ou sessão inválida, redireciona para login
session_unset();
session_destroy();

// Prevenção contra cache
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Redirecionamento seguro
$redirect_url = 'login.php?error=acesso_negado';
if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
    $redirect_url = 'https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $redirect_url;
} else {
    $redirect_url = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/' . $redirect_url;
}

header('Location: ' . $redirect_url);
exit();
?>