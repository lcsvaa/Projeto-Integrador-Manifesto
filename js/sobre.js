        // Seu JavaScript existente para o menu hamburguer
        const hamburgerMenu = document.querySelector('.hamburger-menu');
        const navCenter = document.querySelector('.nav-center');
        const navRight = document.querySelector('.nav-right');
        const body = document.body;

        hamburgerMenu.addEventListener('click', () => {
            hamburgerMenu.classList.toggle('active');
            navCenter.classList.toggle('open');
            navRight.classList.toggle('open');
            body.classList.toggle('menu-open');
        });