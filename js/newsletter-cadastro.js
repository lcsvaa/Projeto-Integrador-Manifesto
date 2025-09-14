document.addEventListener('DOMContentLoaded', () => {
  const form     = document.getElementById('newsletter-form');
  const feedback = document.getElementById('newsletter-feedback');

  form.addEventListener('submit', async (e) => {
    e.preventDefault();

    feedback.style.textAlign = 'center';
    feedback.textContent = 'Enviando…';
    feedback.style.color = '';          
    clearTimeout(feedback._timer);      

    try {
      const formData = new FormData(form);
      const resp     = await fetch(form.action, { method: 'POST', body: formData });
      const data     = await resp.json();

      if (!resp.ok || !data.ok) throw new Error(data.message || 'Erro desconhecido');

      feedback.textContent = data.message;  
      feedback.style.color = '#c2185b';
      form.reset();
    } catch (err) {
      feedback.textContent = err.message;
      feedback.style.color = 'red';
    }

    feedback._timer = setTimeout(() => {
      feedback.textContent = '';
    }, 3000); 
  });
});

