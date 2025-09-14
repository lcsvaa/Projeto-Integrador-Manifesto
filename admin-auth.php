<?php
function verificarAdmin($email, $senha) {
    $adminFile = __DIR__ . '/admins.json';
    
    if (!file_exists($adminFile)) {
        error_log("Arquivo de admins não encontrado: " . $adminFile);
        return false;
    }
    
    $json = file_get_contents($adminFile);
    if ($json === false) {
        error_log("Falha ao ler arquivo de admins");
        return false;
    }
    
    $dados = json_decode($json, true);
    
    if (json_last_error() !== JSON_ERROR_NONE || !isset($dados['admins'])) {
        error_log("Erro no JSON de admins");
        return false;
    }
    
    foreach ($dados['admins'] as $admin) {
        // Comparação direta de email e senha (texto plano)
        if ($admin['email'] === $email && $admin['senha'] === $senha) {
            return $admin;
        }
    }
    
    return false;
}
?>