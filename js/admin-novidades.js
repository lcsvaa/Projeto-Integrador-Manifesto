document.addEventListener('DOMContentLoaded', () => {
  
  const form  = document.getElementById('form-novidade');
  const grid  = document.getElementById('novidades-grid');
  const btn   = form.querySelector('.btn-submit');

  let editingId = null;

  async function loadNovidades() {
    try {
      const resp = await fetch('api_novidades/listar_novidades.php');
      const data = await resp.json();

      grid.innerHTML = '';                       
      data.forEach(renderCard);                  
    } catch (err) {
      console.error(err);
      grid.innerHTML = '<p>Erro ao carregar novidades.</p>';
    }
  }

  function formatarDataParaTexto(dataISO) {
  const meses = [
    'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho',
    'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'
  ];

  const [ano, mes, dia] = dataISO.split('-');
  const nomeMes = meses[parseInt(mes, 10) - 1];

  return `${dia} ${nomeMes} de ${ano}`;
  }
  
  function renderCard(item) {
    const card = document.createElement('div');
    card.className = 'item-card';

    card.innerHTML = `
      <img src="uploads/novidades/${item.imagemNovidade}" alt="${item.titulo}">
      <div class="card-body">
        <h4>${item.titulo}</h4>
        <small class="item-date">${formatarDataParaTexto(item.dataNovidade)}</small>
        <div class="card-actions item-actions">
          <button class="btn-edit btn-action editar"><i class="fas fa-edit"></i> Editar</button>
          <button class="btn-delete btn-action remover"><i class="fas fa-trash"></i> Remover</button>
        </div>
      </div>
    `;

    card.querySelector('.btn-edit').onclick   = () => startEdit(item);
    card.querySelector('.btn-delete').onclick = () => deleteNovidade(item.idNovidade);

    grid.appendChild(card);
  }

  function startEdit(item) {
    editingId        = item.idNovidade;
    form.titulo.value   = item.titulo;
    form.data.value     = item.dataNovidade;
    form.conteudo.value = item.conteudo;
    

    btn.textContent = 'Salvar Alterações';
    form.scrollIntoView({ behavior: 'smooth' });
  }

  async function deleteNovidade(id) {
    if (!confirm('Confirma excluir esta novidade?')) return;

    const fd = new FormData();
    fd.append('idNovidade', id);

    try {
      const resp  = await fetch('api_novidades/remover_novidades.php', {
        method: 'POST',
        body: fd
      });
      const json = await resp.json();
      if (!json.success) throw new Error(json.message || 'Falha ao remover');

      loadNovidades();
    } catch (err) {
      alert(err.message);
    }
  }

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    const fd  = new FormData(form);
    let   url = 'api_novidades/adicionar_novidades.php';
    

    if (editingId) {          
      fd.append('idNovidade', editingId);
      url = 'api_novidades/editar_novidades.php';
    }

    try {
      const resp  = await fetch(url, { method: 'POST', body: fd });
      const json  = await resp.json();
      if (!json.success) throw new Error(json.message || 'Falha ao salvar');
      if (!editingId) {
        fetch('newsletter/cadastrar_newsletter.php?id=' + json.id, {
          method: 'POST'
        });
      }

      form.reset();
      editingId = null;
      btn.textContent = 'Publicar Postagem';
      loadNovidades();
    } catch (err) {
      alert(err.message);
    }
  });

  loadNovidades();
});
