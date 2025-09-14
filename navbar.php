<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar">
    <div class="container">
        <div class="nav-left">
            <a href="index.php">
                <img src="img/logo.png" alt="Logo da loja" class="logo">
            </a>
        </div>

        <div class="nav-center" id="nav-center">
            <ul class="nav-links">
                <li><a href="produtos.php">Produtos</a></li>
                <li><a href="novidades.php">Novidades</a></li>
                <li><a href="colecoes.php" class="active">Colecoes</a></li>
            </ul>
        </div>

        <div class="nav-right" id="nav-right">
            <input type="text" class="search-bar" id="searchInput" placeholder="Buscar produtos...">
            <?php if(isset($_SESSION['user_id'])): ?>
                <a href="profile.php" class="login-btn"><i class="fas fa-user-circle"></i> Perfil</a>
            <?php else: ?>
                <a href="login.php" class="login-btn"><i class="fas fa-user"></i> Login</a>
            <?php endif; ?>
            <a href="cart.php" class="cart-btn"><i class="fas fa-shopping-cart"></i></a>
        </div>

        <button class="hamburger-menu" id="hamburger-menu" aria-label="Menu">
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
            <span class="hamburger-line"></span>
        </button>
    </div>
</nav>

<div class="resultados-produtos" style="display: none;">
        <p class="sem-resultados" style="display: none; color: #888; padding: 1rem;">Nenhum produto encontrado.</p>
        <div id="lista-produtos"></div>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function () {
  const $input = $('#searchInput');
  const $container = $('.resultados-produtos');
  const $mensagem = $('.sem-resultados');
  const $lista = $('#lista-produtos');

  $input.on('input', function () {
    const termo = $input.val().trim();

    if (termo === '') {
      $container.hide();
      $mensagem.hide();
      $lista.empty();
      return;
    }

    $.ajax({
      url: 'buscar-produtos-pesquisa.php',
      method: 'GET',
      data: { termo: termo },
      success: function (html) {
        $container.show();
        $lista.html(html);
        $mensagem.toggle(html.trim() === '');
      },
      error: function () {
        console.error('Erro ao buscar produtos.');
      }
    });
  });
});
</script>