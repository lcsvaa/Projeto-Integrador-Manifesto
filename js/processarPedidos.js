document.addEventListener('DOMContentLoaded', () => {
  const pedidosList = document.querySelector('.pedidos-list');

  if (!pedidosList) {
    console.error('Elemento .pedidos-list não encontrado');
    return;
  } else {
    console.log('.pedidos-list carregado');
  }

  pedidosList.addEventListener('click', async (event) => {

    const btn = event.target.closest('.btn-status');

    if (!btn) return;

    const idPedido = btn.dataset.id;
    const novoStatus = btn.dataset.status;

    console.log('Botão clicado:', btn);
    console.log('idPedido:', idPedido);
    console.log('novoStatus:', novoStatus);

    if (!idPedido || !novoStatus) {
      console.error('Dados do botão incompletos');
      return;
    }

    try {
      console.log('Enviando requisição para atualizar status...');
      const response = await fetch('atualizar_status_pedido.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ idPedido, novoStatus }),
      });

      const data = await response.json();
      console.log('Resposta do servidor:', data);

      if (data.status === 'ok') {
        const pedidoCard = btn.closest('.pedido-card');
        const statusSpan = pedidoCard.querySelector('.pedido-status');

        if (statusSpan) {
          statusSpan.textContent = novoStatus;

          const statusClasses = {
            'Processando pagamento': 'pending',
            'Pago': 'processing',
            'Preparando pra enviar': 'processing',
            'Enviado': 'shipped',
            'Recebido': 'delivered',
            'Cancelado': 'cancelled'
          };
          statusSpan.className = 'pedido-status ' + (statusClasses[novoStatus] || '');

          console.log('Status visual atualizado');
        }

        btn.closest('.pedido-actions').innerHTML = `<span class="status-info">Atualizado para "${novoStatus}".</span>`;

        alert('Status atualizado com sucesso!');
      } else {
        alert('Erro ao atualizar: ' + (data.msg || 'Erro desconhecido'));
        console.error(data);
      }
    } catch (error) {
      console.error('Erro na requisição:', error);
      alert('Erro ao atualizar o status. Verifique o console.');
    }
  });
});
