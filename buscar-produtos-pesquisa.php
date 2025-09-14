<?php
require_once 'conexao.php';

$termo = isset($_GET['termo']) ? $_GET['termo'] : '';
$termo = trim($termo);

if ($termo === '') {
  exit;
}

$stmt = $pdo->prepare("
  SELECT 
    p.id,
    p.nomeItem,
    p.valorItem,
    (
      SELECT nomeImagem
      FROM tb_imagemProduto
      WHERE idProduto = p.id AND statusImagem = 'principal'
      ORDER BY idImagem ASC
      LIMIT 1
    ) AS nomeImagem
  FROM tb_produto p
  WHERE p.nomeItem LIKE :termo
  ORDER BY p.nomeItem ASC
");
$stmt->execute(array(':termo' => "%$termo%"));
$produtos = $stmt->fetchAll();

foreach ($produtos as $prod) {
  $nomeImagem = isset($prod['nomeImagem']) ? $prod['nomeImagem'] : 'default.png';
  $imgSrc = 'uploads/produtos/' . htmlspecialchars($nomeImagem);
  echo '
  <div class="card-produto-pesquisa" data-id="' . htmlspecialchars($prod['id']) . '" data-nome="' . htmlspecialchars($prod['nomeItem']) . '">
    <div class="pesquisa-produto-imagem">
      <img src="' . $imgSrc . '" alt="' . htmlspecialchars($prod['nomeItem']) . '">
    </div>
    <div class="pesquisa-produto-content">
      <h3>' . htmlspecialchars($prod['nomeItem']) . '</h3>
      <span class="price">R$ ' . number_format($prod['valorItem'], 2, ',', '.') . '</span>
    </div>
    <a href="detalhes-produto.php?id=' . htmlspecialchars($prod['id']) . '" class="btn-comprar-pesquisa">Comprar</a>
  </div>';
}