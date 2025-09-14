<?php
require_once 'conexao.php';

// Consulta modificada para remover a condição WHERE idProduto IS NULL
$imagens = $pdo->query("SELECT * FROM tb_imagem ORDER BY statusImagem DESC");

foreach ($imagens as $img) {
  echo '
  <div class="item-card" data-id="'.$img['idImagem'].'">
    <img src="uploads/carrossel/'.$img['nomeImagem'].'" alt="Imagem do carrossel">
    <div class="item-info">
      <span class="status-badge '.$img['statusImagem'].'">'.$img['statusImagem'].'</span>
    </div>
    <div class="item-actions">
      <button class="btn-action editar" data-id="'.$img['idImagem'].'">
        <i class="fas fa-edit"></i> Editar
      </button>
      <button class="btn-action remover" data-id="'.$img['idImagem'].'">
        <i class="fas fa-trash"></i> Remover
      </button>
    </div>
  </div>';
}
?>