(function () {
    // regions popup
    var button = document.getElementById('region-button');
    var buttonClose = document.getElementById('region-popup-close');
    var backdrop = document.getElementById('region-popup-overlay');

    function setRegionPopupOpen(open) {
        backdrop.classList.toggle('hidden', !open);
    }

    button.addEventListener('click', function () { setRegionPopupOpen(true); });

    document.querySelectorAll('[data-region-popup-open]').forEach(function (openButton) {
        openButton.addEventListener('click', function () { setRegionPopupOpen(true); });
    });

    buttonClose.addEventListener('click', function () { setRegionPopupOpen(false); });
    backdrop.addEventListener('click', function(event) {
        // Находим сам попап (белое окно)
        const popup = document.getElementById('region-popup');

        // Проверяем: если кликнули на подложку И клик пришелся НЕ внутрь белого окна
        if (event.target === this && !popup.contains(event.target)) {
            setRegionPopupOpen(false);
        }
    });
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') setRegionPopupOpen(false); });
    // нажатие на Область
    document.querySelectorAll('[data-area-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function () { toggle.parentElement.classList.toggle('is-open'); });
    });
    // - - - regions popup
})();
