if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('sw.js').then(registration => {
            registration.update();
        }).catch(() => {});
    });
}

let deferredInstallPrompt = null;

function removeInstallButton() {
    document.getElementById('pwaInstallButton')?.remove();
}

window.addEventListener('beforeinstallprompt', event => {
    event.preventDefault();
    deferredInstallPrompt = event;

    if (document.getElementById('pwaInstallButton')) {
        return;
    }

    const button = document.createElement('button');
    button.id = 'pwaInstallButton';
    button.type = 'button';
    button.className = 'pwa-install-button';
    button.setAttribute('aria-label', 'Instalar Doce Controle neste dispositivo');
    button.innerHTML = '<span aria-hidden="true">⬇</span> Instalar app';

    button.addEventListener('click', async () => {
        if (!deferredInstallPrompt) {
            return;
        }

        deferredInstallPrompt.prompt();
        await deferredInstallPrompt.userChoice;
        deferredInstallPrompt = null;
        removeInstallButton();
    });

    document.body.appendChild(button);
});

window.addEventListener('appinstalled', () => {
    deferredInstallPrompt = null;
    removeInstallButton();
});
