<?php
session_start();
ob_start();
include_once "conexao.php";

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Inicializa variáveis
$mensagem = '';
$sucesso = false;

// Processamento do formulário de recuperação de senha
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit-recovery"])) {
    $emailRecovery = trim($_POST["recovery-email"]);
    
    try {
        $stmt = $pdo->prepare("SELECT id FROM tb_usuario WHERE email = ?");
        $stmt->execute([$emailRecovery]);
        
        if ($stmt->rowCount() > 0) {
            // Simula o envio do e-mail (substitua por seu código real)
            $_SESSION['recovery_email'] = $emailRecovery;
            $_SESSION['recovery_message'] = [
                'type' => 'success',
                'text' => "Um e-mail com instruções para redefinir sua senha foi enviado para " . htmlspecialchars($emailRecovery)
            ];
            
            // Redireciona para a página de login com o modal aberto
            header("Location: login.php?recovery=1");
            exit();
        } else {
            $mensagem = "E-mail não encontrado em nosso sistema.";
        }
    } catch (PDOException $e) {
        $mensagem = "Erro no sistema. Por favor, tente novamente.";
        error_log("Erro na recuperação de senha: " . $e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Senha | Nome da Loja</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" href="img/icone.png" type="image/png">
    <style>
        /* Estilos específicos para esta página */
        .recovery-container {
            max-width: 500px;
            margin: 2rem auto;
            padding: 2rem;
            background-color: #1a1a1a;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .recovery-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .recovery-header h1 {
            color: #e91e63;
            font-size: 2rem;
            margin-bottom: 1rem;
        }
        
        .recovery-footer {
            text-align: center;
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #333;
        }
    </style>
</head>
<body>
    <?php include_once "navbar.php" ?>

    <div class="banner">
        <h1>Recuperar Senha</h1>
    </div>

    <div class="container">
        <div class="recovery-container">
            <div class="recovery-header">
                <h1><i class="fas fa-key"></i> Redefinição de Senha</h1>
                <p>Digite seu e-mail para receber o link de redefinição</p>
            </div>
            
            <?php if (!empty($mensagem)): ?>
                <div class="alerta <?= $sucesso ? 'sucesso' : 'erro' ?>">
                    <?= htmlspecialchars($mensagem) ?>
                </div>
            <?php endif; ?>
            
            <form method="post" class="auth-form">
                <div class="form-group">
                    <label for="recovery-email">E-mail Cadastrado</label>
                    <input type="email" id="recovery-email" name="recovery-email" class="form-control" required>
                </div>
                
                <button type="submit" class="auth-btn" name="submit-recovery">
                    <i class="fas fa-paper-plane"></i> Enviar Link
                </button>
            </form>
            
            <div class="recovery-footer">
                <p>Lembrou sua senha? <a href="login.php" style="color: #e91e63;">Faça login aqui</a></p>
            </div>
        </div>
    </div>

    <?php include_once "footer.php" ?>

    <script>
        // Validação simples do formulário
        document.querySelector('form').addEventListener('submit', function(e) {
            const email = document.getElementById('recovery-email').value.trim();
            
            if (!email) {
                e.preventDefault();
                alert('Por favor, insira seu e-mail cadastrado.');
                return false;
            }
            
            // Validação básica de e-mail
            if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
                e.preventDefault();
                alert('Por favor, insira um e-mail válido.');
                return false;
            }
        });
    </script>
</body>
</html>