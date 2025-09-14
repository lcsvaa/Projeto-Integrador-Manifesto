<?php
session_start();

if (empty($_SESSION['csrf_token'])) {
    if (function_exists('random_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $_SESSION['csrf_token'] = bin2hex(openssl_random_pseudo_bytes(32));
    } else {
        // Fallback inseguro (não recomendado para produção)
        $_SESSION['csrf_token'] = bin2hex(substr(md5(mt_rand()), 0, 32));
    }
}

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once 'conexao.php';

error_reporting(E_ALL);
ini_set('display_errors', 1);

$user_id = $_SESSION['user_id'];

try {
    $stmt = $pdo->prepare("SELECT * FROM tb_usuario WHERE id = ? AND statusATV = 'ativo'");
    $stmt->execute(array($user_id));
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user) {
        throw new Exception("Usuário não encontrado ou conta desativada");
    }

    $stmt = $pdo->prepare("SELECT * FROM tb_endereco WHERE idUsuario = ? ORDER BY apelidoEndereco LIKE '(Principal)%' DESC, idEndereco DESC");
    $stmt->execute(array($user_id));
    $enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);


    $success_message = isset($_SESSION['success_message']) ? $_SESSION['success_message'] : '';
    $error_message = isset($_SESSION['error_message']) ? $_SESSION['error_message'] : '';
    unset($_SESSION['success_message'], $_SESSION['error_message']);
?>
    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Meu Perfil | Manifesto</title>
        <!-- jQuery PRIMEIRO -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
        <!-- jQuery Mask DEPOIS do jQuery -->
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
        <link rel="icon" href="img/icone.png" type="image/png">

        <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
        <link rel="stylesheet" href="css/style.css">
        <link rel="stylesheet" href="css/profile.css">

    </head>

    <body>

        <?php include_once "navbar.php" ?>

        <div class="resultados-produtos" style="display: none;">
            <p class="sem-resultados" style="display: none; color: #888; padding: 1rem;">Nenhum produto encontrado.</p>
            <div id="lista-produtos"></div>
        </div>

        <div class="banner">
            <h1>Olá, <?= htmlspecialchars($user['nomeUser']) ?>!</h1>
            <p class="welcome-message">Bem-vindo(a) ao seu perfil</p>
        </div>

        <main class="profile-container">
            <aside class="profile-sidebar">
                <div class="profile-card">
                    <div class="profile-avatar">
                        <img src="https://i.pinimg.com/736x/c0/74/9b/c0749b7cc401421662ae901ec8f9f660.jpg" alt="Foto de perfil">
                        <button class="edit-avatar" aria-label="Editar foto de perfil"><i class="fas fa-camera"></i></button>
                    </div>
                    <h3 class="profile-name"><?= htmlspecialchars($user['nomeUser']) ?></h3>
                    <p class="profile-email"><?= htmlspecialchars($user['email']) ?></p>
                </div>

                <nav>
                    <ul class="profile-menu">
                        <li class="active" data-tab="personal-data"><i class="fas fa-user"></i> Dados Pessoais</li>
                        <li data-tab="addresses"><i class="fas fa-map-marker-alt"></i> Endereços</li>
                        <li data-tab="orders"><i class="fas fa-shopping-bag"></i> Meus Pedidos</li>
                        <li data-tab="security"><i class="fas fa-lock"></i> Segurança</li>
                        <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
                    </ul>
                </nav>
            </aside>

            <div class="profile-content">
                
                <section class="profile-section active" id="personal-data">
    <h2 class="section-title">Dados Pessoais</h2>

    <?php if ($success_message): ?>
        <div class="notification success"><?php echo $success_message; ?></div>
    <?php endif; ?>
    <?php if ($error_message): ?>
        <div class="notification error"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <form id="personal-data-form">
        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">

        <div class="form-row">
            <div class="form-group">
                <label for="name">Nome Completo *</label>
                <input type="text" id="name" name="name"
                    value="<?php echo htmlspecialchars($user['nomeUser']); ?>"
                    required minlength="3" maxlength="180">
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="email">E-mail *</label>
                <input type="email" id="email" name="email"
                    value="<?php echo htmlspecialchars($user['email']); ?>"
                    required>
            </div>
            <div class="form-group">
                <label for="phone">Telefone *</label>
                <input type="tel" id="phone" name="phone"
                    value="<?php echo htmlspecialchars(isset($user['telefone']) ? $user['telefone'] : ''); ?>"
                    pattern="\([0-9]{2}\) [0-9]{4,5}-[0-9]{4}"
                    title="Formato: (99) 99999-9999" required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="cpf">CPF *</label>
                <input type="text" id="cpf" name="cpf"
                    value="<?php echo htmlspecialchars($user['cpf']); ?>"
                    readonly class="disabled-field">
            </div>
            <div class="form-group">
                <label for="birthdate">Data de Nascimento *</label>
                <input type="date" id="birthdate" name="birthdate"
                    value="<?php echo htmlspecialchars(isset($user['dataNascimento']) ? $user['dataNascimento'] : ''); ?>"
                    max="<?php echo date('Y-m-d', strtotime('-18 years')); ?>" required>
            </div>
        </div>

        <div class="form-actions">
            <button type="button" class="btn-cancel">Cancelar</button>
            <button type="submit" class="btn-save">Salvar Alterações</button>
        </div>
    </form>
