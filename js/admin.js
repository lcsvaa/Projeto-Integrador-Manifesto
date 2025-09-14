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

document.addEventListener("DOMContentLoaded", function () {
  // Verifica a aba ativa a partir da URL
  const urlParams = new URLSearchParams(window.location.search);
  const activeTab = urlParams.get("tab") || "pedidos"; // Default para 'pedidos'

  // Ativa a aba correta no carregamento
  activateTab(activeTab);

  // Alternar entre categorias
  const categoryTabs = document.querySelectorAll(".category-tab");

  categoryTabs.forEach((tab) => {
    tab.addEventListener("click", function (e) {
      e.preventDefault();

      const category = this.getAttribute("data-category");
      // Atualiza a URL sem recarregar a página
      history.pushState(null, "", `admin.php?tab=${category}`);

      // Ativa a aba selecionada
      activateTab(category);
    });
  });

  // Função para ativar a aba selecionada
  function activateTab(category) {
    // Remove active class from all tabs and sections
    document
      .querySelectorAll(".category-tab")
      .forEach((t) => t.classList.remove("active"));
    document
      .querySelectorAll(".admin-section")
      .forEach((s) => s.classList.remove("active"));

    // Add active class to clicked tab and corresponding section
    const activeTab = document.querySelector(
      `.category-tab[data-category="${category}"]`
    );
    if (activeTab) {
      activeTab.classList.add("active");
      document.getElementById(`${category}-section`).classList.add("active");
    } else {
      // Fallback caso a aba não exista
      document
        .querySelector('.category-tab[data-category="pedidos"]')
        .classList.add("active");
      document.getElementById("pedidos-section").classList.add("active");
    }
  }

  // Alternar entre abas de conteúdo
  const contentTabs = document.querySelectorAll(".content-tab");

  contentTabs.forEach((tab) => {
    tab.addEventListener("click", function () {
      // Remove active class from all tabs and panels
      document
        .querySelectorAll(".content-tab")
        .forEach((t) => t.classList.remove("active"));
      document
        .querySelectorAll(".content-panel")
        .forEach((p) => p.classList.remove("active"));

      // Add active class to clicked tab and corresponding panel
      this.classList.add("active");
      const content = this.getAttribute("data-content");
      document.getElementById(`${content}-panel`).classList.add("active");
    });
  });


  // Formulário de adicionar cupom
  const addCupomBtn = document.querySelector(".add-cupom-btn");
  const addCupomForm = document.querySelector(".add-cupom-form");

  if (addCupomBtn && addCupomForm) {
    addCupomBtn.addEventListener("click", function () {
      addCupomForm.style.display = "block";
      this.style.display = "none";
      addCupomForm.scrollIntoView({ behavior: "smooth" });
    });

    const cancelCupomBtn = addCupomForm.querySelector(".btn-cancel");
    cancelCupomBtn.addEventListener("click", function () {
      addCupomForm.style.display = "none";
      addCupomBtn.style.display = "inline-flex";
    });
  }

  // =============================================
  // Gerenciamento do Carrossel - Atualizado
  // =============================================
  const formCarrossel = document.getElementById("form-carrossel");
  if (formCarrossel) {
    formCarrossel.addEventListener("submit", function (e) {
      e.preventDefault();
      const formData = new FormData(this);

      // Desabilitar botão durante o processamento
      const submitBtn = this.querySelector('button[type="submit"]');
      submitBtn.disabled = true;
      submitBtn.innerHTML =
        '<i class="fas fa-spinner fa-spin"></i> Enviando...';

      fetch("processa_carrossel.php?action=add", {
        method: "POST",
        body: formData,
      })
        .then((response) => response.json())
        .then((data) => {
          if (data.success) {
            showNotification("Imagem adicionada com sucesso!");
            this.reset();
            loadCarrosselImages(); // Recarregar a lista de imagens
          } else {
            showNotification("Erro: " + data.message);
          }
        })
        .catch((error) => {
          showNotification("Erro na requisição: " + error);
        })
        .finally(() => {
          submitBtn.disabled = false;
          submitBtn.textContent = "Adicionar Imagem";
        });
    });
  }

  function loadCarrosselImages() {
    fetch("ger_carrossel.php")
      .then((response) => response.text())
      .then((html) => {
        // Atualiza apenas o grid de imagens, não todo o conteúdo
        const grid = document.getElementById("carrossel-grid");
        if (grid) {
          grid.innerHTML = html;
          setupCarrosselActions();
        }
      })
      .catch(error => {
        console.error('Erro ao carregar imagens:', error);
      });
  }

  // Certifique-se que esta função está no seu admin.js
  function setupCarrosselActions() {
    // Botões de edição
    document.querySelectorAll(".btn-action.editar").forEach(btn => {
      btn.addEventListener("click", function() {
        const id = this.getAttribute("data-id");
        openCarrosselEditModal(id);
      });
    });

    // Botões de remoção (mantenha o existente)
    document.querySelectorAll(".btn-action.remover").forEach(btn => {
      btn.addEventListener("click", function() {
        const id = this.getAttribute("data-id");
        if (confirm("Tem certeza que deseja remover esta imagem?")) {
          fetch(`processa_carrossel.php?action=remove&id=${id}`)
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                showNotification("Imagem removida com sucesso!");
                loadCarrosselImages();
              } else {
                showNotification("Erro: " + data.message, "error");
              }
            });
        }
      });
    });
  }

  function openCarrosselEditModal(id) {
    // Modal de edição
    const modalHTML = `
    <div class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Editar Imagem do Carrossel</h3>
                <button class="btn-action close-modal"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form id="edit-carrossel-form" enctype="multipart/form-data">
                    <div class="form-group">
                        <label>Status da Imagem:</label>
                        <select name="status" class="form-control" required>
                            <option value="principal">Principal (Destaque)</option>
                            <option value="ativa" selected>Ativa</option>
                            <option value="inativa">Inativa</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Nova Imagem (opcional):</label>
                        <input type="file" name="imagem" accept="image/*" class="form-control">
                        <small>Formatos aceitos: JPG, PNG, GIF (máx. 5MB)</small>
                    </div>
                    <div class="form-group">
                        <label>Link de redirecionamento (opcional):</label>
                        <input type="url" name="link" placeholder="https://exemplo.com" class="form-control">
                    </div>
                    <input type="hidden" name="idImagem" value="${id}">
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-cancel close-modal">Cancelar</button>
                <button class="btn-submit" id="save-carrossel-btn">Salvar Alterações</button>
            </div>
        </div>
    </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);

    // Configurar eventos do modal
    document.querySelectorAll(".close-modal").forEach(btn => {
      btn.addEventListener("click", closeModal);
    });

    // Configurar envio do formulário
    document.getElementById("save-carrossel-btn").addEventListener("click", function() {
      saveCarrosselChanges(id);
    });
  }

  function saveCarrosselChanges(id) {
    const form = document.getElementById("edit-carrossel-form");
    const formData = new FormData(form);
    
    const btn = document.getElementById("save-carrossel-btn");
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

    fetch("processa_carrossel.php", {
      method: "POST",
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showNotification("Imagem atualizada com sucesso!");
        loadCarrosselImages();
        closeModal();
      } else {
        showNotification("Erro: " + data.message, "error");
      }
    })
    .catch(error => {
      showNotification("Erro na requisição: " + error, "error");
    })
    .finally(() => {
      btn.disabled = false;
      btn.innerHTML = 'Salvar Alterações';
    });
  }

  // Função para fechar o modal
  function closeModal() {
    const modal = document.querySelector(".modal-overlay");
    if (modal) {
      modal.remove();
    }
  }

  // Função para obter o formulário de edição baseado no tipo
  function getEditForm(type, id, data = {}) {
    switch (type) {
      case "cliente":
        return `
          <form class="edit-form">
            <div class="form-row">
              <div class="form-group">
                <label>Nome:</label>
                <input type="text" value="Nome do Cliente ${id}" required>
              </div>
              <div class="form-group">
                <label>E-mail:</label>
                <input type="email" value="cliente${id}@exemplo.com" required>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>CPF:</label>
                <input type="text" value="123.456.789-00" required>
              </div>
              <div class="form-group">
                <label>Data Nascimento:</label>
                <input type="date" value="1990-01-01" required>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Tipo:</label>
                <select>
                  <option value="user">Usuário</option>
                  <option value="admin">Administrador</option>
                </select>
              </div>
              <div class="form-group">
                <label>Status:</label>
                <select>
                  <option value="ativo">Ativo</option>
                  <option value="desativado">Desativado</option>
                </select>
              </div>
            </div>
          </form>
        `;

      case "pedido":
        return `
          <form class="edit-form">
            <div class="form-row">
              <div class="form-group">
                <label>Status:</label>
                <select>
                  <option value="pendente">Pendente</option>
                  <option value="processando">Processando</option>
                  <option value="enviado">Enviado</option>
                  <option value="entregue">Entregue</option>
                  <option value="cancelado">Cancelado</option>
                </select>
              </div>
              <div class="form-group">
                <label>Data:</label>
                <input type="date" value="2023-05-15" required>
              </div>
            </div>
            <div class="form-group">
              <label>Endereço de Entrega:</label>
              <textarea rows="3">Rua Exemplo, 123 - Centro - São Paulo/SP</textarea>
            </div>
            <div class="form-group">
              <label>Observações:</label>
              <textarea rows="2"></textarea>
            </div>
          </form>
        `;

      case "produto":
        return `
          <form class="edit-form">
            <div class="form-row">
              <div class="form-group">
                <label>Nome:</label>
                <input type="text" value="${id}" required>
              </div>
              <div class="form-group">
                <label>Categoria:</label>
                <select>
                  <option>Roupas</option>
                  <option>Acessórios</option>
                  <option>Coleção X</option>
                </select>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Preço (R$):</label>
                <input type="number" step="0.01" value="129.90" required>
              </div>
              <div class="form-group">
                <label>Estoque:</label>
                <input type="number" value="42" required>
              </div>
            </div>
            <div class="form-group">
              <label>Descrição:</label>
              <textarea rows="4">Descrição detalhada do produto ${id}</textarea>
            </div>
            <div class="form-group">
              <label>Imagem:</label>
              <input type="file" accept="image/*">
              <small>Deixe em branco para manter a imagem atual</small>
            </div>
          </form>
        `;

      case "cupom":
        return `
          <form class="edit-form">
            <div class="form-row">
              <div class="form-group">
                <label>Código:</label>
                <input type="text" value="${id}" required>
              </div>
              <div class="form-group">
                <label>Desconto (%):</label>
                <input type="number" min="1" max="100" value="20" required>
              </div>
            </div>
            <div class="form-row">
              <div class="form-group">
                <label>Validade:</label>
                <input type="date" value="2023-12-31" required>
              </div>
              <div class="form-group">
                <label>Usos Restantes:</label>
                <input type="number" min="0" value="150">
                <small>Deixe em branco para ilimitado</small>
              </div>
            </div>
            <div class="form-group">
              <label>Status:</label>
              <select>
                <option value="ativo">Ativo</option>
                <option value="inativo">Inativo</option>
              </select>
            </div>
          </form>
        `;

      case "conteudo":
        return `
          <form class="edit-form">
            <div class="form-row">
              <div class="form-group">
                <label>Título:</label>
                <input type="text" value="${id}" required>
              </div>
              <div class="form-group">
                <label>Data:</label>
                <input type="date" value="2023-03-15" required>
              </div>
            </div>
            <div class="form-group">
              <label>Imagem:</label>
              <input type="file" accept="image/*">
              <small>Deixe em branco para manter a imagem atual</small>
            </div>
            <div class="form-group">
              <label>Conteúdo:</label>
              <textarea rows="6">Conteúdo detalhado sobre ${id}</textarea>
            </div>
          </form>
        `;

      case "categoria":
        return `
          <form class="edit-form">
            <div class="form-group">
              <label>Nome da Categoria:</label>
              <input type="text" name="nome" value="${data.nome || ''}" required>
            </div>
          </form>
        `;
      
      case "colecao":
        return `
          <form class="edit-form">
            <div class="form-group">
              <label>Nome da Coleção:</label>
              <input type="text" name="nome" value="${data.nome || ''}" required>
            </div>
          </form>
        `;

      default:
        return "<p>Formulário de edição não disponível para este tipo.</p>";
    }
  }

  // Função para salvar edições
  function saveEdit() {
    const type = this.getAttribute("data-type");
    const id = this.getAttribute("data-id");
    const form = document.querySelector(".edit-form");
    const formData = new FormData(form);

    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Salvando...';

    if (type === "categoria" || type === "colecao") {
      const endpoint = type === "categoria" ? "processa_categoria.php" : "processa_colecao.php";
      
      formData.append("id", id);
      formData.append("action", "update");

      fetch(endpoint, {
        method: "POST",
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          showNotification(`${type === "categoria" ? "Categoria" : "Coleção"} atualizada com sucesso!`);
          if (type === "categoria") loadCategorias();
          else loadColecoes();
          closeModal();
        } else {
          showNotification("Erro: " + data.message);
        }
      })
      .catch(error => showNotification("Erro: " + error))
      .finally(() => {
        this.disabled = false;
        this.innerHTML = "Salvar Alterações";
      });
    } else {
      // Simulação de salvamento para outros tipos
      setTimeout(() => {
        showNotification(
          `${type.charAt(0).toUpperCase() + type.slice(1)} ${id} atualizado com sucesso!`
        );
        closeModal();
      }, 1500);
    }
  }

  // Edição de clientes
  document.querySelectorAll(".clientes-table .btn-view").forEach((btn) => {
    btn.addEventListener("click", function () {
      const userId = this.getAttribute("data-id");
      openEditModal("cliente", userId);
    });
  });



  // Edição de produtos
  document.querySelectorAll(".produto-actions .editar").forEach((btn) => {
    btn.addEventListener("click", function () {
      const produtoId =
        this.closest(".produto-card").querySelector("h3").textContent;
      openEditModal("produto", produtoId);
    });
  });

  // Edição de cupons
  document.querySelectorAll(".cupom-actions .editar").forEach((btn) => {
    btn.addEventListener("click", function () {
      const cupomCodigo =
        this.closest(".cupom-card").querySelector(".cupom-codigo").textContent;
      openEditModal("cupom", cupomCodigo);
    });
  });

  // Edição de itens de conteúdo
  document.querySelectorAll(".item-actions .editar").forEach((btn) => {
    btn.addEventListener("click", function () {
      const itemId = this.closest(".item-card").querySelector("h4").textContent;
      openEditModal("conteudo", itemId);
    });
  });

  // Controle de estoque
  document.querySelectorAll(".estoque-actions .fa-minus").forEach((btn) => {
    btn.addEventListener("click", function () {
      const stockElement =
        this.closest(".produto-estoque").querySelector("span");
      let stock = parseInt(stockElement.textContent.replace("Estoque: ", ""));
      if (stock > 0) {
        stockElement.textContent = `Estoque: ${stock - 1}`;
      }
    });
  });

  // Filtro de clientes
  const clientSearch = document.getElementById("client-search");
  const clientStatusFilter = document.getElementById("client-status-filter");

  if (clientSearch && clientStatusFilter) {
    clientSearch.addEventListener("input", filterClients);
    clientStatusFilter.addEventListener("change", filterClients);

    function filterClients() {
      const status = clientStatusFilter.value;
      const searchTerm = clientSearch.value.toLowerCase();
      const rows = document.querySelectorAll(".clientes-table tbody tr");

      rows.forEach((row) => {
        const rowStatus = row
          .querySelector(".status-badge")
          .classList.contains("ativo")
          ? "ativo"
          : "desativado";
        const rowType = row
          .querySelector('td[data-label="Tipo"]')
          .textContent.toLowerCase();
        const rowText = row.textContent.toLowerCase();

        const statusMatch =
          status === "all" ||
          (status === "admin" && rowType.includes("admin")) ||
          (status !== "admin" && rowStatus === status);
        const searchMatch = searchTerm === "" || rowText.includes(searchTerm);

        if (statusMatch && searchMatch) {
          row.style.display = "";
        } else {
          row.style.display = "none";
        }
      });
    }
  }

  // =============================================
  // CÓDIGO PARA CATEGORIAS E COLEÇÕES
  // =============================================

  // Carregar categorias
  function loadCategorias() {
    fetch('ger_categorias.php')
      .then(response => response.text())
      .then(html => {
        const grid = document.getElementById("categorias-grid");
        if (grid) grid.innerHTML = html;
        setupCategoriaActions();
      })
      .catch(error => console.error('Erro ao carregar categorias:', error));
  }

  // Carregar coleções
  function loadColecoes() {
    fetch('ger_colecoes.php')
      .then(response => response.text())
      .then(html => {
        const grid = document.getElementById("colecoes-grid");
        if (grid) grid.innerHTML = html;
        setupColecaoActions();
      })
      .catch(error => console.error('Erro ao carregar coleções:', error));
  }

  // Configurar ações para categorias
  function setupCategoriaActions() {
    // Botões de remover
    document.querySelectorAll("#categorias-grid .remover").forEach(btn => {
      btn.addEventListener("click", function() {
        const id = this.getAttribute("data-id");
        if (confirm("Tem certeza que deseja remover esta categoria?")) {
          fetch(`processa_categoria.php?action=remove&id=${id}`)
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                this.closest(".item-card").remove();
                showNotification("Categoria removida com sucesso!");
              } else {
                showNotification("Erro: " + data.message);
              }
            })
            .catch(error => {
              showNotification("Erro ao remover categoria: " + error);
            });
        }
      });
    });

    // Botões de editar
    document.querySelectorAll("#categorias-grid .editar").forEach(btn => {
      btn.addEventListener("click", function() {
        const id = this.getAttribute("data-id");
        const nome = this.closest(".item-card").querySelector("h4").textContent;
        openEditModal("categoria", id, { nome });
      });
    });
  }

  // Configurar ações para coleções
  function setupColecaoActions() {
    // Botões de remover
    document.querySelectorAll("#colecoes-grid .remover").forEach(btn => {
      btn.addEventListener("click", function() {
        const id = this.getAttribute("data-id");
        if (confirm("Tem certeza que deseja remover esta coleção?")) {
          fetch(`processa_colecao.php?action=remove&id=${id}`)
            .then(response => response.json())
            .then(data => {
              if (data.success) {
                this.closest(".item-card").remove();
                showNotification("Coleção removida com sucesso!");
              } else {
                showNotification("Erro: " + data.message);
              }
            })
            .catch(error => {
              showNotification("Erro ao remover coleção: " + error);
            });
        }
      });
    });

    // Botões de editar
    document.querySelectorAll("#colecoes-grid .editar").forEach(btn => {
      btn.addEventListener("click", function() {
        const id = this.getAttribute("data-id");
        const nome = this.closest(".item-card").querySelector("h4").textContent;
        openEditModal("colecao", id, { nome });
      });
    });
  }

  // Adicionar formulários
  document.getElementById("form-categoria")?.addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

    fetch("processa_categoria.php?action=add", {
      method: "POST",
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showNotification("Categoria adicionada com sucesso!");
        this.reset();
        loadCategorias();
      } else {
        showNotification("Erro: " + data.message);
      }
    })
    .catch(error => showNotification("Erro: " + error))
    .finally(() => {
      btn.disabled = false;
      btn.textContent = "Adicionar Categoria";
    });
  });

  document.getElementById("form-colecao")?.addEventListener("submit", function(e) {
    e.preventDefault();
    const formData = new FormData(this);
    const btn = this.querySelector('button[type="submit"]');
    
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Enviando...';

    fetch("processa_colecao.php?action=add", {
      method: "POST",
      body: formData
    })
    .then(response => response.json())
    .then(data => {
      if (data.success) {
        showNotification("Coleção adicionada com sucesso!");
        this.reset();
        loadColecoes();
      } else {
        showNotification("Erro: " + data.message);
      }
    })
    .catch(error => showNotification("Erro: " + error))
    .finally(() => {
      btn.disabled = false;
      btn.textContent = "Adicionar Coleção";
    });
  });

  // Carregar categorias e coleções quando a página for carregada
  if (document.getElementById("categorias-grid")) {
    loadCategorias();
  }

  if (document.getElementById("colecoes-grid")) {
    loadColecoes();
  }

  // Inicializa os eventos quando a página carrega
  if (document.getElementById("carrossel-grid")) {
    loadCarrosselImages();
  }

  // Manipula o botão voltar/avançar do navegador
  window.addEventListener("popstate", function () {
    const urlParams = new URLSearchParams(window.location.search);
    const activeTab = urlParams.get("tab") || "pedidos";
    document
      .querySelectorAll(".category-tab")
      .forEach((t) => t.classList.remove("active"));
    document
      .querySelectorAll(".admin-section")
      .forEach((s) => s.classList.remove("active"));

    const activeTabElement = document.querySelector(
      `.category-tab[data-category="${activeTab}"]`
    );
    if (activeTabElement) {
      activeTabElement.classList.add("active");
      document.getElementById(`${activeTab}-section`).classList.add("active");
    }
  });
});
