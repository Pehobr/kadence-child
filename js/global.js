document.addEventListener('DOMContentLoaded', function() {
    /**
     * Aktivní stav pro ikony ve spodní mobilní liště.
     *
     * Tento skript porovnává aktuální URL stránky s cílem každé ikony v liště.
     * Pokud se shodují, přidá ikoně třídu 'active', která ji vizuálně zvýrazní.
     */
    const bottomBarLinks = document.querySelectorAll('.mobile-bottom-bar .mobile-nav-icon');
    if (!bottomBarLinks.length) {
        return;
    }

    // Získáme aktuální cestu a normalizujeme ji (odstraníme koncovou / pokud existuje a není to root).
    const currentLocation = new URL(window.location.href);
    let currentPath = currentLocation.pathname;
    if (currentPath.length > 1 && currentPath.endsWith('/')) {
        currentPath = currentPath.slice(0, -1);
    }

    bottomBarLinks.forEach(link => {
        const linkLocation = new URL(link.href);
        let linkPath = linkLocation.pathname;

        // Normalizujeme cestu odkazu.
        if (linkPath.length > 1 && linkPath.endsWith('/')) {
            linkPath = linkPath.slice(0, -1);
        }

        // Pokud se normalizované cesty shodují, přidáme třídu 'active'.
        if (currentPath === linkPath) {
            link.classList.add('active');
        }
    });
});