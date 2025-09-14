<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
include_once "conexao.php";
require_once __DIR__ . '/admin-auth.php';

if (isset($_GET['logout'])) {
    if ($_GET['logout'] === 'admin') {
        $mensagem = "Sessão administrativa encerrada com sucesso.";
    } elseif ($_GET['logout'] === 'user') {
        $mensagem = "Sessão encerrada com sucesso.";
    }
}



// Inicializa variáveis de mensagem
$mensagemCadastro = "Preencha todos os campos corretamente para concluir seu cadastro.";
$mensagemLogin = "Informe seu e-mail e senha para acessar sua conta.";
$sucesso = false;
$mensagem = "";
$abaAtiva = isset($_GET['aba']) ? $_GET['aba'] : 'login';

// Processamento do formulário de recuperação de senha
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit-recovery"])) {
    $emailRecovery = trim($_POST["recovery-email"]);

    try {
        $stmt = $pdo->prepare("SELECT id FROM tb_usuario WHERE email = ?");
        $stmt->execute([$emailRecovery]);

        if ($stmt->rowCount() > 0) {
            // Aqui você implementaria o envio do e-mail de recuperação
            $mensagemRecovery = "Um e-mail com instruções para redefinir sua senha foi enviado para " . htmlspecialchars($emailRecovery);
        } else {
            $mensagemRecovery = "E-mail não encontrado em nosso sistema.";
        }
    } catch (PDOException $e) {
        $mensagemRecovery = "Erro no sistema. Por favor, tente novamente.";
        error_log("Erro na recuperação de senha: " . $e->getMessage());
    }
}

// Processamento do formulário de cadastro
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit-register"])) {
    $abaAtiva = 'register';
    $nome = trim($_POST["register-name"]);
    $email = trim($_POST["register-email"]);
    $cpf = trim($_POST["register-cpf"]);
    $senha = $_POST["register-password"];
    $confirmarSenha = $_POST["register-confirm"];

    if ($senha !== $confirmarSenha) {
        $mensagem = "As senhas não coincidem.";
    } else {
        try {
            $stmt = $pdo->prepare("SELECT id FROM tb_usuario WHERE email = ?");
            $stmt->execute([$email]);

            if ($stmt->rowCount() > 0) {
                $mensagem = "E-mail já cadastrado.";
            } else {
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO tb_usuario (nomeUser, email, senha, cpf, statusATV, tipoUser) VALUES (?, ?, ?, ?, 'ativo', 'user')");

                if ($stmt->execute([$nome, $email, $senhaHash, $cpf])) {
                    $mensagem = "Cadastro realizado com sucesso!";
                    $_POST = [];
                    $abaAtiva = 'login'; // Volta para a aba de login após cadastro
                } else {
                    $mensagem = "Erro ao cadastrar.";
                }
            }
        } catch (PDOException $e) {
            $mensagem = "Erro no sistema. Por favor, tente novamente.";
            error_log("Erro no cadastro: " . $e->getMessage());
        }
    }
}

// Processamento do formulário de login
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["submit-login"])) {
    $abaAtiva = 'login';
    $email = trim($_POST["login-email"]);
    $senha = $_POST["login-password"];

    try {
        // Primeiro verifica se é admin
        $admin = verificarAdmin($email, $senha);
        if ($admin) {
            $_SESSION['is_admin'] = true;
            $_SESSION['admin_name'] = $admin['nome'];
            // Não defina user_id para admin
            header("Location: admin.php");
            exit();
        }

        // Se não for admin, verifica usuário normal
        $stmt = $pdo->prepare("SELECT id, nomeUser, senha FROM tb_usuario WHERE email = ? AND statusATV = 'ativo'");
        $stmt->execute([$email]);

        if ($stmt->rowCount() === 1) {
            $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($senha, $usuario["senha"])) {
                $_SESSION['user_id'] = $usuario["id"];
                $_SESSION['user_name'] = $usuario["nomeUser"];
                header("Location: index.php");
                exit();
            } else {
                $_SESSION['login_error'] = "Senha incorreta.";
                header("Location: login.php?aba=login");
                exit();
            }
        } else {
            $_SESSION['login_error'] = "Usuário não encontrado ou inativo.";
            header("Location: login.php?aba=login");
            exit();
        }
    } catch (PDOException $e) {
        $_SESSION['login_error'] = "Erro no sistema. Por favor, tente novamente.";
        error_log("Erro no login: " . $e->getMessage());
        header("Location: login.php?aba=login");
        exit();
    }
}

