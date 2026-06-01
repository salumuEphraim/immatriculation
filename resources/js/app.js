import './bootstrap';

// Importation de Bootstrap et de ses dépendances (Popper.js)
import 'bootstrap'; 

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

const navbar = document.getElementById('mainAppNavbar');
if (navbar) {
    let lastY = window.scrollY;
    window.addEventListener('scroll', () => {
        const y = window.scrollY;
        navbar.classList.toggle('navbar-scrolled', y > 12);

        if (y > lastY && y > 120) {
            navbar.classList.add('navbar-hidden');
        } else {
            navbar.classList.remove('navbar-hidden');
        }
        lastY = y;
    }, { passive: true });
}