</section>

                <section class="profile-section" id="addresses">
    <h2 class="section-title">Meus Endereços</h2>

    <div class="addresses-list" id="addresses-container">
        <?php foreach ($enderecos as $endereco): ?>
            <?php $isPrincipal = strpos($endereco['apelidoEndereco'], '(Principal)') !== false; ?>
            <article class="address-card <?php echo $isPrincipal ? 'principal' : ''; ?>">
                <div class="address-header">
                    <h3><?php echo htmlspecialchars($endereco['apelidoEndereco']); ?></h3>
                    <div class="address-actions">
                        <button class="btn-edit" data-id="<?php echo $endereco['idEndereco']; ?>">
                            <i class="fas fa-edit"></i>
                        </button>
                        <button class="btn-delete" data-id="<?php echo $endereco['idEndereco']; ?>">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="address-content">
                    <p><?php echo htmlspecialchars($endereco['rua'] . ', ' . $endereco['numero']); ?></p>
                    <?php if (!empty($endereco['complemento'])): ?>
                        <p>Complemento: <?php echo htmlspecialchars($endereco['complemento']); ?></p>
                    <?php endif; ?>
                    <p>Bairro: <?php echo htmlspecialchars($endereco['bairro']); ?></p>
                    <p><?php echo htmlspecialchars($endereco['cidade'] . ' - ' . $endereco['cep']); ?></p>
                </div>
            </article>
        <?php endforeach; ?>
    </div>

    <button class="btn-add-address" id="add-address-btn">
        <i class="fas fa-plus"></i> Adicionar Novo Endereço
    </button>

    <div class="address-form-container" id="address-form-container" style="display: none;">
        <h3 class="form-title">Adicionar Endereço</h3>
        <form id="address-form">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <div class="form-row">
                <div class="form-group">
                    <label for="address-name">Apelido do Endereço</label>
                    <input type="text" id="address-name" name="address-name" placeholder="Ex: Casa, Trabalho">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="address-cep">CEP</label>
                    <input type="text" id="address-cep" name="address-cep" required>
                    <small class="hint">Digite o CEP para autocompletar</small>
                </div>
                <div class="form-group">
                    <label for="address-street">Endereço</label>
                    <input type="text" id="address-street" name="address-street" readonly>
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="address-number">Número</label>
                    <input type="text" id="address-number" name="address-number" required>
                </div>
                <div class="form-group">
                    <label for="address-complement">Complemento</label>
                    <input type="text" id="address-complement" name="address-complement">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label for="address-neighborhood">Bairro</label>
                    <input type="text" id="address-neighborhood" name="address-neighborhood" readonly>
                </div>
                <div class="form-group">
                    <label for="address-city">Cidade</label>
                    <input type="text" id="address-city" name="address-city" readonly>
                </div>
                <div class="form-group">
                    <label for="address-state">UF</label>
                    <input type="text" id="address-state" name="address-state" maxlength="2" required readonly>
                </div>
            </div>
            <div class="form-checkbox">
                <input type="checkbox" id="address-default" name="address-default">
                <label for="address-default">Tornar este endereço principal</label>
            </div>
            <div class="form-actions">
                <button type="button" class="btn-cancel" id="cancel-address-btn">Cancelar</button>
                <button type="submit" class="btn-save">Salvar Endereço</button>
            </div>
        </form>
    </div>
</section>

<?php


$idUsuario = $_SESSION['user_id'];

