// profile.js - Versão Otimizada e Corrigida

// Sistema de Notificação
function showNotification(message, type = "success") {
  let container = document.getElementById('notification-container');

  if (!container) {
    container = document.createElement('div');
    container.id = 'notification-container';
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
  }, 2500);
}

// Inicialização de Máscaras com ViaCEP corrigido
function initMasks() {
  // Verifica se jQuery e jQuery Mask estão carregados
  if (typeof $ === 'function' && $.fn.mask) {
    $('#phone').mask('(00) 00000-0000');
    $('#address-cep').mask('00000-000');
    $('#cpf').mask('000.000.000-00', {reverse: true});
    
    // Auto-preenchimento de CEP - Versão Corrigida
    $('#address-cep').on('blur', function() {
      const cep = $(this).val().replace(/\D/g, '');
      if (cep.length === 8) {
        // Mostrar loading
        $(this).addClass('loading').prop('disabled', true);
        
        // Fazer requisição com tratamento de erro melhorado
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 5000);

        fetch(`https://viacep.com.br/ws/${cep}/json/`, {
          signal: controller.signal
        })
          .then(response => {
            clearTimeout(timeoutId);
            if (!response.ok) throw new Error('CEP não encontrado');
            return response.json();
          })
          .then(data => {
            if (!data || typeof data !== 'object') throw new Error('Resposta inválida da API');
            if (data.erro) throw new Error('CEP não encontrado');
            
            // Preencher campos
            $('#address-street').val(data.logradouro || '').prop('readonly', false);
            $('#address-neighborhood').val(data.bairro || '').prop('readonly', false);
            $('#address-city').val(data.localidade || '').prop('readonly', false);
            $('#address-state').val(data.uf || '').prop('readonly', false);
            $('#address-number').focus();
          })
          .catch(error => {
            clearTimeout(timeoutId);
            showNotification(error.message || 'CEP não encontrado. Preencha os campos manualmente.', 'error');
            // Liberar campos para edição manual
            $('#address-street, #address-neighborhood, #address-city, #address-state')
              .val('').prop('readonly', false);
          })
          .finally(() => {
            $(this).removeClass('loading').prop('disabled', false);
          });
      }
    });
  } else {
    console.error('jQuery Mask plugin não carregado!');
    // Fallback para campos sem máscara
    document.getElementById('address-cep')?.addEventListener('blur', function() {
      const cep = this.value.replace(/\D/g, '');
      if (cep.length === 8) {
        fetch(`https://viacep.com.br/ws/${cep}/json/`)
          .then(response => response.json())
          .then(data => {
            if (!data.erro) {
              document.getElementById('address-street').value = data.logradouro || '';
              document.getElementById('address-neighborhood').value = data.bairro || '';
              document.getElementById('address-city').value = data.localidade || '';
              document.getElementById('address-state').value = data.uf || '';
              document.getElementById('address-number').focus();
            }
          });
      }
    });
  }
}

// Adicione esta função para carregar os pedidos
// Adicione esta função para carregar os pedidos
function loadOrders() {
  const ordersContainer = document.getElementById("orders-container");
  if (!ordersContainer) return;

  ordersContainer.innerHTML = '<p class="no-orders">Carregando seus pedidos...</p>';

  fetch("carregar_pedidos.php", {
    credentials: 'same-origin'
  })
  .then(response => {
    if (!response.ok) throw new Error(`Erro HTTP! status: ${response.status}`);
    return response.json();
  })
  .then(data => {
    if (Array.isArray(data) && data.length > 0) {
      ordersContainer.innerHTML = data.map(createOrderCard).join('');
    } else if (data.error) {
      showNotification(data.error, 'error');
      ordersContainer.innerHTML = `
        <div class="no-orders">
          <p>Erro ao carregar pedidos</p>
          <a href="produtos.php" class="btn-primary">Ver Produtos</a>
        </div>
      `;
    } else {
      ordersContainer.innerHTML = `
        <div class="no-orders">
          <p>Você ainda não fez nenhum pedido</p>
          <a href="produtos.php" class="btn-primary">Ver Produtos</a>
        </div>
      `;
    }
  })
  .catch(error => {
    console.error('Erro ao carregar pedidos:', error);
    showNotification('Erro ao carregar pedidos', 'error');
    ordersContainer.innerHTML = `
      <div class="no-orders">
        <p>Erro ao carregar pedidos</p>
        <a href="produtos.php" class="btn-primary">Ver Produtos</a>
      </div>
    `;
  });
}

