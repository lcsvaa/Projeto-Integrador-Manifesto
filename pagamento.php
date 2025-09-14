<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formas de Pagamento | Nome da Loja</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">


    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/pagamento.css">

    <link rel="icon" href="img/icone.png" type="image/png">

</head>

<body>
    <!-- NAVBAR -->

    <?php include_once "navbar.php" ?>

    <div class="resultados-produtos" style="display: none;">
        <p class="sem-resultados" style="display: none; color: #888; padding: 1rem;">Nenhum produto encontrado.</p>
        <div id="lista-produtos"></div>
    </div>

    <!-- BANNER -->
    <div class="banner">
        <h1>Formas de Pagamento</h1>
    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="payment-container">
        <!-- Cartões de Crédito -->
        <div class="payment-section">
            <h2>Cartões de Crédito</h2>
            <p>Aceitamos todas as bandeiras principais. Parcele suas compras em até 12x sem juros*.</p>

            <div class="payment-methods">
                <div class="method-card">
                    <div class="method-icon">
                        <i class="fab fa-cc-visa"></i>
                    </div>
                    <h3>Visa</h3>
                    <p>Até 12x sem juros*</p>
                </div>

                <div class="method-card">
                    <div class="method-icon">
                        <i class="fab fa-cc-mastercard"></i>
                    </div>
                    <h3>Mastercard</h3>
                    <p>Até 12x sem juros*</p>
                </div>

                <div class="method-card">
                    <div class="method-icon">
                        <i class="fab fa-cc-amex"></i>
                    </div>
                    <h3>American Express</h3>
                    <p>Até 10x sem juros*</p>
                </div>

                <div class="method-card">
                    <div class="method-icon">
                        <i class="fab fa-cc-diners-club"></i>
                    </div>
                    <h3>Diners Club</h3>
                    <p>Até 6x sem juros*</p>
                </div>
            </div>

            <div class="installments-info">
                <p>* Parcelamento sem juros válido para compras acima de R$ 200,00. Para valores menores ou
                    parcelamentos maiores, podem ser cobrados juros conforme política do emissor do cartão.</p>
            </div>
        </div>

        <!-- Outras Formas de Pagamento -->
        <div class="payment-section">
            <h2>Outras Formas de Pagamento</h2>
            <p>Oferecemos diversas opções para facilitar sua compra.</p>

            <div class="payment-methods">
                <div class="method-card">
                    <div class="method-icon">
                        <i class="fas fa-barcode"></i>
                    </div>
                    <h3>Boleto Bancário</h3>
                    <p>Pagamento à vista com 5% de desconto</p>
                </div>

                <div class="method-card">
                    <div class="method-icon">
                        <i class="fas fa-money-bill-wave"></i>
                    </div>
                    <h3>PIX</h3>
                    <p>Pagamento instantâneo com 5% de desconto</p>
                </div>

                <div class="method-card">
                    <div class="method-icon">
                        <i class="fab fa-paypal"></i>
                    </div>
                    <h3>PayPal</h3>
                    <p>Pagamento em até 6x no cartão</p>
                </div>

            </div>
        </div>

        <!-- Segurança -->
        <div class="payment-section">
            <h2>Segurança nas Transações</h2>
            <p>Todas as suas transações são protegidas com os mais altos padrões de segurança.</p>

            <div class="security-info">
                <img src="https://cdn-icons-png.flaticon.com/512/559/559353.png" alt="Selos de Segurança" class="security-badge">
                <div class="security-text">
                    <p><strong>Compra 100% segura:</strong> Utilizamos criptografia SSL de 256 bits para proteger todos
                        os seus dados durante a transação. Nossa loja é certificada pelas principais instituições de
                        segurança de pagamentos online.</p>
                    <p>Trabalhamos com os maiores gateways de pagamento do mercado para garantir a segurança das suas
                        informações financeiras.</p>
                </div>
            </div>
        </div>

        <!-- Dúvidas Frequentes -->
        <div class="payment-section">
            <h2>Dúvidas Frequentes</h2>

            <div class="faq-item">
                <h3>Posso parcelar no boleto bancário?</h3>
                <p>Não, o boleto bancário é sempre à vista. Oferecemos 5% de desconto para pagamentos via boleto.</p>
            </div>

            <div class="faq-item">
                <h3>Como funciona o desconto no PIX?</h3>
                <p>O desconto de 5% no PIX é aplicado automaticamente no checkout, antes da confirmação do pagamento.
                </p>
            </div>

            <div class="faq-item">
                <h3>Qual o prazo para confirmação de pagamento?</h3>
                <p>Cartões de crédito e PIX são confirmados em até 1 hora. Boletos bancários podem levar até 2 dias
                    úteis.</p>
            </div>
        </div>
    </div>

    <!-- FOOTER -->

    <?php include_once "footer.php" ?>


    <script src="js/pagamento.js"></script>
</body>

</html>