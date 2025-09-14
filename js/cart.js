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

  // Remove a notificação após 3 segundos com animação
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

// === MENU HAMBÚRGUER ===
const hamburgerMenu = document.getElementById('hamburger-menu');
const navCenter = document.getElementById('nav-center');
const navRight = document.getElementById('nav-right');
const body = document.body;

if (hamburgerMenu && navCenter && navRight) {
  hamburgerMenu.addEventListener('click', () => {
    hamburgerMenu.classList.toggle('active');
    navCenter.classList.toggle('open');
    navRight.classList.toggle('open');
    body.classList.toggle('menu-open');

    document.querySelectorAll('.hamburger-line').forEach((line, index) => {
      if (hamburgerMenu.classList.contains('active')) {
        if (index === 0) line.style.transform = 'translateY(8px) rotate(45deg)';
        if (index === 1) line.style.opacity = '0';
        if (index === 2) line.style.transform = 'translateY(-8px) rotate(-45deg)';
      } else {
        line.style.transform = '';
        line.style.opacity = '';
      }
    });
  });
}

const clearCartBtn = document.getElementById('clear-cart');

function atualizarEstadoBotaoLimpar() {
  const temItens = document.querySelectorAll('.cart-item').length > 0;
  clearCartBtn.disabled = !temItens;
}

clearCartBtn?.addEventListener('click', () => {
  if (!confirm('Tem certeza que deseja limpar o carrinho?')) return;

  clearCartBtn.disabled = true; // desativa o botão durante a requisição

  fetch('limparCarrinho.php')
    .then(res => res.json())
    .then(data => {
      if (data.status === 'ok') {
        // Remove todos os itens do carrinho da interface
        document.querySelectorAll('.cart-item').forEach(item => item.remove());

        updateCartTotal();
        showNotification('Carrinho limpo com sucesso!');
      } else {
        showNotification('Erro ao limpar o carrinho.', 'error');
      }
    })
    .catch(() => {
      showNotification('Erro ao limpar o carrinho.', 'error');
    })
    .finally(() => {
      atualizarEstadoBotaoLimpar(); // atualiza o estado do botão após finalizar
    });
});

// Atualiza o estado do botão assim que o script roda, para manter coerência
atualizarEstadoBotaoLimpar();




document.querySelector('.checkout-btn')?.addEventListener('click', () => {
  const temItens = document.querySelectorAll('.cart-item').length > 0;

  if (!temItens) {
    showNotification('Seu carrinho está vazio. Adicione itens antes de finalizar.', 'warning');
    return;
  }

  // Redireciona para a página de checkout
  window.location.href = 'checkout.php';
});

function atualizarBotoesQuantidade(item) {
  const input = item.querySelector('.quantity-input');
  const btnMinus = item.querySelector('.quantity-btn.minus');
  const btnPlus = item.querySelector('.quantity-btn.plus');
  const max = parseInt(input.getAttribute('max')) || 99;
  let value = parseInt(input.value);

  btnMinus.disabled = value <= 1;
  btnPlus.disabled = value >= max;
}

function mostrarAvisoEstoqueMaximo() {
  showNotification('Você atingiu o limite do estoque disponível.', 'warning');
}

function formatREAL(valor) {
  return `R$ ${valor.toFixed(2).replace('.', ',')}`;
}

document.querySelectorAll('.remove-item').forEach(btn => {
  btn.addEventListener('click', () => {
    const item = btn.closest('.cart-item');
    if (!item) return;

    const key = item.dataset.key;
    if (!key) return;

    fetch(`removerCarrinho.php?key=${encodeURIComponent(key)}`)
      .then(res => res.json())
      .then(data => {
        if (data.status === 'ok') {
          // Remove o item do DOM
          item.remove();

          // Atualiza o total do carrinho
          updateCartTotal();

          // Se quiser, mostrar notificação
          showNotification('Item removido do carrinho', 'success');

          // Se quiser atualizar botões de quantidade restantes
          document.querySelectorAll('.cart-item').forEach(atualizarBotoesQuantidade);

          // Se não tiver mais itens, mostrar mensagem de carrinho vazio
          if (document.querySelectorAll('.cart-item').length === 0) {
            // Atualize a UI para carrinho vazio, ou recarregue página
            location.reload();
          }
        } else {
          showNotification(data.message || 'Erro ao remover item.', 'error');
        }
      })
      .catch(err => {
        showNotification('Erro na remoção do item.', 'error');
        console.error('Erro ao remover item:', err);
      });
  });
});


