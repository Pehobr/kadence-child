document.addEventListener('DOMContentLoaded', function () {

    /**
     * Funkce pro obsluhu hlavních záložek (Evangelisté, Překlady, atd.).
     */
    function setupMainTabs() {
        const viewSwitcher = document.querySelector('.view-switcher');
        if (!viewSwitcher) {
            return;
        }

        const mainTabs = viewSwitcher.querySelectorAll('.nav-tab[data-target]');
        const tabContents = document.querySelectorAll('.tab-content');

        mainTabs.forEach(tab => {
            tab.addEventListener('click', function (event) {
                event.preventDefault();

                // Deaktivace všech tlačítek a aktivace kliknutého
                mainTabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                // Získání ID cílového obsahu
                const targetId = this.getAttribute('data-target');
                const targetContent = document.getElementById(targetId);

                if (targetContent) {
                    // Skrytí všech obsahů a zobrazení cílového
                    tabContents.forEach(content => content.classList.remove('active'));
                    targetContent.classList.add('active');
                } else {
                    console.error('Cílový obsah nebyl nalezen pro ID:', targetId);
                }
            });
        });
    }

    /**
     * Funkce pro obsluhu přepínání podzáložek (např. evangelistů v Exegezi).
     * Funguje pro jakýkoli přepínač s třídou .evangelist-switcher.
     */
    function setupEvangelistSwitchers() {
        const switchers = document.querySelectorAll('.evangelist-switcher');

        switchers.forEach(switcher => {
            const buttons = switcher.querySelectorAll('.nav-tab[data-target]');
            // Předpokládáme, že kontejner s obsahem je hned další element po přepínači
            const contentContainer = switcher.nextElementSibling;

            if (!contentContainer) {
                console.error('Chybí kontejner s obsahem pro přepínač:', switcher);
                return;
            }

            buttons.forEach(button => {
                button.addEventListener('click', function (event) {
                    event.preventDefault();

                    const targetSelector = this.getAttribute('data-target');
                    const targetContent = document.querySelector(targetSelector);

                    if (!targetContent) {
                        console.error('Cílový obsah nebyl nalezen pro selektor:', targetSelector);
                        return;
                    }

                    // Deaktivace všech tlačítek v rámci tohoto přepínače
                    buttons.forEach(btn => btn.classList.remove('active'));
                    // Aktivace kliknutého tlačítka
                    this.classList.add('active');

                    // Skrytí veškerého obsahu v příslušném kontejneru
                    const allContentPanes = contentContainer.querySelectorAll('.evangelist-translation-content, .exegesis-content, .spiritual-content');
                    allContentPanes.forEach(content => content.classList.remove('active'));

                    // Zobrazení cílového obsahu
                    targetContent.classList.add('active');
                });
            });
        });
    }

    /**
     * Funkce pro kopírování textu do schránky s vizuální zpětnou vazbou.
     */
    function setupCopyToClipboard() {
        const copyButtons = document.querySelectorAll('.copy-to-clipboard-btn');

        copyButtons.forEach(button => {
            button.addEventListener('click', function() {
                const targetSelector = this.getAttribute('data-clipboard-target');
                const targetElement = document.querySelector(targetSelector);

                if (targetElement && navigator.clipboard) {
                    navigator.clipboard.writeText(targetElement.innerText.trim()).then(() => {
                        const originalTitle = this.title;
                        const icon = this.querySelector('i');
                        const originalIconClass = icon.className;

                        this.title = 'Zkopírováno!';
                        icon.className = 'fa fa-check';

                        setTimeout(() => {
                            this.title = originalTitle;
                            icon.className = originalIconClass;
                        }, 2000);
                    }).catch(err => {
                        console.error('Nepodařilo se zkopírovat text: ', err);
                        alert('Kopírování se nezdařilo. Zkuste to prosím ručně.');
                    });
                }
            });
        });
    }

    // Spuštění všech inicializačních funkcí po načtení stránky
    setupMainTabs();
    setupEvangelistSwitchers();
    setupCopyToClipboard();

});