// Limpa erros de login se não estiver na aba de login
if ($abaAtiva !== 'login' && isset($_SESSION['login_error'])) {
    unset($_SESSION['login_error']);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Nome da Loja</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
    <link rel="icon" href="img/icone.png" type="image/png">
</head>

<body>
    <?php include_once "navbar.php" ?>
    
    <div class="resultados-produtos" style="display: none;">
        <p class="sem-resultados" style="display: none; color: #888; padding: 1rem;">Nenhum produto encontrado.</p>
        <div id="lista-produtos"></div>
    </div>

    <div class="banner">
        <h1>Login / Cadastro</h1>
    </div>

    <div class="auth-container">
        <div class="auth-form-container">
            <div class="auth-tabs">
                <div class="auth-tab <?= ($abaAtiva === 'login') ? 'active' : '' ?>" data-tab="login">Login</div>
                <div class="auth-tab <?= ($abaAtiva === 'register') ? 'active' : '' ?>" data-tab="register">Cadastro</div>
            </div>

            <!-- Formulário de Login -->
            <form id="login-form" class="auth-form <?= ($abaAtiva === 'login') ? 'active' : '' ?>" method="post">
                <?php if (!empty($_SESSION['login_error'])): ?>
                    <div class="alerta erro"><?= htmlspecialchars($_SESSION['login_error']) ?></div>
                    <?php unset($_SESSION['login_error']); ?>
                <?php elseif (!empty($mensagem) && $abaAtiva === 'login'): ?>
                    <div class="alerta"><?= htmlspecialchars($mensagem) ?></div>
                <?php elseif ($abaAtiva === 'login'): ?>
                    <div class="alerta"><?= htmlspecialchars($mensagemLogin) ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="login-email">E-mail</label>
                    <input type="email" id="login-email" name="login-email" class="form-control"
                        value="<?= isset($_POST['login-email']) ? htmlspecialchars($_POST['login-email']) : '' ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="login-password">Senha</label>
                    <div class="password-container">
                        <input type="password" id="login-password" name="login-password" class="form-control" required>
                        <i class="fas fa-eye toggle-password" data-target="login-password"></i>
                    </div>
                </div>

                <div class="remember-forgot">
                    <div class="remember-me">
                        <input type="checkbox" id="remember-me" name="remember-me">
                        <label for="remember-me">Lembrar-me</label>
                    </div>
                    <a href="#" class="forgot-password" id="forgot-password-link">Esqueceu sua senha?</a>
                </div>

                <button type="submit" class="auth-btn" name="submit-login">Entrar</button>

                <div class="social-login">
                    <p>Ou entre com</p>
                    <div class="social-icons">
                        <a href="login-google.php" class="social-icon" aria-label="Login com Google"><i class="fab fa-google"></i></a>
                        <a href="login-microsoft.php" class="social-icon" aria-label="Login com Microsoft"><i class="fab fa-microsoft"></i></a>
                        <a href="login-apple.php" class="social-icon" aria-label="Login com Apple"><i class="fab fa-apple"></i></a>
                    </div>
                </div>
            </form>

            <!-- Formulário de Cadastro -->
            <form id="register-form" class="auth-form <?= ($abaAtiva === 'register') ? 'active' : '' ?>" method="post">
                <?php if (!empty($mensagem) && $abaAtiva === 'register'): ?>
                    <div class="alerta"><?= htmlspecialchars($mensagem) ?></div>
                <?php endif; ?>

                <div class="form-group">
                    <label for="register-name">Nome Completo</label>
                    <input type="text" id="register-name" name="register-name" class="form-control"
                        value="<?= isset($_POST['register-name']) ? htmlspecialchars($_POST['register-name']) : '' ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="register-email">E-mail</label>
                    <input type="email" id="register-email" name="register-email" class="form-control"
                        value="<?= isset($_POST['register-email']) ? htmlspecialchars($_POST['register-email']) : '' ?>"
                        required>
                </div>

                <div class="form-group">
                    <label for="register-cpf">CPF</label>
                    <input type="text" id="register-cpf" name="register-cpf" class="form-control"
                        value="<?= isset($_POST['register-cpf']) ? htmlspecialchars($_POST['register-cpf']) : '' ?>"
                        placeholder="000.000.000-00" required pattern="\d{3}\.\d{3}\.\d{3}-\d{2}">
                    <div id="cpf-error" class="cpf-error">CPF inválido. Por favor, digite um CPF válido.</div>
                </div>

                <div class="form-group">
                    <label for="register-password">Senha</label>
                    <div class="password-container">
                        <input type="password" id="register-password" name="register-password" class="form-control" required minlength="6">
                        <i class="fas fa-eye toggle-password" data-target="register-password"></i>
                    </div>
                    <small class="hint">Mínimo de 6 caracteres</small>
                </div>

                <div class="form-group">
                    <label for="register-confirm">Confirme a Senha</label>
                    <div class="password-container">
                        <input type="password" id="register-confirm" name="register-confirm" class="form-control" required>
                        <i class="fas fa-eye toggle-password" data-target="register-confirm"></i>
                    </div>
                </div>

                <div class="form-group">
                    <div class="remember-me">
                        <input type="checkbox" id="accept-terms" name="accept-terms" required>
                        <label for="accept-terms">Li e aceito os <a href="privacidade.php" style="color: #e91e63;">Termos de Uso</a> e <a href="privacidade.php" style="color: #e91e63;">Política de Privacidade</a></label>
                    </div>
                </div>

                <button type="submit" class="auth-btn" name="submit-register">Cadastrar</button>
            </form>
        </div>

        <div class="auth-benefits">
            <h3 class="benefits-title">Benefícios de ter uma conta</h3>
            <ul class="benefits-list">
                <li class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-shopping-bag"></i>
                    </div>
                    <div class="benefit-content">
                        <h4>Histórico de Pedidos</h4>
                        <p>Acompanhe todos os seus pedidos em um só lugar e refaça compras com apenas um clique.</p>
                    </div>
                </li>

                <li class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <div class="benefit-content">
                        <h4>Frete mais rápido</h4>
                        <p>Endereços salvos agilizam o processo de compra e podem garantir frete mais rápido.</p>
                    </div>
                </li>

                <li class="benefit-item">
                    <div class="benefit-icon">
                        <i class="fas fa-percentage"></i>
                    </div>
                    <div class="benefit-content">
                        <h4>Ofertas Exclusivas</h4>
                        <p>Receba descontos especiais e seja o primeiro a saber sobre novos lançamentos.</p>
                    </div>
                </li>
            </ul>
        </div>
    </div>

    <!-- Modal de Recuperação de Senha -->
    <div id="recovery-modal" class="modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <h2>Recuperar Senha</h2>
            <p>Digite seu e-mail cadastrado para receber instruções de recuperação de senha.</p>

            <?php if (!empty($mensagemRecovery)): ?>
                <div class="alerta <?= strpos($mensagemRecovery, 'enviado') !== false ? 'sucesso' : 'erro' ?>">
                    <?= htmlspecialchars($mensagemRecovery) ?>
                </div>
            <?php endif; ?>

            <form id="recovery-form" method="post">
                <div class="form-group">
                    <label for="recovery-email">E-mail</label>
                    <input type="email" id="recovery-email" name="recovery-email" class="form-control" required>
                </div>
                <button type="submit" class="auth-btn" name="submit-recovery">Enviar Instruções</button>
            </form>
        </div>
    </div>

    <?php include_once "footer.php" ?>
    <script src="js/login.js"></script>
    <script>
        // Script para controlar o modal de recuperação de senha
        document.addEventListener('DOMContentLoaded', function() {
            const modal = document.getElementById('recovery-modal');
            const btn = document.getElementById('forgot-password-link');
            const span = document.getElementsByClassName('close-modal')[0];

            // Função para abrir o modal
            btn.onclick = function(e) {
                // e.preventDefault();
                openModal();
            }

            // Função para fechar o modal
            function closeModal() {
                modal.style.display = 'none';
                document.body.style.overflow = 'auto'; // Restaura a rolagem da página
            }

            // Função para abrir o modal
            function openModal() {
                modal.style.display = 'block';
                document.body.style.overflow = 'hidden'; // Impede a rolagem da página
            }

            // Fechar ao clicar no X
            span.onclick = closeModal;

            // Fechar ao clicar fora do modal
            window.onclick = function(event) {
                if (event.target == modal) {
                    closeModal();
                }
            }

            // Fechar ao pressionar ESC
            document.addEventListener('keydown', function(event) {
                if (event.key === 'Escape' && modal.style.display === 'block') {
                    closeModal();
                }
            });

            // Centraliza o modal novamente se a janela for redimensionada
            window.addEventListener('resize', function() {
                if (modal.style.display === 'block') {
                    const modalContent = document.querySelector('.modal-content');
                    modalContent.style.top = '50%';
                    modalContent.style.left = '50%';
                    modalContent.style.transform = 'translate(-50%, -50%)';
                }
            });
        });
    </script>
</body>

</html>