function updateCartTotal() {
  const cartItems = document.querySelectorAll('.cart-item');
  let subtotal = 0;
  let quantidadeTotal = 0;

  cartItems.forEach(item => {
    const priceElem = item.querySelector('.item-price');
    const inputQtd = item.querySelector('.quantity-input');
    if (!priceElem || !inputQtd) return;

    // Preço no formato R$ 123,45 -> 123.45
    const priceText = priceElem.textContent.trim().replace('R$', '').replace(/\./g, '').replace(',', '.');
    const price = parseFloat(priceText) || 0;

    const qtd = parseInt(inputQtd.value) || 0;

    subtotal += price * qtd;
    quantidadeTotal += qtd;
  });

  const frete = quantidadeTotal > 0 ? 15.90 : 0;

  let desconto = 0;
  if (descontoTipo === 'porcentagem') {
    desconto = subtotal * (descontoValor / 100);
  } else if (descontoTipo === 'valor') {
    desconto = descontoValor;
  }

  if (desconto > subtotal) desconto = subtotal;

  let total = subtotal - desconto + frete;

  // Debug log (depois pode remover)
  console.log('Subtotal calculado:', subtotal);
  console.log('Quantidade total:', quantidadeTotal);
  console.log('Desconto:', desconto);
  console.log('Total final:', total);

  function formatBRL(valor) {
     return valor.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
  }

  const subtotalElem = document.querySelector('.subtotal-value');
  if (subtotalElem) subtotalElem.textContent = formatBRL(subtotal);

  const freteElem = document.querySelector('.frete-value');
  if (freteElem) freteElem.textContent = formatBRL(frete);

  const descontoElem = document.querySelector('.desconto-value');
  if (descontoElem) {
    if (desconto > 0) {
      descontoElem.textContent = `- ${formatBRL(desconto)}`;
      descontoElem.style.display = '';
    } else {
      descontoElem.textContent = '';
      descontoElem.style.display = 'none';
    }
  }

  const totalElem = document.querySelector('.total-value');
  if (totalElem) totalElem.textContent = formatBRL(total);

  const subtotalLabel = document.querySelector('.summary-row span:first-child');
  if (subtotalLabel) {
    subtotalLabel.textContent = `Subtotal (${quantidadeTotal} item${quantidadeTotal !== 1 ? 's' : ''})`;
  }
}

// Botão aplicar cupom
const couponBtn = document.querySelector('.coupon-btn');
if (couponBtn) {
  couponBtn.addEventListener('click', () => {
    const input = document.querySelector('.coupon-input');
    const couponCode = input?.value.trim();
    if (!couponCode) {
      showNotification('Por favor, insira um código de cupom');
      return;
    }

    fetch(`validarCupom.php?codigo=${encodeURIComponent(couponCode)}`)
      .then(res => res.json())
      .then(data => {
        showNotification(data.msg);

        if (data.status === 'ok') {
          descontoTipo = data.tipo;      // "valor" ou "porcentagem"
          descontoValor = parseFloat(data.valor) || 0;
          updateCartTotal();
        } else {
          descontoTipo = null;
          descontoValor = 0;
          updateCartTotal();
        }
      })
      .catch(err => {
        showNotification('Erro ao validar cupom.', 'error');
        console.error('Erro ao validar cupom:', err);
      });
  });
}

// Listener para botões de quantidade
document.querySelectorAll('.quantity-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    const item = btn.closest('.cart-item');
    if (!item) return;

    const input = item.querySelector('.quantity-input');
    if (!input) return;

    const key = item.dataset.key;
    if (!key) return;

    const max = parseInt(input.getAttribute('max')) || 99;
    let value = parseInt(input.value);
    if (isNaN(value)) value = 1;

    if (btn.classList.contains('minus')) {
      if (value > 1) value--;
    } else {
      if (value < max) {
        value++;
      } else {
        mostrarAvisoEstoqueMaximo();
        return;
      }
    }

    input.value = value;
    atualizarBotoesQuantidade(item);

    fetch(`atualizarCarrinho.php?key=${encodeURIComponent(key)}&quantidade=${value}`)
      .then(res => res.json())
      .then(data => {
        if (data.status === 'ok') {
          updateCartTotal();
        } else if (data.status === 'error') {
          showNotification(data.message, 'error');
          // Restaura valor anterior se erro
          input.value = value - (btn.classList.contains('plus') ? 1 : 0);
          atualizarBotoesQuantidade(item);
        }
      })
      .catch((err) => {
        showNotification('Erro na atualização da quantidade.', 'error');
        console.error('Erro no fetch atualizarCarrinho:', err);
      });
  });
});

// Inicializa os botões e total ao carregar a página
document.querySelectorAll('.cart-item').forEach(item => {
  atualizarBotoesQuantidade(item);
});
updateCartTotal();
