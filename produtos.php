<?php
require_once "conexao.php";
session_start();

// Buscar categorias
$categorias = $pdo->query("SELECT * FROM tb_categoria")->fetchAll(PDO::FETCH_ASSOC);

// Criar mapeamento idCategoria => slug
$slugPorIdCategoria = [];
foreach ($categorias as $categoria) {
    $slug = strtolower(preg_replace('/\s+/', '-', $categoria['ctgNome']));
    $slugPorIdCategoria[$categoria['id']] = $slug;
}

// Buscar produtos e associar às categorias
$stmt = $pdo->prepare("
  SELECT p.id, p.nomeItem, p.valorItem, p.idCategoria, i.nomeImagem
  FROM tb_produto p
  LEFT JOIN tb_imagemProduto i ON i.idProduto = p.id AND i.statusImagem = 'principal'
  ORDER BY p.nomeItem
");
$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);


$produtosPorCategoria = [];
foreach ($produtos as $produto) {
    $idCat = $produto['idCategoria'];
    $slug = isset($slugPorIdCategoria[$idCat]) ? $slugPorIdCategoria[$idCat] : 'sem-categoria';
    $produtosPorCategoria[$slug][] = $produto;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Produtos - Manifesto</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/roupas.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="icon" href="img/icone.png" type="image/png">
</head>

<body>
  <?php include_once "navbar.php" ?>

  <div class="resultados-produtos" style="display: none;">
        <p class="sem-resultados" style="display: none; color: #888; padding: 1rem;">Nenhum produto encontrado.</p>
        <div id="lista-produtos"></div>
  </div>

  <div class="banner">
    <h1>Produtos</h1>
  </div>

  <!-- Filtros -->
  <div class="filtros-container">
    <div class="filtros">
      <div class="categorias-filtro">
        <button class="categoria-btn active" data-categoria="todos">Todos</button>
        <?php foreach ($categorias as $categoria): ?>
          <?php $slug = strtolower(preg_replace('/\s+/', '-', $categoria['ctgNome'])); ?>
          <button class="categoria-btn" data-categoria="<?= $slug ?>">
            <?= htmlspecialchars($categoria['ctgNome']) ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Produtos -->
  <div class="produtos-container">
    <?php foreach ($categorias as $categoria): ?>
      <?php
        $slug = strtolower(preg_replace('/\s+/', '-', $categoria['ctgNome']));
        $produtosDaCategoria = isset($produtosPorCategoria[$slug]) ? $produtosPorCategoria[$slug] : [];
        ?>
        <section class="categoria-section" id="<?= $slug ?>">
          <h2><?= htmlspecialchars($categoria['ctgNome']) ?></h2>
          <div class="produto-lista">
            <?php if (empty($produtosDaCategoria)): ?>
              <p>Nenhum produto disponível nesta categoria.</p>
            <?php else: ?>
              <?php foreach ($produtosDaCategoria as $produto): ?>
                <?php
                $nomeImagem = isset($produto['nomeImagem']) ? $produto['nomeImagem'] : 'default.png';
                ?>
                <div class="produto-card">
                  <img src="uploads/produtos/<?= htmlspecialchars($nomeImagem) ?>" alt="<?= htmlspecialchars($produto['nomeItem']) ?>" />
                  <h3><?= htmlspecialchars($produto['nomeItem']) ?></h3>
                  <p class="preco">R$ <?= number_format($produto['valorItem'], 2, ',', '.') ?></p>
                  <a href="detalhes-produto.php?id=<?= $produto['id'] ?>" class="btn-comprar">Comprar</a>
                </div>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>
        </section>
    <?php endforeach; ?>
  </div>

  <?php include_once "footer.php" ?>

  <button id="back-to-top" class="back-to-top" aria-label="Voltar ao topo">
    <i class="fas fa-arrow-up"></i>
  </button>

  <script src="js/script.js"></script>
  <script src="js/roupas.js"></script>
</body>
</html>
