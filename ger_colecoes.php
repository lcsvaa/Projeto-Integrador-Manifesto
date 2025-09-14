<?php
require_once 'conexao.php';

$colecoes = $pdo->query("SELECT * FROM tb_colecao ORDER BY id");
foreach ($colecoes as $col): ?>
  <div class="item-card" data-id="<?= $col['id'] ?>">
    <div class="item-info">
      <h4><?= htmlspecialchars($col['colecaoNome']) ?></h4>
    </div>
    <div class="item-actions">
      <button class="btn-action editar" data-id="<?= $col['id'] ?>">
        <i class="fas fa-edit"></i> Editar
      </button>
      <button class="btn-action remover" data-id="<?= $col['id'] ?>">
        <i class="fas fa-trash"></i> Remover
      </button>
    </div>
  </div>
<?php endforeach;