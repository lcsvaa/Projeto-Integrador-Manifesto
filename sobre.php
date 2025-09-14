<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Sobre Nós</title>
  <link rel="stylesheet" href="css/style.css" />
  <!-- Estilo da pagina sobre nos -->
  <link rel="stylesheet" href="css/sobre.css">

  <!-- Fontes do Google -->
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&family=Quicksand:wght@400;600&display=swap"
    rel="stylesheet">

  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- Icone -->
  <link rel="icon" href="img/icone.png" type="image/png">
</head>

<body>
  <!-- NAVBAR -->
  
  <?php include_once "navbar.php" ?>

  <div class="resultados-produtos" style="display: none;">
    <p class="sem-resultados" style="display: none; color: #888; padding: 1rem;">Nenhum produto encontrado.</p>
    <div id="lista-produtos"></div>
  </div>

  <!-- Seção Hero Sobre Nós -->
  <header class="sobre-header">

  <div class="banner">
        <h1>Sobre Nós</h1>
    </div>
  </header>

  <!-- Seção de Identidade Visual (layout modificado) -->
  <section class="visual-identity">
    <div class="container">
      <!-- Linha com logo e mascote lado a lado -->
      <div class="identity-row">
        <div class="identity-item">
          <img src="img/logo.png " alt="Logo da Loja">
          <h3>Nosso Logo</h3>
          <p>Mais do que uma marca, somos uma atitude.
            Manifesto vem de “manifestar” — nosso estilo, nossas ideias, nosso jeito de ser. O (011) é a raiz, o código
            da Grande São Paulo, onde nascemos e nos conectamos com o movimento urbano e criativo da cidade. E as quatro
            estrelas? Um tributo à bandeira de São Paulo e ao orgulho de representar nosso estado com autenticidade.</p>
        </div>

        <div class="identity-item">
          <img src="img/mascote.png" alt="Mascote da Loja">
          <h3>Nosso Mascote</h3>
          <p>Cheia de atitude, estilo e criatividade, ela é a personificação da nossa energia. Com visual marcante e
            espírito inquieto, representa tudo o que acreditamos: autenticidade, movimento e paixão pelo que fazemos.
          </p>
        </div>
      </div>

      <!-- Linha com a fachada da loja (mais larga) -->
      <div class="identity-item-wide">
        <img src="img/loja.png" alt="Fachada da Loja">
        <h3>Nossa Loja Física</h3>
        <p>No coração da cidade, uma extensão viva da nossa essência. A loja Manifesto é mais do que ponto de venda — é
          ponto de encontro. Um espaço onde estilo, atitude e energia urbana se cruzam. Vem sentir de perto o que a
          gente acredita: vestir é manifestar quem você é.</p>
      </div>
    </div>
  </section>

  <!-- Seção Nossa História -->
  <section class="nossa-historia">
    <div class="container">
      <div>
        <h2>Nossa História</h2>
        <p>Fundada em 2010, nossa loja começou como um pequeno empreendimento familiar com a missão de trazer os
          melhores produtos para nossos clientes.</p>
        <p>Com o tempo, crescemos e nos tornamos referência no mercado, sem nunca perder nosso compromisso com a
          qualidade e atendimento personalizado.</p>
        <p>Hoje, temos orgulho de servir milhares de clientes satisfeitos em todo o país, tanto em nossa loja física
          quanto através do nosso e-commerce.</p>
      </div>
      <img src="img/nos.jpeg" alt="Nossa Equipe">
    </div>
  </section>

  <!-- Footer -->
  <?php include_once "footer.php" ?>

  <!-- Scripts -->
  <script src="js/sobre.js"></script>
</body>

</html>