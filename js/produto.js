let timeoutBusca;

$(document).on('click', '.btn-cancel', function() {
  limparEFecharFormulario();
});

$(document).ready(function() {
  carregarProdutos();
  atualizarVisibilidade();
  configurarEventos();
  adicionarEstilosNotificacao();
});


$('#btn-novo-produto').on('click', function() {
  toggleFormulario(true, true); 
});

function limparEFecharFormulario() {
  const $formContainer = $('.add-produto-form'); 
  const $form = $formContainer.find('form');     

  if ($form.length === 0) {
    console.error('Formulário <form> não encontrado dentro de .add-produto-form');
    return;
  }


  $form[0].reset();
  $('#produto-tamanho-unico').prop('checked', false);
  $('#estoque-tamanhos-container, #msg-erro-estoque').show();

  
  $('#produto-id').val('');

  
  $('#imagem-preview').attr('src', '').hide();

  $('#estoque-p, #estoque-m, #estoque-g').val('0');

  $form.find('.is-invalid').removeClass('is-invalid');
  $form.find('.invalid-feedback').remove();

  $formContainer.slideUp(300);

  $('html, body').animate({ scrollTop: 0 }, 300);
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


function configurarEventos() {

  $('#busca-produto-adm').on('input', function() {
  clearTimeout(timeoutBusca);
  timeoutBusca = setTimeout(() => {
    const valor = $(this).val().trim();
    buscarProdutos(valor);
  }, 300);
  });


  $('.add-produto-form form').on('submit', function(e) {
  e.preventDefault();
  submeterFormulario(this);  // aqui `this` é o form
  });


  $(document).on('click', '.apaga-produto', confirmarExclusaoProduto);
  $(document).on('click', '.btn-estoque', gerenciarEstoqueClick);
  $(document).on('click', '#EditarProduto', editarProduto);
  $('#produto-tamanho-unico').on('change', atualizarVisibilidade);
  $('#produto-categoria').on('change', filtrarPorCategoria);
  

  $('#estoque-p, #estoque-m, #estoque-g, #produto-estoque').on('input', validarSoma);
}


function buscarProdutos(termo) {
  showLoading();
  
  $.ajax({
    url: 'buscarProdutos.php',
    type: 'GET',
    data: { termo: termo },
    dataType: 'json',
    success: function(res) {
      if (res.status === 'success') {
        renderizarProdutos(res.produtos);
      } else {
        showNotification('Erro ao buscar produtos: ' + res.message, 'error');
      }
    },
    error: function(xhr, status, error) {
      showNotification('Erro na requisição: ' + error, 'error');
    }
  });
}


function carregarProdutos() {
  showLoading();
  
  $.ajax({
    url: 'listarProdutos.php',
    type: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.status === 'success') {
        renderizarProdutos(res.produtos);
      } else {
        showNotification('Erro ao carregar produtos: ' + res.message, 'error');
      }
    },
    error: function(xhr, status, error) {
      showNotification('Erro ao buscar produtos: ' + error, 'error');
    }
  });
}


function renderizarProdutos(produtos) {
  const container = $('#produtos-container');
  container.empty();

  if (produtos.length === 0) {
    container.append('<p class="sem-produtos">Nenhum produto encontrado.</p>');
    return;
  }

  produtos.forEach(prod => {
    container.append(criarCardProduto(prod));
  });
}

function criarCardProduto(prod) {
  const estoqueHtml = criarHtmlEstoque(prod);
  
  return `
    <div class="produto-card" data-id="${prod.id}">
      <img src="uploads/produtos/${prod.imagem ?? 'default.jpg'}" alt="${prod.nomeItem}">
      <div class="produto-info">
        <h3>${prod.nomeItem}</h3>
        <p class="produto-categoria">${prod.categoria}</p>
        <p class="produto-preco">R$ ${parseFloat(prod.valorItem).toFixed(2)}</p>
        <div class="produto-estoque">
          ${estoqueHtml}
        </div>
      </div>
      <div class="produto-actions">
        <button class="btn-action editar" id="EditarProduto" data-action="editar" data-id="${prod.id}">
          <i class="fas fa-edit"></i> Editar
        </button>
        <button class="btn-action remover apaga-produto" data-id="${prod.id}">
          Excluir
        </button>
      </div>
    </div>
  `;
}


