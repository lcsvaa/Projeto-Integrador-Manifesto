// Função de notificação
function showNotification(message, type = "success") {
  let container = document.getElementById('notification-container');
  if (!container) {
    container = document.createElement('div');
    container.id = 'notification-container';
    container.style.position = 'fixed';
    container.style.top = '20px';
    container.style.right = '20px';
    container.style.zIndex = '1000';
    container.style.display = 'flex';
    container.style.flexDirection = 'column';
    container.style.gap = '10px';
    container.style.maxWidth = '300px';
    document.body.appendChild(container);
  }

  const notification = document.createElement('div');
  notification.className = `notification ${type}`;
  notification.textContent = message;

  container.appendChild(notification);

  setTimeout(() => {
    notification.classList.add('fade-out');
    notification.addEventListener('animationend', () => {
      notification.remove();
    });
  }, 3000);
}

function adicionarEstilosNotificacao() {
  const style = document.createElement('style');
  style.textContent = `
    #notification-container {
      position: fixed;
      top: 20px;
      right: 20px;
      z-index: 1000;
      display: flex;
      flex-direction: column;
      gap: 10px;
      max-width: 300px;
    }
    .notification {
      padding: 15px;
      border-radius: 5px;
      color: white;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      animation: slideIn 0.3s ease-out;
      position: relative;
    }
    .notification.success {
      background-color: #4CAF50;
    }
    .notification.error {
      background-color: #F44336;
    }
    .notification.warning {
      background-color: #FF9800;
    }
    .fade-out {
      animation: fadeOut 0.5s ease-out forwards;
    }
    @keyframes slideIn {
      from { transform: translateX(100%); opacity: 0; }
      to { transform: translateX(0); opacity: 1; }
    }
    @keyframes fadeOut {
      to { opacity: 0; transform: translateX(100%); }
    }
  `;
  document.head.appendChild(style);
}

function verificarEstoqueBaixo(estoque) {
  const aviso = document.getElementById('estoque-baixo-msg');
  if (!aviso) return;

  if (estoque < 10 && estoque > 0) {
    aviso.classList.add('visible');
  } else {
    aviso.classList.remove('visible');
  }
}

document.addEventListener('DOMContentLoaded', function() {
  adicionarEstilosNotificacao();

  const btnAdd = document.getElementById('btnAdd');
  const qtyInput = document.getElementById('quantity');
  const estoqueMsg = document.getElementById('estoque-baixo-msg');

  // Ajustar max e mensagem inicial considerando tamanho selecionado ou estoque geral
  const selectedBtn = document.querySelector('.size-btn.selected');
  if (selectedBtn) {
    const estoque = parseInt(selectedBtn.dataset.estoque);
    qtyInput.max = estoque;
    qtyInput.setAttribute('data-estoque', estoque);
    verificarEstoqueBaixo(estoque);
  } else {
    const estoque = parseInt(qtyInput.getAttribute('max')) || 0;
    verificarEstoqueBaixo(estoque);
  }

  document.querySelectorAll('.size-btn').forEach(btn => {
    btn.addEventListener('click', function () {
      document.querySelectorAll('.size-btn').forEach(b => b.classList.remove('selected'));
      this.classList.add('selected');

      const estoque = parseInt(this.dataset.estoque);
      qtyInput.max = estoque;
      qtyInput.setAttribute('data-estoque', estoque);

      if (parseInt(qtyInput.value) > estoque) {
        qtyInput.value = estoque;
      }

      verificarEstoqueBaixo(estoque);
    });
  });

  btnAdd?.addEventListener("click", () => {
    const id = new URLSearchParams(window.location.search).get("id");
    const nome = document.querySelector(".product-title").textContent;
    const preco = parseFloat(
      document.querySelector(".product-price").textContent
        .replace("R$ ", "")
        .replace(/\./g, "")   
        .replace(",", ".")    
    );
    const qtd = parseInt(qtyInput.value);
    const tamanho = document.querySelector(".size-btn.selected")?.textContent || "Único";
    const imagem = document.getElementById("mainImage").getAttribute("src").split("/").pop();

    const produto = { id, nome, preco, qtd, tamanho, imagem };

    fetch("adicionarCarrinho.php", {
      method: "POST",
      headers: { "Content-Type": "application/json" },
      body: JSON.stringify(produto)
    })
    .then(res => res.json())
    .then(data => {
      if (data.status === "ok") {
        showNotification("Produto adicionado ao carrinho!");
      } else {
        showNotification("Erro ao adicionar ao carrinho.", "error");
      }
    });
  });
});

// Menu Hamburguer (sem alterações)
const hamburger = document.getElementById('hamburger-menu');
const navCenter = document.getElementById('nav-center');
const navRight = document.getElementById('nav-right');

if (hamburger && navCenter && navRight) {
  hamburger.addEventListener('click', function() {
    this.classList.toggle('active');
    navCenter.classList.toggle('open');
    navRight.classList.toggle('open');
    document.body.classList.toggle('menu-open');

    const isExpanded = this.classList.contains('active');
    this.setAttribute('aria-expanded', isExpanded);
  });

  const closeMenu = () => {
    hamburger.classList.remove('active');
    navCenter.classList.remove('open');
    navRight.classList.remove('open');
    document.body.classList.remove('menu-open');
    hamburger.setAttribute('aria-expanded', 'false');
  };

  document.querySelectorAll('.nav-links a, .login-btn, .cart-btn').forEach(link => {
    link.addEventListener('click', closeMenu);
  });

  document.addEventListener('click', (event) => {
    const isClickInsideNav = navCenter.contains(event.target) || 
                             navRight.contains(event.target) || 
                             hamburger.contains(event.target);
    
    if (!isClickInsideNav && hamburger.classList.contains('active')) {
      closeMenu();
    }
  });

  document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape' && hamburger.classList.contains('active')) {
      closeMenu();
      hamburger.focus();
    }
  });
}

// Troca imagem principal
window.changeImage = function(element, newImage) {
  document.querySelectorAll('.thumbnail').forEach(thumb => {
    thumb.classList.remove('active');
  });
  element.classList.add('active');
  document.getElementById('mainImage').src = newImage;
}

// Atualiza quantidade manualmente
window.changeQuantity = function(change) {
  const input = document.getElementById('quantity');
  const maxEstoque = parseInt(input.getAttribute('data-estoque')) || parseInt(input.max) || 1000;

  let value = parseInt(input.value) + change;
  if (value < 1) value = 1;
  if (value > maxEstoque) value = maxEstoque;

  input.value = value;
}

