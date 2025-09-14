<?php
require_once "conexao.php";
session_start();

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    echo "Produto inválido.";
    exit;
}

// Buscar dados do produto com imagem principal
$stmt = $pdo->prepare("
    SELECT p.*, c.ctgNome, co.colecaoNome, ip.nomeImagem
    FROM tb_produto p
    LEFT JOIN tb_imagemProduto ip ON ip.idProduto = p.id AND ip.statusImagem = 'principal'
    LEFT JOIN tb_categoria c ON p.idCategoria = c.id
    LEFT JOIN tb_colecao co ON p.idColecao = co.id
    WHERE p.id = :id
");
$stmt->bindParam(':id', $id, PDO::PARAM_INT);
$stmt->execute();
$produto = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$produto) {
    echo "Produto não encontrado.";
    exit;
}

// Buscar imagens do produto para a galeria
$stmtImgs = $pdo->prepare("
    SELECT nomeImagem 
    FROM tb_imagemProduto 
    WHERE idProduto = :idProduto 
    ORDER BY statusImagem DESC
");
$stmtImgs->bindParam(':idProduto', $id, PDO::PARAM_INT);
$stmtImgs->execute();
$imagens = $stmtImgs->fetchAll(PDO::FETCH_ASSOC);

// Verificar se há controle por tamanho
$stmtTamanhos = $pdo->prepare("
    SELECT tamanho, estoque 
    FROM tb_produto_tamanho 
    WHERE idProduto = :idProduto
");
$stmtTamanhos->bindParam(':idProduto', $id, PDO::PARAM_INT);
$stmtTamanhos->execute();
$tamanhos = $stmtTamanhos->fetchAll(PDO::FETCH_ASSOC);

$estoquePorTamanho = array();
foreach ($tamanhos as $linha) {
    $estoquePorTamanho[$linha['tamanho']] = (int)$linha['estoque'];
}
$temTamanho = count($estoquePorTamanho) > 0;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1" />
  <title>Detalhes do Produto - <?php echo htmlspecialchars($produto['nomeItem']); ?></title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="css/style.css" />
  <link rel="stylesheet" href="css/notificacao.css" />
  <link rel="stylesheet" href="css/detalhes-produto.css" />
</head>
<body>

<?php include_once "navbar.php" ?>

<div class="container">
  <div class="product-detail">
    <!-- Galeria de Imagens -->
    <div class="product-gallery">
      <?php $imagemPrincipal = isset($imagens[0]['nomeImagem']) ? $imagens[0]['nomeImagem'] : ''; ?>
      <?php if ($imagemPrincipal): ?>
        <img src="uploads/produtos/<?php echo htmlspecialchars($imagemPrincipal); ?>" alt="<?php echo htmlspecialchars($produto['nomeItem']); ?>" class="main-image" id="mainImage">
      <?php else: ?>
        <p>Imagem não disponível</p>
      <?php endif; ?>

      <div class="thumbnail-container">
        <?php foreach ($imagens as $index => $img): ?>
          <img 
            src="uploads/produtos/<?php echo htmlspecialchars($img['nomeImagem']); ?>" 
            alt="Miniatura <?php echo $index + 1; ?>" 
            class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>" 
            onclick="changeImage(this, 'uploads/produtos/<?php echo htmlspecialchars($img['nomeImagem']); ?>')"
          >
        <?php endforeach; ?>
      </div>
    </div>

    <!-- Informações do Produto -->
    <div class="product-info">
      <h1 class="product-title"><?php echo htmlspecialchars($produto['nomeItem']); ?></h1>
      <div class="product-price">R$ <?php echo number_format($produto['valorItem'], 2, ',', '.'); ?></div>

      <?php if ($temTamanho): ?>
        <div class="option-group">
          <span class="option-title">Tamanho:</span>
          <div class="size-options">
            <?php foreach (array('P', 'M', 'G', 'GG') as $t): ?>
              <?php if (!empty($estoquePorTamanho[$t])): ?>
                <div class="size-btn<?php echo empty($selected) ? ' selected' : ''; ?>" data-tamanho="<?php echo $t; ?>" data-estoque="<?php echo $estoquePorTamanho[$t]; ?>">
                  <?php echo $t; ?>
                </div>
                <?php $selected = true; ?>
              <?php endif; ?>
            <?php endforeach; ?>
          </div>
        </div>
      <?php endif; ?>

      <div class="quantity-control">
        <span class="option-title">Quantidade:</span>
        <button class="qty-btn" onclick="changeQuantity(-1)">-</button>
        <input type="number" value="1" min="1" class="qty-input" id="quantity" max="<?php echo $temTamanho ? 1 : intval($produto['estoqueItem']); ?>">
        <button class="qty-btn" onclick="changeQuantity(1)">+</button>
      </div>

      <p id="estoque-baixo-msg" class="estoque-baixo-msg" style="color: red; display: none; font-weight: bold;">
        Estoque baixo! Restam poucas unidades.
      </p>

      <button class="add-to-cart" id="btnAdd" data-id="<?php echo $produto['id']; ?>">
        <i class="fas fa-shopping-cart"></i> Adicionar ao Carrinho
      </button>

      <div class="product-description">
        <h3>Descrição do Produto</h3>
        <p><?php echo nl2br(htmlspecialchars($produto['descItem'])); ?></p>
      </div>
    </div>
  </div>
</div>

<?php include_once "footer.php" ?>

<script src="js/detalhes-produto.js"></script>

</body>
</html>

