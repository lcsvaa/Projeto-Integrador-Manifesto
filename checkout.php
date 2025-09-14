<?php
require_once "conexao.php";
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$userId = $_SESSION['user_id'];
$itensCarrinho = isset($_SESSION['carrinho']) ? $_SESSION['carrinho'] : array();
$cupomAtivo = isset($_SESSION['cupom']) ? $_SESSION['cupom'] : null;

$subtotal = 0;
foreach ($itensCarrinho as $item) {
    if (is_array($item) && isset($item['preco'], $item['qtd'])) {
        $subtotal += floatval($item['preco']) * intval($item['qtd']);
    }
}

$desconto = 0;
if ($cupomAtivo && isset($cupomAtivo['tipo'], $cupomAtivo['valor'])) {
    if ($cupomAtivo['tipo'] === 'porcentagem') {
        $desconto = ($subtotal * $cupomAtivo['valor']) / 100;
    } elseif ($cupomAtivo['tipo'] === 'valor') {
        $desconto = $cupomAtivo['valor'];
    }
}

$frete = !empty($itensCarrinho) ? 15.90 : 0;
$total = $subtotal - $desconto + $frete;

// Buscar endereços do usuário
$stmt = $pdo->prepare("SELECT * FROM tb_endereco WHERE idUsuario = :idUsuario");
$stmt->execute(array('idUsuario' => $userId));
$enderecos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Checkout | Manifesto</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" />
    <link rel="stylesheet" href="css/style.css" />
    <link rel="stylesheet" href="css/checkout.css" />
    <link rel="icon" href="img/icone.png" type="image/png" />
