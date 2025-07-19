document.addEventListener('DOMContentLoaded', function () {

    // --- Logika pro přepínání VŠECH záložek na stránce detailu příběhu ---

    function initializeTabSwitcher(switcherSelector, contentSelector, dataAttribute) {
        const tabs = document.querySelectorAll(switcherSelector);
        const contents = document.querySelectorAll(contentSelector);
        
        if (tabs.length === 0) return;

        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Deaktivace všech tlačítek a obsahů v dané skupině
                tabs.forEach(t => t.classList.remove('active'));
                contents.forEach(c => c.classList.remove('active'));

                // Aktivace kliknutého tlačítka a příslušného obsahu
                tab.classList.add('active');
                const targetId = tab.dataset[dataAttribute.replace('data-', '')];
                const targetContent = document.getElementById(targetId);
                
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    }

    // Hlavní záložky (Evangelisté, Překlady, Exegeze...)
    initializeTabSwitcher('.story-detail .view-switcher .nav-tab', '.story-detail > .tab-content', 'data-target');
    
    // Pod-záložky (jednotliví evangelisté v překladech)
    initializeTabSwitcher('#translations-view .evangelist-switcher .nav-tab', '.evangelist-translation-content', 'data-evangelist');

    // Pod-záložky (jednotliví evangelisté v exegezi)
    initializeTabSwitcher('#analysis-view .exegesis-switcher .nav-tab', '.exegesis-content', 'data-exegesis-target');

    // Pod-záložky (jednotliví evangelisté ve výkladu)
    initializeTabSwitcher('#spiritual-view .spiritual-switcher .nav-tab', '.spiritual-content', 'data-spiritual-target');


    // --- Funkce pro kopírování do schránky ---
    const copyButtons = document.querySelectorAll('.copy-to-clipboard-btn');

    copyButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetSelector = button.dataset.clipboardTarget;
            const contentElement = document.querySelector(targetSelector);

            if (contentElement) {
                const textToCopy = contentElement.innerText;

                navigator.clipboard.writeText(textToCopy).then(() => {
                    // Vizuální zpětná vazba pro uživatele
                    const originalIcon = button.innerHTML;
                    button.innerHTML = '✓ Zkopírováno';
                    button.classList.add('copied');

                    // Vrátí tlačítko do původního stavu po 2 sekundách
                    setTimeout(() => {
                        button.innerHTML = originalIcon;
                        button.classList.remove('copied');
                    }, 2000);
                }).catch(err => {
                    console.error('Chyba při kopírování textu: ', err);
                    alert('Chyba při kopírování. Zkuste to prosím znovu.');
                });
            } else {
                 console.warn('Element ke zkopírování nebyl nalezen:', targetSelector);
            }
        });
    });
});