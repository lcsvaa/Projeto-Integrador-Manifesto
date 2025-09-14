document.addEventListener('DOMContentLoaded', function() {
  // Menu Hamburguer
  const hamburger = document.getElementById('hamburger-menu');
  const navCenter = document.getElementById('nav-center');
  const navRight = document.getElementById('nav-right');
  
  hamburger.addEventListener('click', function() {
    this.classList.toggle('active');
    navCenter.classList.toggle('open');
    navRight.classList.toggle('open');
    document.body.classList.toggle('menu-open');
  });

  // Fechar menu ao clicar em um link
  document.querySelectorAll('.nav-links a, .login-btn, .cart-btn').forEach(link => {
    link.addEventListener('click', () => {
      hamburger.classList.remove('active');
      navCenter.classList.remove('open');
      navRight.classList.remove('open');
      document.body.classList.remove('menu-open');
    });
  });

  // Carrossel - Atualização completa
  const prevButton = document.getElementById('prev-button');
  const nextButton = document.getElementById('next-button');
  const carousel = document.getElementById('carousel');
  let currentIndex = 0;
  let intervalId;
  
  // Função para mover o carrossel - ATUALIZADA
  function moveCarousel() {
    const items = document.querySelectorAll('.carousel-item');
    if (items.length === 0) return;
    
    // Garante que o índice esteja dentro dos limites
    if (currentIndex >= items.length) currentIndex = 0;
    if (currentIndex < 0) currentIndex = items.length - 1;
    
    // Calcula o deslocamento baseado na largura do item
    const itemWidth = items[0].offsetWidth;
    const offset = -currentIndex * itemWidth;
    
    // Aplica a transformação
    carousel.style.transform = `translateX(${offset}px)`;
    carousel.style.transition = 'transform 0.5s ease';
  }
  
  // Event listeners para os botões
  if (prevButton && nextButton) {
    prevButton.addEventListener('click', function() {
      currentIndex--;
      moveCarousel();
      resetInterval();
    });
    
    nextButton.addEventListener('click', function() {
      currentIndex++;
      moveCarousel();
      resetInterval();
    });
  }
  
  // Configura autoplay
  function startInterval() {
    intervalId = setInterval(function() {
      currentIndex++;
      moveCarousel();
    }, 5000); // Muda a cada 5 segundos
  }
  
  function resetInterval() {
    clearInterval(intervalId);
    startInterval();
  }
  
  // Inicia o carrossel
  moveCarousel();
  startInterval();
  
  // Pausa o carrossel quando o mouse está sobre ele
  carousel.addEventListener('mouseenter', function() {
    clearInterval(intervalId);
  });
  
  carousel.addEventListener('mouseleave', function() {
    startInterval();
  });
  
  // Adiciona suporte para touch (mobile)
  let touchStartX = 0;
  let touchEndX = 0;
  
  carousel.addEventListener('touchstart', function(e) {
    touchStartX = e.changedTouches[0].screenX;
    clearInterval(intervalId);
  }, {passive: true});
  
  carousel.addEventListener('touchend', function(e) {
    touchEndX = e.changedTouches[0].screenX;
    handleSwipe();
    startInterval();
  }, {passive: true});
  
  function handleSwipe() {
    const difference = touchStartX - touchEndX;
    if (difference > 50) { // Swipe para a esquerda
      currentIndex++;
      moveCarousel();
    } else if (difference < -50) { // Swipe para a direita
      currentIndex--;
      moveCarousel();
    }
  }
});

// Função para adicionar produto ao carrinho
function adicionarAoCarrinho(nome, preco, imagem) {
  // Recupera o carrinho do localStorage ou cria um novo array vazio
  let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
  
  // Adiciona o novo produto ao carrinho
  carrinho.push({
    nome: nome,
    preco: preco,
    imagem: imagem
  });
  
  // Salva o carrinho atualizado no localStorage
  localStorage.setItem('carrinho', JSON.stringify(carrinho));
  
  // Mostra mensagem de sucesso
  alert(`${nome} foi adicionado ao carrinho!`);
  
  // Atualiza o contador do carrinho na navbar
  atualizarContadorCarrinho();
}

// Função para atualizar o contador do carrinho
function atualizarContadorCarrinho() {
  let carrinho = JSON.parse(localStorage.getItem('carrinho')) || [];
  let contador = document.querySelector('.cart-btn .contador');
  
  if (!contador) {
    contador = document.createElement('span');
    contador.className = 'contador';
    document.querySelector('.cart-btn').appendChild(contador);
  }
  
  contador.textContent = carrinho.length > 0 ? carrinho.length : '';
}

// Atualiza o contador quando a página carrega
document.addEventListener('DOMContentLoaded', atualizarContadorCarrinho);