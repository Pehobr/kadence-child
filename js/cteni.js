jQuery(document).ready(function($) {

    /**
     * Univerzální funkce pro přepínání záložek na stránce "Detail Čtení".
     * @param {string} navSelector - Selektor pro navigační prvky (např. '.tabs-nav').
     */
    function initializeTabs(navSelector) {
        var $navs = $(navSelector);

        $navs.each(function() {
            var $nav = $(this);
            var $links = $nav.find('a');
            // Najde nejbližší rodičovský kontejner a v něm wrapper pro obsah
            var $contentWrapper = $nav.next('.tab-content-wrapper, .sub-tab-content-wrapper');

            $links.on('click', function(e) {
                e.preventDefault();
                var target = $(this).attr('href');

                // Změní aktivní stav v rámci aktuální sady záložek
                $nav.find('li').removeClass('active');
                $(this).parent().addClass('active');

                // Zobrazí/skryje správný obsah
                $contentWrapper.children().removeClass('active');
                $(target).addClass('active');
            });
        });
    }

    // Spustíme funkci pro hlavní záložky
    initializeTabs('.tabs-nav');

    // Spustíme funkci pro vnořené pod-záložky
    initializeTabs('.sub-tabs-nav');

});
