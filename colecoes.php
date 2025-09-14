<?php
require_once "conexao.php";
session_start();

$colecoes = $pdo->query("SELECT * FROM tb_colecao")->fetchAll(PDO::FETCH_ASSOC);

$slugPorIdColecoes = []; // Corrigido o nome da variável
foreach ($colecoes as $colecao) {
    $slug = strtolower(preg_replace('/\s+/', '-', $colecao['colecaoNome']));
    $slugPorIdColecoes[$colecao['id']] = $slug;
}

$stmt = $pdo->prepare("
  SELECT p.id, p.nomeItem, p.valorItem, p.idColecao, i.nomeImagem
  FROM tb_produto p
  LEFT JOIN tb_imagemProduto i ON i.idProduto = p.id AND i.statusImagem = 'principal'
  ORDER BY p.nomeItem
");

$stmt->execute();
$produtos = $stmt->fetchAll(PDO::FETCH_ASSOC);

$produtosPorColecoes = [];
foreach ($produtos as $produto) {
    $idCol = $produto['idColecao'];
    $slug = isset($slugPorIdColecoes[$idCol]) ? $slugPorIdColecoes[$idCol] : 'sem-colecao';
    $produtosPorColecoes[$slug][] = $produto;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Coleções - Manifesto</title>
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/roupas.css" />
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@400;700&family=Quicksand:wght@400;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />
  <link rel="icon" href="img/icone.png" type="image/png">
</head>

<body>
  <?php include_once "navbar.php"; ?>

  <div class="resultados-colecao" style="display: none;">
    <p class="sem-resultados" style="display: none; color: #888; padding: 1rem;">Nenhum produto encontrado.</p>
    <div id="lista-colecao"></div>
  </div>

  <div class="banner">
    <h1>Coleções</h1>
  </div>

  <!-- Filtros -->
  <div class="filtros-container">
    <div class="filtros">
      <div class="colecoes-filtro">
        <button class="categoria-btn active" data-categoria="todos">Todos</button>
        <?php foreach ($colecoes as $colecao): ?>
          <?php $slug = strtolower(preg_replace('/\s+/', '-', $colecao['colecaoNome'])); ?>
          <button class="categoria-btn" data-categoria="<?php echo $slug; ?>">
            <?php echo htmlspecialchars($colecao['colecaoNome']); ?>
          </button>
        <?php endforeach; ?>
      </div>
    </div>
  </div>

  <!-- Produtos -->
  <div class="produtos-container">
    <?php foreach ($colecoes as $colecao): ?>
      <?php
        $slug = strtolower(preg_replace('/\s+/', '-', $colecao['colecaoNome']));
        $produtosDaColecao = isset($produtosPorColecoes[$slug]) ? $produtosPorColecoes[$slug] : [];
      ?>
      <section class="categoria-section" id="<?php echo $slug; ?>">
        <h2><?php echo htmlspecialchars($colecao['colecaoNome']); ?></h2>
        <div class="produto-lista">
          <?php if (empty($produtosDaColecao)): ?>
            <p>Nenhum produto disponível nesta categoria.</p>
          <?php else: ?>
            <?php foreach ($produtosDaColecao as $produto): ?>
              <div class="produto-card" data-category="<?php echo $slug; ?>">
                <?php
                  $nomeImagem = isset($produto['nomeImagem']) ? $produto['nomeImagem'] : 'default.png';
                ?>
                <img src="uploads/produtos/<?php echo htmlspecialchars($nomeImagem); ?>" alt="<?php echo htmlspecialchars($produto['nomeItem']); ?>" />
                <h3><?php echo htmlspecialchars($produto['nomeItem']); ?></h3>
                <p class="preco">R$ <?php echo number_format($produto['valorItem'], 2, ',', '.'); ?></p>
                <a href="detalhes-produto.php?id=<?php echo $produto['id']; ?>" class="btn-comprar">Comprar</a>
              </div>
            <?php endforeach; ?>
          <?php endif; ?>
        </div>
      </section>
    <?php endforeach; ?>
  </div>

  <?php include_once "footer.php"; ?>

  <button id="back-to-top" class="back-to-top" aria-label="Voltar ao topo">
    <i class="fas fa-arrow-up"></i>
  </button>

  <script src="js/script.js"></script>
  <script src="js/roupas.js"></script>
</body>
</html>