// Função para criar o card de pedido
function createOrderCard(order) {
  const date = new Date(order.dataPedido);
  const formattedDate = date.toLocaleDateString('pt-BR');
  
  let productsHtml = '';
  order.produtos.forEach(product => {
    productsHtml += `
      <div class="order-product">
        <img src="${product.imagem}" alt="${product.nome}" class="product-image">
        <div class="product-info">
          <h4>${product.nome}</h4>
          <p>Quantidade: ${product.quantidade}</p>
          <p>R$ ${parseFloat(product.preco).toFixed(2)}</p>
        </div>
      </div>
    `;
  });

  return `
    <div class="order-card">
      <div class="order-header">
        <h3>Pedido #${order.idPedido}</h3>
        <span class="order-status ${order.status.toLowerCase()}">${order.status}</span>
      </div>
      <div class="order-details">
        <p><strong>Data:</strong> ${formattedDate}</p>
        <p><strong>Total:</strong> R$ ${parseFloat(order.valorTotal).toFixed(2)}</p>
      </div>
      <div class="order-products">
        ${productsHtml}
      </div>
    </div>
  `;
}

// Sistema de Abas
function setupProfileTabs() {
  // Inicializa abas se nenhuma estiver ativa
  if ($('.profile-menu li.active').length === 0) {
    $('.profile-menu li:first').addClass('active');
    $('.profile-section:first').addClass('active');
  }

  // Manipulador de clique nas abas
  $('.profile-menu li[data-tab]').off('click').on('click', function(e) {
    e.preventDefault();
    
    const $this = $(this);
    const tabId = $this.data('tab');
    
    if ($this.hasClass('active')) return;
    
    // Atualiza UI
    $('.profile-menu li').removeClass('active');
    $('.profile-section').removeClass('active');
    
    $this.addClass('active');
    $(`#${tabId}`).addClass('active');
    
    // Carrega conteúdo dinâmico conforme necessário
    if (tabId === 'addresses') {
      loadAddresses();
    } else if (tabId === 'orders') {
      loadOrders();
    }
  });
}

// Gerenciamento de Endereços
function loadAddresses() {
  fetch("carregar_enderecos.php", {
    credentials: 'same-origin'
  })
  .then(response => {
    if (!response.ok) throw new Error(`Erro HTTP! status: ${response.status}`);
    return response.json();
  })
  .then(data => {
    const addressesContainer = document.getElementById("addresses-container");
    if (!addressesContainer) return;
    
    if (Array.isArray(data)) {
      addressesContainer.innerHTML = data.length > 0 
        ? data.map(createAddressCard).join('')
        : '<p class="no-address">Nenhum endereço cadastrado</p>';
    } else if (data.error) {
      showNotification(data.error, 'error');
    }
  })
  .catch(error => {
    console.error('Erro ao carregar endereços:', error);
    showNotification('Erro ao carregar endereços', 'error');
  });
}

function createAddressCard(address) {
  const isPrincipal = address.apelidoEndereco && address.apelidoEndereco.includes("(Principal)");

  return `
    <article class="address-card ${isPrincipal ? "principal" : ""}">
      <div class="address-header">
        <h3>${address.apelidoEndereco || "Endereço"}</h3>
        <div class="address-actions">
          <button class="btn-edit" data-id="${address.idEndereco}" aria-label="Editar endereço">
            <i class="fas fa-edit"></i>
          </button>
          <button class="btn-delete" data-id="${address.idEndereco}" aria-label="Excluir endereço">
            <i class="fas fa-trash"></i>
          </button>
        </div>
      </div>
      <div class="address-content">
        <p>${address.rua}, ${address.numero}</p>
        ${address.complemento ? `<p>Complemento: ${address.complemento}</p>` : ""}
        <p>Bairro: ${address.bairro}</p>
        <p>${address.cidade} - ${address.cep}</p>
      </div>
      ${isPrincipal ? '<div class="default-badge"></div>' : ""}
    </article>
  `;
}

