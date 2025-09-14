<?php
require_once 'conexao.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Manifesto</title>
  <link rel="stylesheet" href="css/style.css" />

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


  <!-- CARROSSEL -->
<section class="carousel-section">
  <div class="carousel-wrapper">
    <div class="carousel" id="carousel">
      <?php
        require_once 'conexao.php';

        $imagens = $pdo->query("SELECT * FROM tb_imagem WHERE statusImagem IN ('ativa', 'principal') ORDER BY statusImagem DESC");

        foreach ($imagens as $img) {
          echo '<div class="carousel-item">';
          echo '<img src="uploads/carrossel/' . htmlspecialchars($img['nomeImagem']) . '" alt="Imagem do carrossel" />';
          echo '<button class="buy-now"><a href='. htmlspecialchars($img['linkImagem']) .'>Ver Mais</a></button>';
          echo '</div>';
        }
      ?>
    </div>
    <div class="carousel-controls">
      <button id="prev-button" aria-label="Slide anterior"><i class="fa fa-chevron-left"></i></button>
      <button id="next-button" aria-label="Próximo slide"><i class="fa fa-chevron-right"></i></button>
    </div>
  </div>
</section>

  <!-- LANÇAMENTOS -->
<section class="lancamentos">
  <div class="container">
    <h2>Lançamentos</h2>
    <div class="produto-lista">
      <?php

      $sql = "SELECT p.id, p.nomeItem, p.valorItem, i.nomeImagem 
              FROM tb_produto p 
              LEFT JOIN tb_imagemProduto i ON i.idProduto = p.id AND i.statusImagem = 'principal'
              ORDER BY p.id DESC
              LIMIT 6";

      $stmt = $pdo->query($sql);

      while ($produto = $stmt->fetch(PDO::FETCH_ASSOC)) {
          $nome = htmlspecialchars($produto['nomeItem']);
          $preco = number_format($produto['valorItem'], 2, ',', '.');
          $img = isset($produto['nomeImagem']) && $produto['nomeImagem'] !== '' 
                 ? "uploads/produtos/" . $produto['nomeImagem'] 
                 : "img/default-product.png";

          echo '<div class="produto-card">';
          echo '<img src="' . $img . '" alt="' . $nome . '" />';
          echo '<h3>' . $nome . '</h3>';
          echo '<p class="preco">R$ ' . $preco . '</p>';
          echo '<a href="detalhes-produto.php?id=' . $produto['id'] . '" class="btn-comprar">Comprar</a>';
          echo '</div>';
      }
      ?>
    </div>
  </div>
</section>

  <!-- COLEÇÃO -->
  <?php

    // Buscar todas as coleções com seus produtos
    $sql = "
      SELECT 
        co.id AS idColecao,
        co.colecaoNome,
        p.id AS idProduto,
        p.nomeItem,
        p.valorItem,
        ip.nomeImagem
      FROM tb_colecao co
      JOIN tb_produto p ON p.idColecao = co.id
      LEFT JOIN tb_imagemProduto ip ON ip.idProduto = p.id AND ip.statusImagem = 'principal'
      ORDER BY co.colecaoNome, p.nomeItem
    ";

    $stmt = $pdo->query($sql);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Agrupar produtos por coleção
    $colecoes = array();
    foreach ($resultados as $row) {
        $colecoes[$row['colecaoNome']][] = $row;
    }
  ?>

  <?php foreach ($colecoes as $nomeColecao => $produtos): ?>
    <section class="colecao">
      <div class="container">
        <h2><?php echo htmlspecialchars($nomeColecao); ?></h2>
        <div class="produto-lista">
          <?php foreach ($produtos as $produto): ?>
            <div class="produto-card">
              <img src="uploads/produtos/<?php echo isset($produto['nomeImagem']) && $produto['nomeImagem'] !== '' ? htmlspecialchars($produto['nomeImagem']) : 'padrao.jpg'; ?>" alt="<?php echo htmlspecialchars($produto['nomeItem']); ?>" />
              <h3><?php echo htmlspecialchars($produto['nomeItem']); ?></h3>
              <p class="preco">R$ <?php echo number_format($produto['valorItem'], 2, ',', '.'); ?></p>
              <a href="detalhes-produto.php?id=<?php echo $produto['idProduto']; ?>" class="btn-comprar">Comprar</a>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
    </section>
  <?php endforeach; ?>

  <!-- FOOTER -->
  <?php include_once "footer.php" ?>

  <!-- JavaScript -->
  <script src="js/script.js"></script>

</body>

</html>