function criarHtmlEstoque(prod) {
  const temTamanhos = Array.isArray(prod.estoquesTamanhos) && prod.estoquesTamanhos.length > 0;

  if (temTamanhos) {
    const tamanhos = ['P', 'M', 'G'];
    const estoques = Object.fromEntries(
      tamanhos.map(tam => [tam, 0])
    );

    // Preenche com os valores reais
    prod.estoquesTamanhos.forEach(item => {
      if (estoques.hasOwnProperty(item.tamanho)) {
        estoques[item.tamanho] = item.estoque;
      }
    });

    const htmlTamanhos = tamanhos.map(tam => `
      <div class="estoque-tamanho">
        <strong>${tam}:</strong> 
        <span class="qtd-estoque" data-tamanho="${tam}" data-id="${prod.id}">${estoques[tam]}</span>
        <button class="btn-action small btn-estoque" data-id="${prod.id}" data-acao="incrementar" data-tamanho="${tam}">
          <i class="fas fa-plus"></i>
        </button>
        <button class="btn-action small btn-estoque" data-id="${prod.id}" data-acao="decrementar" data-tamanho="${tam}">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    `).join('');

    return `
      <span class="total-estoque">Estoque Total: ${prod.estoqueItem}</span>
      <div class="estoque-por-tamanho">
        ${htmlTamanhos}
      </div>
    `;
  } else {
    return `
      <span class="total-estoque">Estoque: ${prod.estoqueItem}</span>
      <div class="estoque-actions">
        <button class="btn-action small btn-estoque" data-id="${prod.id}" data-acao="incrementar">
          <i class="fas fa-plus"></i>
        </button>
        <button class="btn-action small btn-estoque" data-id="${prod.id}" data-acao="decrementar">
          <i class="fas fa-minus"></i>
        </button>
      </div>
    `;
  }
}

// Função para confirmar exclusão de produto
function confirmarExclusaoProduto() {
  const id = $(this).data('id');
  
  if (confirm('Tem certeza que deseja remover este produto?')) {
    $.ajax({
      url: 'apagarProduto.php',
      type: 'POST',
      data: { id: id },
      dataType: 'json',
      success: function(res) {
        showNotification(res.message, res.status === 'success' ? 'success' : 'error');
        if (res.status === 'success') {
          carregarProdutos();
        }
      },
      error: function(xhr, error) {
        showNotification('Erro ao excluir produto: ' + error, 'error');
      }
    });
  }
}

function limparValorPreco() {
  let val = $('#produto-preco').val();

  val = val.replace(/\s/g, '')          
           .replace('R$', '')           
           .replace(/\./g, '')          
           .replace(',', '.');          

  $('#produto-preco').val(val);
}

// Função para submeter formulário de produto
function submeterFormulario(form) {

  if (form instanceof jQuery) {
    form = form[0];
  }

   limparValorPreco();

  const formData = new FormData(form);
  const idProduto = $('#produto-id').val();
  const $formContainer = $('.add-produto-form');
  const submitBtn = $(form).find('button[type="submit"]');
  const url = 'criarEditarProduto.php';

  // Validação de campos obrigatórios
  const camposObrigatorios = ['nomeItem', 'descItem', 'valorItem', 'estoqueItem', 'idCategoria', 'idColecao'];
  const camposFaltando = camposObrigatorios.filter(campo => !formData.get(campo));

  if (camposFaltando.length > 0) {
    showNotification(`Preencha os campos obrigatórios: ${camposFaltando.join(', ')}`, 'error');
    return;
  }

  // Validação de estoque por tamanho (se não for tamanho único)
  const isTamanhoUnico = formData.get('tamanhoUnico') === 'on';
  if (!isTamanhoUnico) {
    const estoqueP = parseInt(formData.get('estoqueP')) || 0;
    const estoqueM = parseInt(formData.get('estoqueM')) || 0;
    const estoqueG = parseInt(formData.get('estoqueG')) || 0;
    const estoqueTotal = parseInt(formData.get('estoqueItem')) || 0;

    if ((estoqueP + estoqueM + estoqueG) !== estoqueTotal) {
      showNotification('A soma dos estoques P, M e G deve ser igual ao estoque total.', 'error');
      return;
    }
  }

  // Feedback visual durante envio
  submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Salvando...');

  // Envio via AJAX
  $.ajax({
    url: url,
    type: 'POST',
    data: formData,
    processData: false,
    contentType: false,
    dataType: 'json',
    success: function(res) {
      if (res.status === 'success') {
        showNotification(res.message, 'success');

        $formContainer.slideUp(300, function () {
          if (!idProduto) {
            form.reset();
            $('#produto-id').val('');
          }
          carregarProdutos(); // Atualiza produtos na tela
        });

      } else {
        showNotification(res.message, 'error');
      }

      submitBtn.prop('disabled', false).html('Salvar');
    },
    error: function(xhr, status, error) {
      const errorMsg = xhr.responseJSON?.message || error;
      showNotification(`Erro no servidor: ${errorMsg}`, 'error');

      console.error('Erro completo:', {
        status: xhr.status,
        response: xhr.responseText,
        error: error
      });

      submitBtn.prop('disabled', false).html('Salvar');
    }
  });
}


function toggleFormulario(abrir, limparDados = false) {
  const $formContainer = $('.add-produto-form');
  const $form = $formContainer.find('form'); // encontra o <form> dentro da div

  if ($form.length === 0) {
    console.error('Erro: Formulário não encontrado dentro de .add-produto-form');
    return;
  }

  if (limparDados) {
    $form[0].reset(); 

    $('#produto-id').val('');
    $('#imagem-preview').attr('src', '').hide();
    $('#estoque-p, #estoque-m, #estoque-g').val('0');
    $('#current-image-info').hide().html('');
  }

  if (abrir) {
    $formContainer.slideDown(300, function () {
      $('html, body').animate({
        scrollTop: $formContainer.offset().top - 20
      }, 300);
    });
  } else {
    $formContainer.slideUp(300);
  }
}

// Função para validar formulário
function validarFormulario(formData) {
  // Validações básicas
  if (!formData.get('nomeItem') || !formData.get('descItem') || !formData.get('valorItem')) {
    showNotification('Preencha todos os campos obrigatórios', 'error');
    return false;
  }
  
  // Validação específica para estoque
  if (!formData.get('tamanhoUnico')) {
    const estoqueP = parseInt(formData.get('estoqueP') || 0);
    const estoqueM = parseInt(formData.get('estoqueM') || 0);
    const estoqueG = parseInt(formData.get('estoqueG') || 0);
    const estoqueTotal = parseInt(formData.get('estoqueItem') || 0);
    
    if ((estoqueP + estoqueM + estoqueG) !== estoqueTotal) {
      showNotification('A soma dos estoques por tamanho deve ser igual ao estoque total', 'error');
      return false;
    }
  }
  
  return true;
}

// Função para gerenciar estoque (click handler)
function gerenciarEstoqueClick() {
  const idProduto = $(this).data('id');
  const acao = $(this).data('acao');
  const tamanho = $(this).data('tamanho') || null;
  
  gerenciarEstoque(idProduto, acao, tamanho);
}

// Função para atualizar o estoque no card, para produtos com ou sem tamanhos
function atualizarEstoqueNoCard(card, tamanho, res) {
  if (tamanho) {
    // Produto com tamanhos específicos
    const spanQtde = card.find(`.qtd-estoque[data-tamanho="${tamanho}"]`);
    if (spanQtde.length > 0) {
      spanQtde.text(res.novoEstoqueTamanho ?? '?');
    } else {
      //console.warn(`Elemento .qtd-estoque para tamanho ${tamanho} não encontrado`);
    }

    const totalEstoque = card.find('.total-estoque');
    if (totalEstoque.length > 0) {
      totalEstoque.text('Estoque Total: ' + (res.novoEstoqueTotal ?? '?'));
    } else {
      //console.warn('Elemento .total-estoque não encontrado');
    }
  } else {
    const estoqueSpan = card.find('.produto-estoque > span:not(.total-estoque)');
    if (estoqueSpan.length > 0) {
      estoqueSpan.text('Estoque: ' + (res.novoEstoque ?? '?'));
    } else {
      //console.warn('Elemento para estoque simples não encontrado');
      card.find('span').first().text('Estoque: ' + (res.novoEstoque ?? '?'));
    }
  }
}

// Função para gerenciar estoque (click handler)
function gerenciarEstoqueClick() {
  const idProduto = $(this).data('id');
  const acao = $(this).data('acao');
  const tamanho = $(this).data('tamanho') || null;

  gerenciarEstoque(idProduto, acao, tamanho);
}

// Função para atualizar o estoque no card, para produtos com ou sem tamanhos
function atualizarEstoqueNoCard(card, tamanho, res) {
  if (tamanho) {
    // Produto com tamanhos específicos
    const spanQtde = card.find(`.qtd-estoque[data-tamanho="${tamanho}"]`);
    if (spanQtde.length > 0) {
      spanQtde.text(res.novoEstoqueTamanho ?? '?');
    } else {
      console.warn(`Elemento .qtd-estoque para tamanho ${tamanho} não encontrado`);
    }

    const totalEstoque = card.find('.total-estoque');
    if (totalEstoque.length > 0) {
      totalEstoque.text('Estoque Total: ' + (res.novoEstoqueTotal ?? '?'));
    } else {
      console.warn('Elemento .total-estoque não encontrado');
    }
  } else {
    // Produto sem tamanhos específicos
    const estoqueSpan = card.find('.produto-estoque > span:not(.total-estoque)');
    if (estoqueSpan.length > 0) {
      estoqueSpan.text('Estoque: ' + (res.novoEstoque ?? '?'));
    } else {
      //console.warn('Elemento para estoque simples não encontrado');
      // fallback: tenta atualizar o primeiro span dentro do card
      card.find('span').first().text('Estoque: ' + (res.novoEstoque ?? '?'));
    }
  }
}

// Função para gerenciar estoque (click handler)
function gerenciarEstoqueClick() {
  const idProduto = $(this).data('id');
  const acao = $(this).data('acao');
  const tamanho = $(this).data('tamanho') || null;

  gerenciarEstoque(idProduto, acao, tamanho);
}

// Função para gerenciar estoque (lógica principal)
function gerenciarEstoque(idProduto, acao, tamanho = null) {
  const dados = { idProduto, acao };
  if (tamanho) dados.tamanho = tamanho;

  $.ajax({
    url: 'gerenciarEstoque.php',
    type: 'POST',
    data: dados,
    dataType: 'json',
    success: function(res) {
      //console.log('Resposta AJAX estoque:', res);
      const card = $(`.produto-card[data-id="${idProduto}"]`);
      atualizarEstoqueNoCard(card, tamanho, res);

      if (res.status === 'success') {
        showNotification('Estoque atualizado com sucesso', 'success');
      } else {
        showNotification('Erro ao atualizar estoque: ' + res.message, 'error');
      }
    },
    error: function(xhr, status, error) {
      showNotification('Erro ao atualizar estoque: ' + error, 'error');
    }
  });
}



// Função para editar produto
function editarProduto() {
  const idProduto = $(this).data('id');
  
  $.ajax({
    url: 'buscarProdutoId.php',
    type: 'GET',
    data: { id: idProduto },
    success: function(res) {
      if (res.status === 'success') {
        preencherFormularioEdicao(res.produto);
        toggleFormulario(true); 
      }
    }
  });
}



// Função para preencher formulário de edição
function preencherFormularioEdicao(produto) {

  const valor = parseFloat(produto.valorItem).toFixed(2);
  const valorFormatado = 'R$ ' + valor.replace('.', ',');

  $('#produto-id').val(produto.id);
  $('#produto-nome').val(produto.nomeItem);
  $('#produto-descricao').val(produto.descItem);
  $('#produto-preco').val(valorFormatado);
  $('#produto-estoque').val(produto.estoqueItem);
  $('#produto-categoria-form').val(produto.idCategoria);
  $('#produto-colecao').val(produto.idColecao);

  $('#produto-tamanho-unico').prop('checked', produto.tamanhoUnico);
  $('#estoque-p').val(produto.estoqueP || 0);
  $('#estoque-m').val(produto.estoqueM || 0);
  $('#estoque-g').val(produto.estoqueG || 0);

  
  if (produto.imagem) {
    $('#current-image-info').html(`
      <strong>Imagem atual:</strong> ${produto.imagem}
      <br><small>Deixe em branco para manter esta imagem</small>
    `).show();
  } else {
    $('#current-image-info').html('<strong>Nenhuma imagem cadastrada</strong>').show();
  }

  atualizarVisibilidade();
  $('.add-produto-form')[0].scrollIntoView({ behavior: 'smooth' });
}

// Função para filtrar por categoria
function filtrarPorCategoria() {
  const idCategoria = $(this).val();

  $.ajax({
    url: 'listarProdutoFiltro.php',
    type: 'GET',
    dataType: 'json',
    data: { idCategoria },
    success: function(res) {
      if (res.status === 'success') {
        renderizarProdutos(res.produtos);
      } else {
        showNotification('Erro ao filtrar produtos: ' + res.message, 'error');
      }
    },
    error: function(xhr, status, error) {
      showNotification('Erro ao filtrar produtos: ' + error, 'error');
    }
  });
}

function atualizarVisibilidade() {
  const tamanhoUnico = $('#produto-tamanho-unico').is(':checked');

  if (tamanhoUnico) {
    $('#estoque-tamanhos-container, #msg-erro-estoque').hide();
    $('#estoque-p, #estoque-m, #estoque-g').val(0);
  } else {
    $('#estoque-tamanhos-container').show();
    validarSoma();
  }
}

// Função para validar soma dos estoques
function validarSoma() {
  const estoqueTotal = Number($('#produto-estoque').val()) || 0;
  const estoqueP = Number($('#estoque-p').val()) || 0;
  const estoqueM = Number($('#estoque-m').val()) || 0;
  const estoqueG = Number($('#estoque-g').val()) || 0;
  
  const soma = estoqueP + estoqueM + estoqueG;

  if (Math.abs(soma - estoqueTotal) > 0.001) { 
    $('#msg-erro-estoque').show();
    return false;
  } else {
    $('#msg-erro-estoque').hide();
    return true;
  }
}

// Função para mostrar loading
function showLoading() {
  $('#produtos-container').html('<div class="loading"><i class="fas fa-spinner fa-spin"></i> Carregando...</div>');
}

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

  // Remove a notificação após 3 segundos com animação
  setTimeout(() => {
    notification.classList.add('fade-out');
    notification.addEventListener('animationend', () => {
      notification.remove();
    });
  }, 3000);
}
