    // Filtro de categorias
    document.addEventListener('DOMContentLoaded', function () {
      const filterButtons = document.querySelectorAll('.categoria-btn');

      filterButtons.forEach(button => {
        button.addEventListener('click', function () {
          // Remove a classe active de todos os botões
          filterButtons.forEach(btn => btn.classList.remove('active'));

          // Adiciona a classe active ao botão clicado
          this.classList.add('active');

          const categoria = this.getAttribute('data-categoria');

          if (categoria === 'todos') {
            // Mostra todas as categorias
            document.querySelectorAll('.categoria-section').forEach(section => {
              section.style.display = 'block';
            });
          } else {
            // Esconde todas as categorias
            document.querySelectorAll('.categoria-section').forEach(section => {
              section.style.display = 'none';
            });

            // Mostra apenas a categoria selecionada
            document.getElementById(categoria).style.display = 'block';
          }

          // Rola suavemente para a seção
          if (categoria !== 'todos') {
            document.getElementById(categoria).scrollIntoView({
              behavior: 'smooth'
            });
          } else {
            window.scrollTo({
              top: 0,
              behavior: 'smooth'
            });
          }
        });
      });
    });

    // Botão Voltar ao Topo
    const backToTopButton = document.getElementById('back-to-top');

    window.addEventListener('scroll', () => {
      if (window.pageYOffset > 300) {
        backToTopButton.style.display = 'flex';
      } else {
        backToTopButton.style.display = 'none';
      }
    });

    backToTopButton.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });