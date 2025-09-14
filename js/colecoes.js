function carregarColecoes() {
  $.ajax({
    url: 'listarColecoes.php',
    type: 'GET',
    dataType: 'json',
    success: function(res) {
      if (res.status === 'success') {
        const select = $('#produto-colecao');
        select.empty();
        select.append('<option value="">Selecione</option>');

        res.colecoes.forEach(colecao => {
          select.append(`<option value="${colecao.id}">${colecao.colecaoNome}</option>`);
        });
      } else {
        alert('Erro ao carregar coleções: ' + res.message);
      }
    },
    error: function() {
      alert('Erro ao buscar coleções.');
    }
  });
}


$(document).ready(function() {
  carregarColecoes();
});