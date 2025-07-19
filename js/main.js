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
                    // Jakmile je záložka viditelná, zkusíme inicializovat přehrávač.
                    // Funkce je napsaná tak, že nevadí, když se zavolá víckrát.
                    initializeCustomAudioPlayers();
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

    // --- Funkce pro vytvoření vlastního audio přehrávače ---
    function initializeCustomAudioPlayers() {
        const playerContainers = document.querySelectorAll('.podcast-player-container');

        playerContainers.forEach(container => {
            // Zkontrolujeme, zda jsme pro tento kontejner již přehrávač nevytvořili.
            if (container.dataset.playerInitialized) {
                return;
            }

            const originalAudio = container.querySelector('audio');
            if (!originalAudio) {
                return;
            }

            // Skryjeme původní přehrávač od WordPressu (MediaElement.js).
            const mediaElementWrapper = originalAudio.closest('.mejs-container');
            if (mediaElementWrapper) {
                mediaElementWrapper.style.display = 'none';
            } else {
                originalAudio.style.display = 'none';
            }

            // Vytvoříme HTML strukturu pro náš vlastní přehrávač.
            const customPlayerHTML = `
                <div class="ai-audio-player">
                    <button class="play-pause-btn">
                        <i class="fa fa-play" aria-hidden="true"></i>
                    </button>
                    <div class="progress-bar-wrapper">
                        <div class="progress-bar"></div>
                    </div>
                    <div class="time-display">0:00 / 0:00</div>
                </div>
            `;

            container.insertAdjacentHTML('beforeend', customPlayerHTML);
            container.dataset.playerInitialized = 'true';

            const customPlayer = container.querySelector('.ai-audio-player');
            const playPauseBtn = customPlayer.querySelector('.play-pause-btn');
            const playIcon = playPauseBtn.querySelector('.fa');
            const progressBarWrapper = customPlayer.querySelector('.progress-bar-wrapper');
            const progressBar = customPlayer.querySelector('.progress-bar');
            const timeDisplay = customPlayer.querySelector('.time-display');

            const formatTime = (seconds) => {
                if (isNaN(seconds) || seconds < 0) return "0:00";
                const minutes = Math.floor(seconds / 60);
                const secs = Math.floor(seconds % 60);
                return `${minutes}:${secs < 10 ? '0' : ''}${secs}`;
            };

            playPauseBtn.addEventListener('click', () => {
                if (originalAudio.paused) {
                    originalAudio.play();
                } else {
                    originalAudio.pause();
                }
            });

            originalAudio.addEventListener('play', () => { playIcon.classList.remove('fa-play'); playIcon.classList.add('fa-pause'); });
            originalAudio.addEventListener('pause', () => { playIcon.classList.remove('fa-pause'); playIcon.classList.add('fa-play'); });

            originalAudio.addEventListener('timeupdate', () => {
                const { currentTime, duration } = originalAudio;
                if (duration) {
                    progressBar.style.width = `${(currentTime / duration) * 100}%`;
                    timeDisplay.textContent = `${formatTime(currentTime)} / ${formatTime(duration)}`;
                }
            });

            originalAudio.addEventListener('loadedmetadata', () => { if (originalAudio.duration) { timeDisplay.textContent = `0:00 / ${formatTime(originalAudio.duration)}`; } });

            progressBarWrapper.addEventListener('click', (e) => {
                const { clientWidth } = progressBarWrapper;
                const clickX = e.offsetX;
                const duration = originalAudio.duration;
                if (duration) {
                    originalAudio.currentTime = (clickX / clientWidth) * duration;
                }
            });
        });
    }

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