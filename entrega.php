<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formas de Entrega | Nome da Loja</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/entrega.css">

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
        <h1>Formas de Entrega</h1>
    </div>

    <!-- CONTEÚDO PRINCIPAL -->
    <div class="delivery-container">
        <!-- Opções de Entrega -->
        <div class="delivery-section">
            <h2>Nossas Opções de Entrega</h2>
            <p>Oferecemos diversas formas de entrega para que você receba seus produtos com segurança e no menor prazo possível.</p>

            <div class="delivery-methods">
                <div class="method-card">
                    <div class="method-icon">
                        <i class="fas fa-shipping-fast"></i>
                    </div>
                    <h3>Entrega Expressa</h3>
                    <p>Entrega em até 2 dias úteis para capitais e regiões metropolitanas.</p>
                    <p class="price">R$ 25,90</p>
                </div>

                <div class="method-card">
                    <div class="method-icon">
                        <i class="fas fa-truck"></i>
                    </div>
                    <h3>Entrega Padrão</h3>
                    <p>Entrega em até 5 dias úteis para todo o Brasil.</p>
                    <p class="price">R$ 15,90</p>
                </div>

                <div class="method-card">
                    <div class="method-icon">
                        <i class="fas fa-store"></i>
                    </div>
                    <h3>Retirada na Loja</h3>
                    <p>Retire seu pedido em nossa loja física sem custo adicional.</p>
                    <p class="price">Grátis</p>
                </div>

                <div class="method-card">
                    <div class="method-icon">
                        <i class="fas fa-gift"></i>
                    </div>
                    <h3>Frete Grátis</h3>
                    <p>Para compras acima de R$ 200 em todo o Brasil.</p>
                    <p class="price">Grátis</p>
                </div>
            </div>
        </div>

        <!-- Processo de Entrega -->
        <div class="delivery-section">
            <h2>Como Funciona Nosso Processo de Entrega</h2>
            <p>Entenda todas as etapas desde a confirmação do seu pedido até a entrega em sua casa.</p>

            <div class="timeline">
                <div class="timeline-item">
                    <h4>Pedido Confirmado</h4>
                    <p>Após a confirmação do pagamento, seu pedido é separado em nosso estoque.</p>
                </div>

                <div class="timeline-item">
                    <h4>Embalagem</h4>
                    <p>Seus itens são cuidadosamente embalados para garantir que cheguem em perfeito estado.</p>
                </div>

                <div class="timeline-item">
                    <h4>Envio</h4>
                    <p>O pedido é despachado para nossa transportadora e você recebe o código de rastreio.</p>
                </div>

                <div class="timeline-item">
                    <h4>Entrega</h4>
                    <p>Seu pedido é entregue no endereço cadastrado dentro do prazo estimado.</p>
                </div>
            </div>
        </div>

        <!-- Acompanhamento -->
        <div class="delivery-section">
            <h2>Acompanhe Seu Pedido</h2>
            <p>Todos os pedidos são enviados com código de rastreio. Você receberá atualizações por e-mail e poderá acompanhar:</p>

            <div class="delivery-methods">
                <div class="method-card">
                    <div class="method-icon">
                        <i class="fas fa-envelope"></i>
                    </div>
                    <h3>Por E-mail</h3>
                    <p>Enviaremos atualizações em cada etapa do processo de entrega.</p>
                </div>

                <div class="method-card">
                    <div class="method-icon">
                        <i class="fas fa-mobile-alt"></i>
                    </div>
                    <h3>Por SMS</h3>
                    <p>Ative as notificações por SMS para receber alertas importantes.</p>
                </div>

                <div class="method-card">
                    <div class="method-icon">
                        <i class="fas fa-search"></i>
                    </div>
                    <h3>No Nosso Site</h3>
                    <p>Acesse "Meus Pedidos" na sua conta para ver o status atualizado.</p>
                </div>
            </div>
        </div>

        <!-- Dúvidas Frequentes -->
        <div class="delivery-section">
            <h2>Dúvidas Frequentes</h2>

            <div class="faq-item">
                <h3>Qual o prazo para postagem após a compra?</h3>
                <p>Enviamos todos os pedidos em até 2 dias úteis após a confirmação do pagamento.</p>
            </div>

            <div class="faq-item">
                <h3>Posso alterar o endereço de entrega após a compra?</h3>
                <p>Sim, desde que o pedido ainda não tenha sido enviado. Entre em contato conosco o mais rápido possível.</p>
            </div>

            <div class="faq-item">
                <h3>O que acontece se eu não estiver em casa no momento da entrega?</h3>
                <p>O entregador fará até 3 tentativas de entrega. Após isso, o pacote será devolvido e entraremos em contato.</p>
            </div>

            <div class="faq-item">
                <h3>Vocês fazem entregas internacionais?</h3>
                <p>No momento, atendemos apenas todo o território nacional.</p>
            </div>
        </div>
    </div>

    <!-- FOOTER -->

    <?php include_once "footer.php" ?>

    <script src="js/entrega.js"></script>
</body>

</html>