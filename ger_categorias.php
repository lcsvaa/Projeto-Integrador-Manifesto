<?php
require_once 'conexao.php';

$categorias = $pdo->query("SELECT * FROM tb_categoria ORDER BY id");
foreach ($categorias as $cat): ?>
  <div class="item-card" data-id="<?= $cat['id'] ?>">
    <div class="item-info">
      <h4><?= htmlspecialchars($cat['ctgNome']) ?></h4>
    </div>
    <div class="item-actions">
      <button class="btn-action editar" data-id="<?= $cat['id'] ?>">
        <i class="fas fa-edit"></i> Editar
      </button>
      <button class="btn-action remover" data-id="<?= $cat['id'] ?>">
        <i class="fas fa-trash"></i> Remover
      </button>
    </div>
  </div>
<?php endforeach;