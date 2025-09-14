document.addEventListener('DOMContentLoaded', carregarNovidades);

async function carregarNovidades() {
  const grid = document.getElementById('novidades-grid');

  try {
    const resp  = await fetch('api_novidades/listar_novidades.php');
    const posts = await resp.json();

    posts.forEach(post => {
      const card   = criarCard(post);
      const modal  = criarModal(post);

      grid.appendChild(card);
      document.body.appendChild(modal);
    });

    inicializarModais();
  } catch (err) {
    console.error(err);
    grid.innerHTML = '<p>Não foi possível carregar as novidades.</p>';
  }
}

function criarCard(p) {
  const card = document.createElement('div');
  card.className = 'blog-card';

  const modalId  = `modal-${p.idNovidade}`;

  card.innerHTML = `
    <img src="uploads/novidades/${p.imagemNovidade}" alt="${p.titulo}" loading="lazy">
    <div class="blog-content">
        <div class="blog-date">${formataData(p.dataNovidade)}</div>
        <h3 class="blog-title">${p.titulo}</h3>
        <p class="blog-excerpt">${resume(p.conteudo, 120)}…</p>
        <a href="#" class="read-more" data-modal="${modalId}">Ler mais →</a>
    </div>
  `;
  return card;
}

function criarModal(p) {
  const modal = document.createElement('div');
  modal.className = 'blog-modal';
  modal.id        = `modal-${p.idNovidade}`;
  modal.style.display = 'none';

  modal.innerHTML = `
    <div class="modal-content">
      <h2>${p.titulo}</h2>
      <span class="close-modal">&times;</span>
      <img src="uploads/novidades/${p.imagemNovidade}" alt="${p.titulo}">
      <div class="blog-date">${formataData(p.dataNovidade)}</div>
      
      <p>${p.conteudo}</p>
    </div>
  `;
  return modal;
}

function formataData(iso) {
  const meses = [
    'JANEIRO','FEVEREIRO','MARÇO','ABRIL','MAIO','JUNHO',
    'JULHO','AGOSTO','SETEMBRO','OUTUBRO','NOVEMBRO','DEZEMBRO'
  ];
  const [ano, mes, dia] = iso.split('-');
  return `${dia.padStart(2,'0')} ${meses[+mes-1]} de ${ano}`;
}

const resume = (txt, n) => (txt.length > n ? txt.slice(0, n) : txt);


function inicializarModais() {
  const readMoreLinks = document.querySelectorAll('.read-more');
  readMoreLinks.forEach(link => {
    link.addEventListener('click', function (e) {
      e.preventDefault();
      const modal = document.getElementById(this.dataset.modal);
      modal.style.display = 'block';
      document.body.style.overflow = 'hidden';
    });
  });

  const closeButtons = document.querySelectorAll('.close-modal');
  closeButtons.forEach(btn => {
    btn.addEventListener('click', function () {
      const modal = this.closest('.blog-modal');
      modal.style.display = 'none';
      document.body.style.overflow = 'auto';
    });
  });

  const modals = document.querySelectorAll('.blog-modal');
  modals.forEach(modal => {
    modal.addEventListener('click', function (e) {
      if (e.target === this) {
        this.style.display = 'none';
        document.body.style.overflow = 'auto';
      }
    });
  });

  document.addEventListener('keydown', function (e) {
    if (e.key === 'Escape') {
      modals.forEach(m => {
        if (m.style.display === 'block') {
          m.style.display = 'none';
          document.body.style.overflow = 'auto';
        }
      });
    }
  });
}