// Manipuladores de Formulários
function setupAddressForm() {
  const addAddressBtn = document.getElementById("add-address-btn");
  const addressFormContainer = document.getElementById("address-form-container");
  const addressForm = document.getElementById("address-form");
  const cancelAddressBtn = document.getElementById("cancel-address-btn");

  if (!addAddressBtn || !addressFormContainer || !addressForm || !cancelAddressBtn) return;

  // Mostrar formulário de endereço
  addAddressBtn.addEventListener("click", () => {
    addressForm.reset();
    document.querySelector("#address-form-container .form-title").textContent = "Adicionar Endereço";
    document.querySelector("#address-form .btn-save").textContent = "Salvar Endereço";
    document.querySelector('#address-form input[name="address_id"]')?.remove();
    addAddressBtn.style.display = "none";
    addressFormContainer.style.display = "block";
  });

  // Cancelar formulário de endereço
  cancelAddressBtn.addEventListener("click", () => {
    addressFormContainer.style.display = "none";
    addAddressBtn.style.display = "block";
  });

  // Enviar formulário de endereço
  addressForm.addEventListener("submit", function(e) {
    e.preventDefault();

    const formData = new FormData(this);
    const addressId = formData.get("address_id");
    formData.append("action", addressId ? "update_address" : "add_address");

    fetch("atualizar_dados.php", {
      method: "POST",
      body: formData,
    })
    .then(response => {
      if (!response.ok) throw new Error("Erro na rede");
      return response.json();
    })
    .then(data => {
      if (data.success) {
        showNotification(data.message);
        loadAddresses();
        addressForm.reset();
        addressFormContainer.style.display = "none";
        addAddressBtn.style.display = "block";
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch(error => {
      console.error("Erro:", error);
      showNotification("Erro ao salvar endereço", "error");
    });
  });
}

// Editar Endereço
function loadAddressForEdit(addressId) {
  const formData = new FormData();
  formData.append("action", "get_address");
  formData.append("address_id", addressId);
  formData.append("csrf_token", document.querySelector('input[name="csrf_token"]').value);

  fetch("atualizar_dados.php", {
    method: "POST",
    body: formData,
  })
  .then(response => {
    if (!response.ok) throw new Error("Erro na rede");
    return response.json();
  })
  .then(data => {
    if (data.success) {
      const address = data.address;
      const apelido = address.apelidoEndereco || "";

      document.getElementById("address-name").value = apelido.replace("(Principal) ", "");
      document.getElementById("address-cep").value = address.cep;
      document.getElementById("address-street").value = address.rua;
      document.getElementById("address-number").value = address.numero;
      document.getElementById("address-complement").value = address.complemento || "";
      document.getElementById("address-neighborhood").value = address.bairro;
      document.getElementById("address-city").value = address.cidade;
      document.getElementById("address-default").checked = apelido.includes("(Principal)");

      document.querySelector("#address-form-container .form-title").textContent = "Editar Endereço";
      document.querySelector("#address-form .btn-save").textContent = "Atualizar Endereço";

      let idInput = document.querySelector('#address-form input[name="address_id"]');
      if (!idInput) {
        idInput = document.createElement("input");
        idInput.type = "hidden";
        idInput.name = "address_id";
        document.getElementById("address-form").appendChild(idInput);
      }
      idInput.value = addressId;

      document.getElementById("add-address-btn").style.display = "none";
      document.getElementById("address-form-container").style.display = "block";
    } else {
      showNotification(data.message, "error");
    }
  })
  .catch(error => {
    console.error("Erro:", error);
    showNotification("Erro ao carregar endereço", "error");
  });
}

// Excluir Endereço
function deleteAddress(addressId) {
  if (confirm("Tem certeza que deseja excluir este endereço?")) {
    const formData = new FormData();
    formData.append("action", "delete_address");
    formData.append("address_id", addressId);
    formData.append("csrf_token", document.querySelector('input[name="csrf_token"]').value);

    fetch("atualizar_dados.php", {
      method: "POST",
      body: formData,
    })
    .then(response => {
      if (!response.ok) throw new Error("Erro na rede");
      return response.json();
    })
    .then(data => {
      if (data.success) {
        showNotification(data.message);
        loadAddresses();
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch(error => {
      console.error("Erro:", error);
      showNotification("Erro ao excluir endereço", "error");
    });
  }
}

// Formulário de Dados Pessoais
function setupPersonalDataForm() {
  const form = document.getElementById("personal-data-form");
  if (!form) return;

  // Armazena valores originais para cancelamento
  const originalValues = {
    name: document.getElementById("name").value,
    email: document.getElementById("email").value,
    phone: document.getElementById("phone").value,
    birthdate: document.getElementById("birthdate").value,
  };

  // Botão cancelar - restaura valores originais
  form.querySelector(".btn-cancel")?.addEventListener("click", () => {
    document.getElementById("name").value = originalValues.name;
    document.getElementById("email").value = originalValues.email;
    document.getElementById("phone").value = originalValues.phone;
    document.getElementById("birthdate").value = originalValues.birthdate;
    showNotification("Alterações canceladas", "info");
  });

  // Envio do formulário
  form.addEventListener("submit", function(e) {
    e.preventDefault();

    // Validação de idade (mínimo 18 anos)
    const birthdate = new Date(document.getElementById("birthdate").value);
    const today = new Date();
    let age = today.getFullYear() - birthdate.getFullYear();
    const monthDiff = today.getMonth() - birthdate.getMonth();

    if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthdate.getDate())) {
      age--;
    }

    if (age < 18) {
      showNotification("Você deve ter pelo menos 18 anos", "error");
      return;
    }

    const formData = new FormData(this);
    formData.append("action", "update_personal_data");

    fetch("atualizar_dados.php", {
      method: "POST",
      body: formData,
    })
    .then(response => {
      if (!response.ok) throw new Error("Erro na rede");
      return response.json();
    })
    .then(data => {
      if (data.success) {
        // Atualiza UI
        document.querySelectorAll(".profile-name, .username").forEach(el => {
          el.textContent = formData.get("name");
        });
        document.querySelectorAll(".profile-email").forEach(el => {
          el.textContent = formData.get("email");
        });

        // Atualiza valores originais
        originalValues.name = formData.get("name");
        originalValues.email = formData.get("email");
        originalValues.phone = formData.get("phone");
        originalValues.birthdate = formData.get("birthdate");

        showNotification(data.message);
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch(error => {
      console.error("Erro:", error);
      showNotification("Erro ao atualizar dados", "error");
    });
  });
}

// Alternar Visibilidade de Senha
function setupPasswordToggle() {
  document.addEventListener("click", function(e) {
    if (e.target.closest(".toggle-password")) {
      const toggleBtn = e.target.closest(".toggle-password");
      const targetId = toggleBtn.getAttribute("data-target");
      const passwordInput = document.getElementById(targetId);
      
      if (passwordInput.type === "password") {
        passwordInput.type = "text";
        toggleBtn.innerHTML = '<i class="fas fa-eye-slash"></i>';
      } else {
        passwordInput.type = "password";
        toggleBtn.innerHTML = '<i class="fas fa-eye"></i>';
      }
    }
  });
}

// Formulário de Alteração de Senha
function setupChangePasswordForm() {
  const form = document.getElementById("change-password-form");
  if (!form) return;

  form.addEventListener("submit", function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    formData.append("action", "change_password");
    
    // Validação básica
    const newPassword = formData.get("new_password");
    const confirmPassword = formData.get("confirm_password");
    
    if (newPassword !== confirmPassword) {
      showNotification("As senhas não coincidem!", "error");
      return;
    }
    
    fetch("atualizar_dados.php", {
      method: "POST",
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showNotification(data.message);
        this.reset();
      } else {
        showNotification(data.message, "error");
      }
    })
    .catch(error => {
      console.error("Erro:", error);
      showNotification("Erro ao alterar senha", "error");
    });
  });
}

// Menu Hamburguer
function setupHamburgerMenu() {
  const hamburger = document.getElementById("hamburger-menu");
  if (hamburger) {
    hamburger.addEventListener("click", () => {
      document.body.classList.toggle("menu-open");
    });
  }
}

// Delegação de Eventos para Elementos Dinâmicos
function setupEventDelegation() {
  document.addEventListener("click", function(e) {
    // Editar Endereço
    if (e.target.closest(".btn-edit")) {
      const btn = e.target.closest(".btn-edit");
      loadAddressForEdit(btn.getAttribute("data-id"));
    }

    // Excluir Endereço
    if (e.target.closest(".btn-delete")) {
      const btn = e.target.closest(".btn-delete");
      deleteAddress(btn.getAttribute("data-id"));
    }
  });
}

// Funções de Exclusão de Conta
function createDeleteConfirmationModal() {
  const modal = document.createElement('div');
  modal.id = 'delete-confirm-modal';
  
  modal.innerHTML = `
    <div class="confirm-box">
      <h3>Tem certeza que deseja deletar sua conta?</h3>
      <p>Esta ação não pode ser desfeita. Todos os seus dados serão permanentemente removidos.</p>
      <div class="confirm-buttons">
        <button id="confirm-delete-btn" class="btn-danger">Sim, deletar</button>
        <button id="cancel-delete-btn" class="btn-secondary">Cancelar</button>
      </div>
    </div>
  `;
  
  document.body.appendChild(modal);
}

function deleteUserAccount() {
  // Obter o token CSRF do formulário ou meta tag
  const csrfToken = document.querySelector('input[name="csrf_token"]')?.value || 
                   document.querySelector('meta[name="csrf-token"]')?.content;

  if (!csrfToken) {
    showNotification('Token de segurança não encontrado', 'error');
    return;
  }

  // Usar FormData para garantir o envio correto do token
  const formData = new FormData();
  formData.append('action', 'delete_account');
  formData.append('csrf_token', csrfToken);

  fetch('deletar_conta.php', {
    method: 'POST',
    body: formData,
    headers: {
      'Accept': 'application/json'
    }
  })
  .then(response => {
    if (!response.ok) throw new Error('Erro na rede');
    return response.json();
  })
  .then(data => {
    if (data.success) {
      showNotification('Conta deletada com sucesso. Redirecionando...', 'success');
      setTimeout(() => {
        window.location.href = 'index.php';
      }, 2000);
    } else {
      showNotification(data.message || 'Erro ao deletar conta', 'error');
      if (data.redirect) {
        setTimeout(() => {
          window.location.href = data.redirect;
        }, 1500);
      }
    }
  })
  .catch(error => {
    console.error('Error:', error);
    showNotification('Erro ao conectar com o servidor', 'error');
  });
}

function setupAccountDeletion() {
  createDeleteConfirmationModal();
  
  const deleteBtn = document.getElementById('delete-account-btn');
  const confirmModal = document.getElementById('delete-confirm-modal');
  const confirmBtn = document.getElementById('confirm-delete-btn');
  const cancelBtn = document.getElementById('cancel-delete-btn');

  if (!deleteBtn) return;

  deleteBtn.addEventListener('click', () => {
    confirmModal.style.display = 'flex';
  });

  cancelBtn.addEventListener('click', () => {
    confirmModal.style.display = 'none';
  });

  confirmBtn.addEventListener('click', () => {
    confirmModal.style.display = 'none';
    deleteUserAccount();
  });
}

// Inicialização Principal
document.addEventListener("DOMContentLoaded", function() {
  // Inicializa recursos dependentes do jQuery quando o DOM estiver pronto
  $(document).ready(function() {
    initMasks();
    setupProfileTabs();
    
    // Carrega pedidos se a aba estiver ativa
    if ($('#orders').hasClass('active')) {
      loadOrders();
    }
  });

  // Inicializa outros recursos
  setupHamburgerMenu();
  setupAddressForm();
  setupPersonalDataForm();
  setupPasswordToggle();
  setupChangePasswordForm();
  setupEventDelegation();
  setupAccountDeletion();
});