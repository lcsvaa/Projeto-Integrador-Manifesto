// Elementos do DOM
const hamburgerMenu = document.getElementById('hamburger-menu');
const navCenter = document.getElementById('nav-center');
const navRight = document.getElementById('nav-right');
const body = document.body;
const authTabs = document.querySelectorAll('.auth-tab');
const authForms = document.querySelectorAll('.auth-form');
const togglePasswordIcons = document.querySelectorAll('.toggle-password');
const cpfInput = document.getElementById('register-cpf');
const cpfError = document.getElementById('cpf-error');

// Menu Hamburguer
function toggleMenu() {
    hamburgerMenu.classList.toggle('active');
    navCenter.classList.toggle('open');
    navRight.classList.toggle('open');
    body.classList.toggle('menu-open');
    
    const lines = document.querySelectorAll('.hamburger-line');
    if (hamburgerMenu.classList.contains('active')) {
        lines.forEach((line, index) => {
            if (index === 0) line.style.transform = 'translateY(8px) rotate(45deg)';
            if (index === 1) line.style.opacity = '0';
            if (index === 2) line.style.transform = 'translateY(-8px) rotate(-45deg)';
        });
    } else {
        lines.forEach(line => {
            line.style.transform = '';
            line.style.opacity = '';
        });
    }
}

// Tabs de Login/Cadastro
function switchAuthTab(tab) {
    authTabs.forEach(t => t.classList.remove('active'));
    tab.classList.add('active');
    
    authForms.forEach(form => form.classList.remove('active'));
    const tabId = tab.getAttribute('data-tab');
    document.getElementById(`${tabId}-form`).classList.add('active');
}

// Toggle Password Visibility
function togglePasswordVisibility(icon) {
    const targetId = icon.getAttribute('data-target');
    const passwordInput = document.getElementById(targetId);
    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
    passwordInput.setAttribute('type', type);
    
    icon.classList.toggle('fa-eye');
    icon.classList.toggle('fa-eye-slash');
}

// Máscara para CPF
function formatCPF(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.length > 11) {
        value = value.substring(0, 11);
    }
    
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d)/, '$1.$2');
    value = value.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
    
    input.value = value;
}

// Validação de CPF
function validateCPF(cpf) {
    cpf = cpf.replace(/[^\d]+/g,'');
    
    if(cpf == '') return false;
    
    // Elimina CPFs inválidos conhecidos
    if (cpf.length != 11 || 
        cpf == "00000000000" || 
        cpf == "11111111111" || 
        cpf == "22222222222" || 
        cpf == "33333333333" || 
        cpf == "44444444444" || 
        cpf == "55555555555" || 
        cpf == "66666666666" || 
        cpf == "77777777777" || 
        cpf == "88888888888" || 
        cpf == "99999999999") {
        return false;
    }
    
    // Valida 1º dígito
    let add = 0;
    for (let i = 0; i < 9; i++) {
        add += parseInt(cpf.charAt(i)) * (10 - i);
    }
    let rev = 11 - (add % 11);
    if (rev == 10 || rev == 11) rev = 0;
    if (rev != parseInt(cpf.charAt(9))) return false;
    
    // Valida 2º dígito
    add = 0;
    for (let i = 0; i < 10; i++) {
        add += parseInt(cpf.charAt(i)) * (11 - i);
    }
    rev = 11 - (add % 11);
    if (rev == 10 || rev == 11) rev = 0;
    if (rev != parseInt(cpf.charAt(10))) return false;
    
    return true;
}

function handleCPFBlur() {
    if (this.value.length === 0) {
        cpfError.style.display = 'none';
        return;
    }
    
    const cpfValido = validateCPF(this.value);
    cpfError.style.display = cpfValido ? 'none' : 'block';
    this.classList.toggle('invalid', !cpfValido);
}

// Login com JSON
async function handleLogin(e) {
    // e.preventDefault();
    
    const email = document.getElementById('login-email').value;
    const password = document.getElementById('login-password').value;
    
    if (!email || !password) {
        alert('Por favor, preencha todos os campos.');
        return;
    }

    try {
        const response = await fetch('users.json');
        const users = await response.json();
        const user = users.find(u => u.email === email && u.password === password);
        
        if (user) {
             sessionStorage.setItem('currentUser', JSON.stringify(user));
             window.location.href = user.is_admin ? 'admin.php' : 'profile.php';
         } else {
             alert('Credenciais inválidas!');
         }
    } catch (error) {
        console.error('Erro:', error);
        alert('Erro ao fazer login');
    }
}

// Validação de Cadastro
function handleRegister(e) {
    // e.preventDefault();
    
    const name = document.getElementById('register-name').value;
    const email = document.getElementById('register-email').value;
    const cpf = document.getElementById('register-cpf').value;
    const password = document.getElementById('register-password').value;
    const confirmPassword = document.getElementById('register-confirm').value;
    const termsAccepted = document.getElementById('accept-terms').checked;
    
    // Validações
    if (!name || !email || !cpf || !password || !confirmPassword) {
        alert('Por favor, preencha todos os campos.');
        return;
    }
    
    if (!validateCPF(cpf)) {
        cpfError.style.display = 'block';
        cpfInput.classList.add('invalid');
        alert('Por favor, insira um CPF válido.');
        return;
    }
    
    if (password !== confirmPassword) {
        alert('As senhas não coincidem.');
        return;
    }
    
    if (password.length < 6) {
        alert('A senha deve ter pelo menos 6 caracteres.');
        return;
    }
    
    if (!termsAccepted) {
        alert('Você deve aceitar os termos e condições.');
        return;
    }
    
    // Simulação de cadastro (em um projeto real, enviaria para o servidor)
    console.log('Cadastro realizado:', { name, email, cpf, password });
    alert('Cadastro realizado com sucesso! Agora você pode fazer login.');
    document.querySelector('.auth-tab[data-tab="login"]').click();
}

// Modal de recuperação
function checkRecoveryModal() {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('recovery')) {
        document.getElementById('recovery-modal').style.display = 'block';
        document.body.style.overflow = 'hidden';
    }
}

// Event Listeners
hamburgerMenu.addEventListener('click', toggleMenu);
authTabs.forEach(tab => tab.addEventListener('click', () => switchAuthTab(tab)));
togglePasswordIcons.forEach(icon => {
    icon.addEventListener('click', () => togglePasswordVisibility(icon));
});
cpfInput.addEventListener('input', () => formatCPF(cpfInput));
cpfInput.addEventListener('blur', handleCPFBlur);
// document.getElementById('login-form').addEventListener('submit', handleLogin);
document.getElementById('register-form').addEventListener('submit', handleRegister);

// Inicialização
checkRecoveryModal();