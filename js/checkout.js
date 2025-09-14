// ========== MENU HAMBÚRGUER ==========
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

// ========== MÁSCARAS DE FORMULÁRIO ==========
const inputCEP = document.getElementById('cep');
if (inputCEP) {
  inputCEP.addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/^(\d{5})(\d)/, '$1-$2');
    e.target.value = value;
  });
}

const inputTelefone = document.getElementById('telefone');
if (inputTelefone) {
  inputTelefone.addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/^(\d{2})(\d)/g, '($1) $2');
    value = value.replace(/(\d)(\d{4})$/, '$1-$2');
    e.target.value = value;
  });
}

const inputCard = document.getElementById('card-number');
if (inputCard) {
  inputCard.addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/(\d{4})(?=\d)/g, '$1 ');
    e.target.value = value;
  });
}

const inputExpiry = document.getElementById('card-expiry');
if (inputExpiry) {
  inputExpiry.addEventListener('input', function (e) {
    let value = e.target.value.replace(/\D/g, '');
    value = value.replace(/^(\d{2})(\d)/, '$1/$2');
    e.target.value = value;
  });
}

// ========== ESTADO DO ENDEREÇO ==========
let possuiEndereco = false;

// ========== AO CARREGAR A PÁGINA ==========
window.addEventListener('DOMContentLoaded', () => {
  // Aviso geral
  alert('Aceitamos apenas pagamento via PIX e entrega padrão.');

  // Desativar abas de pagamento não-Pix
  document.querySelectorAll('.payment-tab').forEach(tab => {
    const isPix = tab.dataset.tab === 'pix';
    if (!isPix) {
      tab.setAttribute('disabled', 'disabled');
      tab.classList.add('disabled');
    } else {
      tab.classList.add('active');
    }
  });

  // Ativar conteúdo PIX apenas
  document.querySelectorAll('.payment-content').forEach(content => {
    content.classList.remove('active');
  });
  const pixTab = document.getElementById('pix-tab');
  if (pixTab) pixTab.classList.add('active');

  // Desativar entrega expressa
  const expressRadio = document.querySelector('input[name="shipping"][value="express"]');
  if (expressRadio) {
    expressRadio.disabled = true;
    const option = expressRadio.closest('.shipping-option');
    if (option) option.classList.add('disabled');
  }

  // Buscar endereço cadastrado
  fetch('buscar_enderecos_usuario.php')
    .then(res => res.json())
    .then(data => {
      const formEndereco = document.getElementById('novo-endereco-form');
      const botaoAlterarEndereco = document.querySelector('form[action="profile.php"]');

      if (!formEndereco || !botaoAlterarEndereco) return;

      let avisoEndereco = document.getElementById('aviso-endereco');
      if (!avisoEndereco) {
        avisoEndereco = document.createElement('p');
        avisoEndereco.id = 'aviso-endereco';
        avisoEndereco.style.color = '#e91e63';
        avisoEndereco.style.margin = '10px 0';
        botaoAlterarEndereco.parentNode.insertBefore(avisoEndereco, botaoAlterarEndereco);
      }

      if (data.status === 'ok' && data.enderecos.length > 0) {
        const d = data.enderecos[0];
        if (document.getElementById('nome')) document.getElementById('nome').value = d.nomeUser || '';
        if (document.getElementById('email')) document.getElementById('email').value = d.email || '';
        if (document.getElementById('telefone')) document.getElementById('telefone').value = d.telefone || '';
        if (document.getElementById('cep')) document.getElementById('cep').value = d.cep || '';
        if (document.getElementById('endereco')) document.getElementById('endereco').value = d.endereco || '';
        if (document.getElementById('numero')) document.getElementById('numero').value = d.numero || '';
        if (document.getElementById('complemento')) document.getElementById('complemento').value = d.complemento || '';
        if (document.getElementById('bairro')) document.getElementById('bairro').value = d.bairro || '';
        if (document.getElementById('cidade')) document.getElementById('cidade').value = d.cidade || '';

        formEndereco.style.display = 'block';
        avisoEndereco.textContent = '';
        possuiEndereco = true;
      } else {
        formEndereco.style.display = 'none';
        avisoEndereco.textContent = 'Você não tem endereços cadastrados. Por favor, cadastre no perfil.';
        possuiEndereco = false;
      }
    })
    .catch(err => {
      console.error('Erro ao carregar dados do perfil:', err);
    });
});

// ========== CONFIRMAR PEDIDO ==========
const botaoConfirmar = document.getElementById('confirm-order');
if (botaoConfirmar) {
  botaoConfirmar.addEventListener('click', async function (e) {
    e.preventDefault();

    if (!possuiEndereco) {
      alert('Você precisa cadastrar um endereço antes de finalizar a compra.');
      return;
    }

    const formaPagamento = document.querySelector('.payment-tab.active')?.dataset.tab || 'indefinido';
    const shippingInput = document.querySelector('input[name="shipping"]:checked');
    const shipping = shippingInput ? shippingInput.value : 'standard';

    const dadosPedido = {
      formaPagamento,
      shipping
    };

    try {
      const res = await fetch('finalizarCompra.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json'
        },
        body: JSON.stringify(dadosPedido)
      });

      const data = await res.json();

      if (data.status === 'ok') {
        alert(data.msg);
        // window.location.href = 'confirmacao.html';
      } else {
        alert('Erro: ' + data.msg);
        if (data.error) console.error('Erro detalhado:', data.error);
      }

    } catch (error) {
      alert('Erro ao enviar pedido. Tente novamente.');
      console.error(error);
    }
  });
}
