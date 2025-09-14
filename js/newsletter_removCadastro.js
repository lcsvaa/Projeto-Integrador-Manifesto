document.addEventListener('DOMContentLoaded', () => {
  const form     = document.getElementById('remove-newsletter-form');
  const feedback = document.getElementById('remove-feedback');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();              
    feedback.style.display = 'none'; 

    try {
      const resp = await fetch(form.action, {
        method : 'POST',
        body   : new FormData(form)
      });

      const data = await resp.json();     

      feedback.textContent = data.message; 
      feedback.style.color = data.ok ? '#c2185b' : 'red';

      if (data.ok) {
        form.reset();                     
      }

    } catch (err) {
      feedback.textContent = 'Erro ao conectar ao servidor.';
      feedback.style.color = 'red';
    } finally {
      feedback.style.display = 'block';

      
      setTimeout(() => {
        feedback.style.display = 'none';
      }, 5000);
    }
  });
});
