(function () {
    // Regions popup
    var button = document.getElementById('region-button');
    var buttonClose = document.getElementById('region-popup-close');
    var backdrop = document.getElementById('region-popup-overlay');
    var burger = document.querySelector('[data-header-burger]');
    var mobileMenu = document.querySelector('[data-header-mobile-menu]');
    var mobileClose = document.querySelector('[data-header-mobile-close]');

    function setMobileMenuOpen(open) {
        if (!burger || !mobileMenu) {
            return;
        }

        mobileMenu.classList.toggle('hidden', !open);
        burger.setAttribute('aria-expanded', open ? 'true' : 'false');
        burger.setAttribute('aria-label', open
            ? (burger.dataset.closeLabel || 'Закрыть меню')
            : (burger.dataset.openLabel || 'Открыть меню'));

        document.body.classList.toggle('overflow-hidden', open);

        if (open) {
            mobileClose && mobileClose.focus();
        } else if (document.activeElement && mobileMenu.contains(document.activeElement)) {
            burger.focus();
        }
    }

    function setRegionPopupOpen(open) {
        backdrop.classList.toggle('hidden', !open);
    }

    button.addEventListener('click', function () {
        setRegionPopupOpen(true);
    });

    document.querySelectorAll('[data-region-popup-open]').forEach(function (openButton) {
        openButton.addEventListener('click', function () {
            setRegionPopupOpen(true);
        });
    });

    buttonClose.addEventListener('click', function () {
        setRegionPopupOpen(false);
    });

    backdrop.addEventListener('click', function (event) {
        var popup = document.getElementById('region-popup');

        if (event.target === this && !popup.contains(event.target)) {
            setRegionPopupOpen(false);
        }
    });

    if (burger) {
        burger.addEventListener('click', function () {
            setMobileMenuOpen(mobileMenu.classList.contains('hidden'));
        });
    }

    if (mobileClose) {
        mobileClose.addEventListener('click', function () {
            setMobileMenuOpen(false);
        });
    }

    if (mobileMenu) {
        // Закрываем полноэкранное меню при переходе по любой ссылке.
        mobileMenu.addEventListener('click', function (event) {
            if (event.target.closest('a')) {
                setMobileMenuOpen(false);
            }
        });
    }

    document.addEventListener('keydown', function (event) {
        if (event.key !== 'Escape') {
            return;
        }

        // Escape closes the region popup first; the mobile menu stays underneath.
        if (!backdrop.classList.contains('hidden')) {
            setRegionPopupOpen(false);

            return;
        }

        setMobileMenuOpen(false);
    });

    window.addEventListener('resize', function () {
        if (window.matchMedia('(min-width: 768px)').matches) {
            setMobileMenuOpen(false);
        }
    });

    // Нажатие на область
    document.querySelectorAll('[data-area-toggle]').forEach(function (toggle) {
        toggle.addEventListener('click', function () {
            toggle.parentElement.classList.toggle('is-open');
        });
    });

    function bindCategoryToggles(root) {
        root.querySelectorAll('[data-category-toggle]').forEach(function (toggle) {
            if (toggle.dataset.bound === '1') {
                return;
            }

            toggle.dataset.bound = '1';
            toggle.addEventListener('click', function (event) {
                event.preventDefault();
                event.stopPropagation();

                var item = toggle.closest('[data-category-item]');
                var container = item ? item.querySelector('[data-category-children]') : null;

                if (!container) {
                    return;
                }

                var isOpen = toggle.getAttribute('aria-expanded') === 'true';
                var icon = toggle.querySelector('svg');

                if (isOpen) {
                    toggle.setAttribute('aria-expanded', 'false');
                    container.classList.add('hidden');
                    if (icon) {
                        icon.classList.remove('rotate-45');
                    }

                    return;
                }

                toggle.setAttribute('aria-expanded', 'true');
                container.classList.remove('hidden');
                if (icon) {
                    icon.classList.add('rotate-45');
                }

                if (container.dataset.loaded === '1') {
                    return;
                }

                container.innerHTML = '';
                var loading = document.createElement('p');
                loading.className = 'py-2 text-xs text-gray-400';
                loading.textContent = toggle.dataset.loadingLabel || 'Загрузка...';
                container.appendChild(loading);
                toggle.disabled = true;

                fetch(toggle.dataset.childrenUrl, {
                    headers: { Accept: 'application/json' },
                })
                    .then(function (response) {
                        if (!response.ok) {
                            throw new Error('Children request failed');
                        }

                        return response.json();
                    })
                    .then(function (data) {
                        container.innerHTML = '';
                        var categories = Array.isArray(data.categories) ? data.categories : [];

                        if (categories.length === 0) {
                            var empty = document.createElement('p');
                            empty.className = 'py-2 text-xs text-gray-400';
                            empty.textContent = toggle.dataset.emptyLabel || '';
                            container.appendChild(empty);
                        } else {
                            renderChildren(container, categories, toggle);
                        }

                        container.dataset.loaded = '1';
                    })
                    .catch(function () {
                        container.innerHTML = '';
                        var error = document.createElement('p');
                        error.className = 'py-2 text-xs text-red-600';
                        error.textContent = toggle.dataset.errorLabel || '';
                        container.appendChild(error);
                    })
                    .finally(function () {
                        toggle.disabled = false;
                    });
            });
        });
    }

    function renderChildren(container, categories, sourceButton) {
        var list = document.createElement('ul');
        list.className = 'space-y-1 border-l border-green-first/20 pl-3';

        categories.forEach(function (category) {
            var item = document.createElement('li');
            item.setAttribute('data-category-item', '');

            var row = document.createElement('div');
            row.className = 'flex items-center gap-1';

            var link = document.createElement('a');
            link.href = category.url;
            link.textContent = category.name;
            link.className = 'min-w-0 flex-1 rounded-lg px-2 py-2 text-sm text-gray-600 transition hover:text-green-first';
            row.appendChild(link);

            if (category.has_children) {
                var toggle = document.createElement('button');
                toggle.type = 'button';
                toggle.setAttribute('data-category-toggle', '');
                toggle.setAttribute('data-children-url', category.children_url);
                toggle.setAttribute('data-loading-label', sourceButton.dataset.loadingLabel || 'Загрузка...');
                toggle.setAttribute('data-error-label', sourceButton.dataset.errorLabel || '');
                toggle.setAttribute('data-empty-label', sourceButton.dataset.emptyLabel || '');
                toggle.setAttribute('aria-expanded', 'false');
                toggle.setAttribute('aria-label', sourceButton.getAttribute('aria-label') || '');
                toggle.className = 'flex h-8 w-8 shrink-0 cursor-pointer items-center justify-center rounded-full text-gray-400 transition hover:bg-green-first/10 hover:text-green-first';
                toggle.innerHTML = '<svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5 transition-transform duration-200"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>';
                row.appendChild(toggle);
            }

            item.appendChild(row);

            var childContainer = document.createElement('div');
            childContainer.setAttribute('data-category-children', '');
            childContainer.className = 'hidden px-2 pb-3';
            item.appendChild(childContainer);

            list.appendChild(item);
        });

        container.appendChild(list);
        bindCategoryToggles(container);
    }

    bindCategoryToggles(document);
})();
