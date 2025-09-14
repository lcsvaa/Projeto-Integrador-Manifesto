function carregarCategoriasFiltro() {
  $.ajax({
    url: 'listarCategorias.php',
    type: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.status === 'success') {
        const select = $('#produto-categoria');
        select.empty();
        select.append('<option value="">Selecione</option>');

        res.categorias.forEach(categoria => {
          select.append(`<option value="${categoria.id}">${categoria.ctgNome}</option>`);
        });
      } else {
        alert('Erro ao carregar categorias: ' + res.message);
      }
    },
    error: function() {
      alert('Erro ao buscar categorias.');
    }
  });
}

function carregarCategorias() {
  $.ajax({
    url: 'listarCategorias.php',
    type: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.status === 'success') {
        const select = $('#produto-categoria-form');
        select.empty();
        select.append('<option value="">Selecione</option>');

        res.categorias.forEach(categoria => {
          select.append(`<option value="${categoria.id}">${categoria.ctgNome}</option>`);
        });
      } else {
        alert('Erro ao carregar categorias: ' + res.message);
      }
    },
    error: function() {
      alert('Erro ao buscar categorias.');
    }
  });
}

// Chame essa função ao carregar a página
$(document).ready(function() {
  
  carregarCategoriasFiltro();
  carregarCategorias();
  
});