</head>
<body>
    <?php include_once "navbar.php" ?>

    <div class="banner">
        <h1>Finalizar Compra</h1>
    </div>

    <div class="checkout-container">

        <div class="checkout-form">

            <h2 class="checkout-title">Informações de Entrega</h2>

            <div>
            <form id="novo-endereco-form">
                <h3>Verique suas Informações de Entrega</h3>
                <div class="form-row">
                    <div class="form-group"><label for="nome">Nome Completo</label><input type="text" id="nome" name="nome" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label for="email">E-mail</label><input type="email" id="email" name="email" value="<?= htmlspecialchars(isset($_SESSION['user_email']) ? $_SESSION['user_email'] : '') ?>" readonly></div>
                    <div class="form-group"><label for="telefone">Telefone</label><input type="tel" id="telefone" name="telefone" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label for="cep">CEP</label><input type="text" id="cep" name="cep" required></div>
                    <div class="form-group"><label for="endereco">Endereço</label><input type="text" id="endereco" name="endereco" required></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label for="numero">Número</label><input type="text" id="numero" name="numero" required></div>
                    <div class="form-group"><label for="complemento">Complemento</label><input type="text" id="complemento" name="complemento"></div>
                </div>
                <div class="form-row">
                    <div class="form-group"><label for="bairro">Bairro</label><input type="text" id="bairro" name="bairro" required></div>
                    <div class="form-group"><label for="cidade">Cidade</label><input type="text" id="cidade" name="cidade" required></div>
                </div>       
            </form>
            
            <form action="profile.php" method="get" style="margin: 10px;">
                <button type="submit" class="checkout-btn">Alterar Endereço no Perfil</button>
            </form>
            </div>

            <h2 class="checkout-title">Método de Envio</h2>
            <div class="shipping-methods">
                <label class="shipping-option">
                    <input type="radio" name="shipping" value="standard" checked>
                    <div class="shipping-info">
                        <span class="shipping-name">Entrega Padrão</span>
                        <span class="shipping-price">R$ 15,90</span>
                        <span class="shipping-time">3-5 dias úteis</span>
                    </div>
                </label>
                <label class="shipping-option">
                    <input type="radio" name="shipping" value="express">
                    <div class="shipping-info">
                        <span class="shipping-name">Entrega Expressa</span>
                        <span class="shipping-price">R$ 29,90</span>
                        <span class="shipping-time">1-2 dias úteis</span>
                    </div>
                </label>
            </div>

            <h2 class="checkout-title">Método de Pagamento</h2>
            <div class="payment-methods">
                <div class="payment-tabs">
                    <button type="button" class="payment-tab" data-tab="credit">Cartão de Crédito</button>
                    <button type="button" class="payment-tab active" data-tab="pix">PIX</button>
                    <button type="button" class="payment-tab" data-tab="boleto">Boleto</button>
                </div>

                <div class="payment-content active" id="credit-tab">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="card-number">Número do Cartão</label>
                            <input type="text" id="card-number" name="card-number" placeholder="0000 0000 0000 0000" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="card-name">Nome no Cartão</label>
                            <input type="text" id="card-name" name="card-name" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="card-expiry">Validade</label>
                            <input type="text" id="card-expiry" name="card-expiry" placeholder="MM/AA" />
                        </div>
                        <div class="form-group">
                            <label for="card-cvv">CVV</label>
                            <input type="text" id="card-cvv" name="card-cvv" placeholder="000" />
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="card-installments">Parcelamento</label>
                            <select id="card-installments" name="card-installments">
                                <option value="1">1x de R$ <?= number_format($total, 2, ',', '.') ?></option>
                                <option value="2">2x de R$ <?= number_format($total / 2, 2, ',', '.') ?></option>
                                <option value="3">3x de R$ <?= number_format($total / 3, 2, ',', '.') ?></option>
                                <option value="4">4x de R$ <?= number_format($total / 4, 2, ',', '.') ?></option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="payment-content" id="pix-tab">
                    <p>Você será redirecionado para realizar o pagamento via PIX após confirmar o pedido.</p>
                    <p>O pedido será processado assim que o pagamento for confirmado.</p>
                    <div id="pix-area"></div>
                </div>

                <div class="payment-content" id="boleto-tab">
                    <p>O boleto será gerado após a confirmação do pedido e enviado para o seu e-mail.</p>
                    <p>O pedido será processado após a confirmação do pagamento, que pode levar até 3 dias úteis.</p>
                </div>
            </div>
        </div>

        <div class="checkout-summary">
            <h2 class="checkout-title">Resumo do Pedido</h2>

            <div class="summary-card">
                <div class="order-items">
                    <?php if (empty($itensCarrinho)): ?>
                        <p>Seu carrinho está vazio.</p>
                    <?php else: ?>
                        <?php foreach ($itensCarrinho as $item): ?>
                            <div class="order-item">
                                <img src="uploads/produtos/<?= htmlspecialchars(isset($item['imagem']) ? $item['imagem'] : 'default.png') ?>" alt="<?= htmlspecialchars($item['nome']) ?>" class="item-image" />
                                <div class="item-details">
                                    <h3 class="item-name"><?= htmlspecialchars($item['nome']) ?></h3>
                                    <?php if (isset($item['tamanho'])): ?>
                                        <p class="item-variant">Tamanho: <?= htmlspecialchars($item['tamanho']) ?></p>
                                    <?php endif; ?>
                                    <p class="item-price">R$ <?= number_format($item['preco'], 2, ',', '.') ?></p>
                                    <p class="item-quantity">Quantidade: <?= intval($item['qtd']) ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <div class="summary-row">
                    <span>Subtotal (<?= count($itensCarrinho) ?> <?= count($itensCarrinho) === 1 ? 'item' : 'itens' ?>)</span>
                    <span>R$ <?= number_format($subtotal, 2, ',', '.') ?></span>
                </div>
                <div class="summary-row">
                    <span>Frete</span>
                    <span>R$ <?= number_format($frete, 2, ',', '.') ?></span>
                </div>
                <div class="summary-row">
                    <span>Desconto</span>
                    <span>- R$ <?= number_format($desconto, 2, ',', '.') ?></span>
                </div>
                <div class="summary-row summary-total">
                    <span>Total</span>
                    <span>R$ <?= number_format($total, 2, ',', '.') ?></span>
                </div>
            </div>

            <button class="checkout-btn" id="confirm-order">Confirmar Pedido</button>
        </div>

    </div>

    <?php include_once "footer.php" ?>

    <script src="js/checkout.js"></script>
    <script>
    document.getElementById('confirm-order').addEventListener('click', function() {
        var email = document.getElementById('email').value;
        var total = <?= json_encode($total) ?>;
        var descricao = "Compra na loja Manifesto - <?= count($itensCarrinho) ?> item(s)";

        var metodoPagamento = 'pix'; 
        document.querySelectorAll('.payment-tab').forEach(function(btn) {
            if (btn.classList.contains('active')) {
                metodoPagamento = btn.dataset.tab; 
            }
        });

        fetch('criarPagamento.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: email,
                total: total,
                descricao: descricao,
                metodo: metodoPagamento
            })
        })
        .then(function(res) { return res.json(); })
        .then(function(dados) {
            console.log(dados);

            // Se for pagamento aprovado diretamente
            if (dados.status === 'approved') {
                alert("Pagamento aprovado!");
                return;
            }

            if (metodoPagamento === 'pix' && dados.point_of_interaction) {
                var qrCode = dados.point_of_interaction.transaction_data.qr_code_base64;
                var copiaCola = dados.point_of_interaction.transaction_data.qr_code;

                var divPix = document.createElement('div');
                divPix.innerHTML = '<h3>Pagamento via PIX</h3>' +
                    '<img src="data:image/png;base64,' + qrCode + '" alt="QR Code PIX" style="width: 200px; height: 200px; margin-bottom: 10px;">' +
                    '<p><strong>Copia e Cola:</strong><br>' + copiaCola + '</p>';
                document.querySelector('.checkout-summary').appendChild(divPix);
            } else if (metodoPagamento === 'boleto') {
                alert("Boleto gerado. Verifique seu e-mail.");
            } else if (metodoPagamento === 'credit') {
                alert("Processamento de cartão pendente de implementação.");
            } else {
                alert("Erro ao criar pagamento. Tente novamente.");
            }
        })
        .catch(function(err) {
            console.error("Erro ao criar pagamento:", err);
            alert("Erro ao processar pagamento.");
        });
    });
    </script>

</body>
</html>