// Buscar os pedidos do usuário
$sql = "SELECT * FROM tb_compra WHERE idUsuario = :idUsuario ORDER BY dataCompra DESC";
$stmt = $pdo->prepare($sql);
$stmt->bindParam(':idUsuario', $idUsuario, PDO::PARAM_INT);
$stmt->execute();
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="profile-section" id="orders"> 
    <h2 class="section-title">Meus Pedidos</h2>
    <div class="orders-preview">
        <?php if (!empty($pedidos)): ?>
            <?php foreach ($pedidos as $pedido): ?>
                <div class="order-card">
                    <div class="order-header">
                        <div class="order-id">Pedido #<?php echo $pedido['id']; ?></div>
                        <div class="order-date"><?php echo date('d/m/Y', strtotime($pedido['dataCompra'])); ?></div>
                        <div class="order-status 
                            <?php echo $pedido['statusCompra'] == 'Recebido' ? 'status-delivered' : ''; ?>">
                            <?php echo htmlspecialchars($pedido['statusCompra']); ?>
                        </div>
                    </div>

                    <div class="order-products">
                        <?php
                        $sqlItens = "
                            SELECT ic.*, p.nomeItem, ip.nomeImagem
                            FROM tb_itemCompra ic
                            JOIN tb_produto p ON ic.idProduto = p.id
                            LEFT JOIN tb_imagemProduto ip ON ip.idProduto = p.id AND ip.statusImagem = 'principal'
                            WHERE ic.idCompra = :idCompra
                        ";
                        $stmtItens = $pdo->prepare($sqlItens);
                        $stmtItens->bindParam(':idCompra', $pedido['id'], PDO::PARAM_INT);
                        $stmtItens->execute();
                        $itens = $stmtItens->fetchAll(PDO::FETCH_ASSOC);

                        foreach ($itens as $item):
                            $imagem = !empty($item['nomeImagem']) ? 'uploads/produtos/' . $item['nomeImagem'] : 'imagens/sem-imagem.png';
                        ?>
                        <div class="product-item">
                            <img src="<?php echo $imagem; ?>" alt="Produto" class="product-image">
                            <div class="product-info">
                                <div class="product-name"><?php echo htmlspecialchars($item['nomeItem']); ?></div>
                                <div class="product-price">R$ <?php echo number_format($item['valorUnitario'], 2, ',', '.'); ?></div>
                                <div class="product-quantity">
                                    <?php echo $item['quantidade']; ?>x (Tamanho: <?php echo htmlspecialchars($item['tamanho']); ?>)
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="order-total product-price">Total: R$ <?php echo number_format($pedido['valorTotal'], 2, ',', '.'); ?></div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>Você ainda não fez nenhum pedido.</p>
        <?php endif; ?>
    </div>
</section>


                <!-- Outras seções do perfil -->
                <section class="profile-section" id="security">
                    <h2 class="section-title">Segurança</h2>

                    <form id="change-password-form">
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">

                        <div class="form-group password-container">
                            <label for="current-password">Senha Atual *</label>
                            <input type="password" id="current-password" name="current_password" required>
                            <span class="toggle-password" data-target="current-password">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>

                        <div class="form-group password-container">
                            <label for="new-password">Nova Senha *</label>
                            <input type="password" id="new-password" name="new_password" required minlength="6">
                            <span class="toggle-password" data-target="new-password">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>

                        <div class="form-group password-container">
                            <label for="confirm-password">Confirmar Nova Senha *</label>
                            <input type="password" id="confirm-password" name="confirm_password" required minlength="6">
                            <span class="toggle-password" data-target="confirm-password">
                                <i class="fas fa-eye"></i>
                            </span>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn-save">Alterar Senha</button>
                        </div>
                        <br>
                        <hr>

                        <div class="security-section">
                            <h3>Deletar Conta</h3>
                            <p>Esta ação é irreversível e removerá todos os seus dados permanentemente.</p>
                            <div class="delete-account-container">
                                <button id="delete-account-btn" class="btn-danger">Deletar Minha Conta</button>
                            </div>
                        </div>
                    </form>
                </section>
            </div>
        </main>

        <?php include_once "footer.php" ?>

        <script src="js/profile.js"></script>
        
    </body>

    </html>
<?php
} catch (PDOException $e) {
    error_log("Erro no banco de dados: " . $e->getMessage());
    $_SESSION['error_message'] = "Erro ao carregar dados do perfil";
    header('Location: login.php');
    exit();
} catch (Exception $e) {
    session_destroy();
    // Como a sessão foi destruída, é necessário iniciar novamente para setar a mensagem
    session_start();
    $_SESSION['error_message'] = $e->getMessage();
    header('Location: login.php');
    exit();
}
?>