        // Menu Hamburguer
        const hamburgerMenu = document.getElementById('hamburger-menu');
        const navCenter = document.getElementById('nav-center');
        const navRight = document.getElementById('nav-right');
        const body = document.body;

        hamburgerMenu.addEventListener('click', () => {
            hamburgerMenu.classList.toggle('active');
            navCenter.classList.toggle('open');
            navRight.classList.toggle('open');
            body.classList.toggle('menu-open');
            
            if (hamburgerMenu.classList.contains('active')) {
                document.querySelectorAll('.hamburger-line').forEach((line, index) => {
                    if (index === 0) line.style.transform = 'translateY(8px) rotate(45deg)';
                    if (index === 1) line.style.opacity = '0';
                    if (index === 2) line.style.transform = 'translateY(-8px) rotate(-45deg)';
                });
            } else {
                document.querySelectorAll('.hamburger-line').forEach(line => {
                    line.style.transform = '';
                    line.style.opacity = '';
                });
            }
        });

        // Tabs de Pedidos
        const tabs = document.querySelectorAll('.order-tab');
        const orderLists = document.querySelectorAll('.order-list');
        
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                tabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                tab.classList.add('active');
                
                // Hide all order lists
                orderLists.forEach(list => list.classList.remove('active'));
                // Show the selected order list
                const tabId = tab.getAttribute('data-tab');
                document.getElementById(`${tabId}-orders`).classList.add('active');
            });
        });