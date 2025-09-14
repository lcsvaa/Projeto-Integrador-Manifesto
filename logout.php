<?php
session_start();

// Verifica se é logout de admin ou usuário comum
$is_admin = isset($_SESSION['is_admin']);

// Destrói TODOS os dados da sessão
$_SESSION = array();

// Se desejar destruir a sessão completamente, apague também o cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destrói a sessão
session_destroy();

// Redireciona com mensagem apropriada
if ($is_admin) {
    header("Location: login.php?logout=admin");
} else {
    header("Location: login.php?logout=user");
}
exit();
?>