<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Novidades | Manifesto</title>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/card-novidades.css">
    <link rel="stylesheet" href="css/novidades.css">
    <link rel="icon" href="img/icone.png" type="image/png">
    <style>
        /* Estilos para os modais */
        .blog-modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: auto;
            background-color: rgba(0,0,0,0.8);
            backdrop-filter: blur(5px);
        }

        .modal-content {
            background-color: #111111;
            margin: 5% auto;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.2);
            width: 90%;
            max-width: 800px;
            position: relative;
            animation: modalopen 0.4s;
        }

        @keyframes modalopen {
            from {opacity: 0; transform: translateY(-50px);}
            to {opacity: 1; transform: translateY(0);}
        }

        .close-modal {
            color: #ffffff;
            font-size: 28px;
            font-weight: bold;
            position: absolute;
            right: 20px;
            top: 10px;
            cursor: pointer;
        }

        .close-modal:hover {
            color: #cd1b5d;
        }

        .modal-image {
            width: 100%;
            max-height: 400px;
            object-fit: cover;
            border-radius: 4px;
            margin-bottom: 20px;
        }

        .modal-date {
            color: #777;
            font-size: 14px;
            margin-bottom: 10px;
        }

        .modal-title {
            font-size: 24px;
            margin-bottom: 20px;
            color: #cd1b5d;
        }

        .modal-text {
            line-height: 1.6;
            color: #ffffff;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .modal-content {
                margin: 10% auto;
                width: 95%;
                padding: 20px;
            }
        }
    </style>
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
        <h1>Novidades</h1>
    </div>

    <div id="card-novidades-ryan"> 

            <div id="novidades-grid"></div>
            
    </div>

    <!-- BLOG SECTION -->
    <div class="container">
        <div class="blog-container">
            <!-- Post 1 -->
            <!-- <div class="blog-card">
                <img src="https://i.pinimg.com/736x/8e/cf/2d/8ecf2d139e5a08d19ea8a4767b23c165.jpg" alt="Nova Coleção Outono/Inverno">
                <div class="blog-content">
                    <div class="blog-date">15 MARÇO 2023</div>
                    <h3 class="blog-title">Conheça nossa nova coleção Outono/Inverno</h3>
                    <p class="blog-excerpt">Descubra as peças exclusivas que preparamos para a estação mais fria do ano, com materiais premium e designs inovadores.</p>
                    <a href="#" class="read-more" data-modal="modal1">Ler mais →</a>
                </div>
            </div> -->

            <!-- Post 2 -->
            <!-- <div class="blog-card">
                <img src="https://i.pinimg.com/736x/74/e9/7c/74e97c55ce3a690b335c220f28f751b9.jpg" alt="Colaboração com Artista Local">
                <div class="blog-content">
                    <div class="blog-date">28 FEVEREIRO 2023</div>
                    <h3 class="blog-title">Colaboração exclusiva com artista urbano</h3>
                    <p class="blog-excerpt">Nossa parceria com o renomado artista @streetartguy resulta em peças únicas com estampas limitadas.</p>
                    <a href="#" class="read-more" data-modal="modal2">Ler mais →</a>
                </div>
            </div> -->

            <!-- Post 3 -->
            <!-- <div class="blog-card">
                <img src="img/loja.png" alt="Evento de Lançamento">
                <div class="blog-content">
                    <div class="blog-date">10 FEVEREIRO 2023</div>
                    <h3 class="blog-title">Evento VIP de lançamento na galeria urbana</h3>
                    <p class="blog-excerpt">Confira como foi o nosso evento exclusivo para clientes selecionados, com desfile e drinks especiais.</p>
                    <a href="#" class="read-more" data-modal="modal3">Ler mais →</a>
                </div>
            </div> -->
        </div>

        <!-- NEWSLETTER SECTION -->
        <div class="newsletter-section">
            <h2 class="newsletter-title">Assine nossa newsletter</h2>
            <p class="newsletter-desc">Receba em primeira mão informações sobre novos lançamentos, coleções exclusivas e eventos especiais.</p>
            <form class="newsletter-form" id="newsletter-form" action="newsletter/enviar-newsletter.php" method="POST">
                <input type="email" name="email" class="newsletter-input" placeholder="Seu melhor e‑mail" required>
                <button type="submit" class="newsletter-btn">Assinar</button>
            </form>
            <p id="newsletter-feedback" class="newsletter-feedback"></p>
        </div>
    </div>

    <!-- Modais dos posts -->
    <!-- Modal 1 -->
    <div id="modal1" class="blog-modal">
        <div class="modal-content">
            <span class="close-modal" >&times;</span>
    
            
            <img src="https://i.pinimg.com/736x/8e/cf/2d/8ecf2d139e5a08d19ea8a4767b23c165.jpg" alt="Nova Coleção Outono/Inverno" class="modal-image">
            <div class="modal-date">15 MARÇO 2023</div>
            <h3 class="modal-title">Conheça nossa nova coleção Outono/Inverno</h3>
            <div class="modal-text">
                <p>A coleção Outono/Inverno 2023 da Manifesto traz um conceito inovador que mistura conforto e estilo urbano. Nossas peças foram cuidadosamente desenvolvidas com materiais premium como lã merino, algodão orgânico e couro sustentável.</p>
                <p>Destaques da coleção:</p>
                <ul>
                    <li>Casacos com tecnologia térmica</li>
                    <li>Jaquetas em couro vegetal</li>
                    <li>Camisas com estampas exclusivas</li>
                    <li>Calças com cortes modernos</li>
                </ul>
                <p>Todas as peças são produzidas de forma sustentável, mantendo nosso compromisso com o meio ambiente. A coleção já está disponível em nossas lojas físicas e no e-commerce.</p>
            </div>
        </div>
    </div>

    <!-- Modal 2 -->
    <div id="modal2" class="blog-modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <img src="https://i.pinimg.com/736x/74/e9/7c/74e97c55ce3a690b335c220f28f751b9.jpg" alt="Colaboração com Artista Local" class="modal-image">
            <div class="modal-date">28 FEVEREIRO 2023</div>
            <h3 class="modal-title">Colaboração exclusiva com artista urbano</h3>
            <div class="modal-text">
                <p>Estamos orgulhosos de anunciar nossa parceria com o renomado artista urbano @streetartguy, que resultou em uma coleção cápsula exclusiva com estampas limitadas.</p>
                <p>A coleção apresenta:</p>
                <ul>
                    <li>10 estampas exclusivas criadas pelo artista</li>
                    <li>Peças numeradas e assinadas</li>
                    <li>Técnicas de impressão sustentáveis</li>
                    <li>Edição limitada a 100 unidades de cada peça</li>
                </ul>
                <p>O artista transformou nossos clássicos em telas urbanas, com grafites que retratam a essência da cultura das ruas. Parte da renda será revertida para projetos de arte comunitária.</p>
                <p>A coleção estará disponível a partir do dia 10/03 em nosso flagship store.</p>
            </div>
        </div>
    </div>

    <!-- Modal 3 -->
    <div id="modal3" class="blog-modal">
        <div class="modal-content">
            <span class="close-modal">&times;</span>
            <img src="img/loja.png" alt="Evento de Lançamento" class="modal-image">
            <div class="modal-date">10 FEVEREIRO 2023</div>
            <h3 class="modal-title">Evento VIP de lançamento na galeria urbana</h3>
            <div class="modal-text">
                <p>No último sábado, realizamos um evento exclusivo para lançar nossa nova coleção na Galeria Urbana, espaço cultural no coração da cidade. O evento reuniu clientes selecionados, influenciadores e parceiros da marca.</p>
                <p>Confira os melhores momentos:</p>
                <ul>
                    <li>Desfile com modelos locais</li>
                    <li>Bar com drinks temáticos criados pelo mixologista João Silva</li>
                    <li>DJ set com batidas exclusivas</li>
                    <li>Área interativa com realidade aumentada</li>
                </ul>
                <p>Os convidados foram os primeiros a conferir as peças da nova coleção e puderam adquirir itens com 20% de desconto exclusivo para o evento.</p>
                <p>"Foi incrível ver nossa comunidade reunida celebrando a moda e a arte urbana", disse Maria Souza, nossa diretora criativa.</p>
                <p>Fique de olho em nossas redes sociais para os próximos eventos!</p>
            </div>
        </div>
    </div>

    <!-- FOOTER -->
    <?php include_once "footer.php" ?>

    <script>
        // Controle dos modais
        document.addEventListener('DOMContentLoaded', function() {
            // Abrir modal
            const readMoreLinks = document.querySelectorAll('.read-more');
            readMoreLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    e.preventDefault();
                    const modalId = this.getAttribute('data-modal');
                    const modal = document.getElementById(modalId);
                    modal.style.display = 'block';
                    document.body.style.overflow = 'hidden';
                });
            });

            // Fechar modal
            const closeButtons = document.querySelectorAll('.close-modal');
            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const modal = this.closest('.blog-modal');
                    modal.style.display = 'none';
                    document.body.style.overflow = 'auto';
                });
            });

            // Fechar ao clicar fora
            const modals = document.querySelectorAll('.blog-modal');
            modals.forEach(modal => {
                modal.addEventListener('click', function(e) {
                    if (e.target === this) {
                        this.style.display = 'none';
                        document.body.style.overflow = 'auto';
                    }
                });
            });

            // Fechar com ESC
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    modals.forEach(modal => {
                        if (modal.style.display === 'block') {
                            modal.style.display = 'none';
                            document.body.style.overflow = 'auto';
                        }
                    });
                }
            });
        });
    </script>
    <script src="js/novidades-public.js" type="module"></script>
    <script src="js/newsletter-cadastro.js" type="module"></script>
</body>
</html>