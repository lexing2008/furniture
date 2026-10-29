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

    // Сортировка на странице категории.
    document.querySelectorAll('[data-sort-select]').forEach(function (select) {
        select.addEventListener('change', function () {
            if (select.value) {
                window.location.href = select.value;
            }
        });
    });

    // Количество товаров на странице.
    document.querySelectorAll('[data-per-page-select]').forEach(function (select) {
        select.addEventListener('change', function () {
            if (select.value) {
                window.location.href = select.value;
            }
        });
    });

    // Подсказки категорий в поиске: debounce 350 мс.
    document.querySelectorAll('[data-category-search]').forEach(function (input) {
        var box = input.closest('[data-search-box]');
        var suggestions = box ? box.querySelector('[data-search-suggestions]') : null;

        if (!box || !suggestions) {
            return;
        }

        var timer = null;
        var requestVersion = 0;

        var hide = function () {
            suggestions.classList.add('hidden');
            suggestions.replaceChildren();
        };

        var render = function (categories, filterVariants) {
            suggestions.replaceChildren();
            var items = (Array.isArray(categories) ? categories : [])
                .concat(Array.isArray(filterVariants) ? filterVariants : []);

            if (items.length === 0) {
                hide();

                return;
            }

            items.forEach(function (category) {
                var link = document.createElement('a');
                link.href = category.url;
                link.className = 'flex items-center justify-between gap-2 rounded-xl px-3 py-2 text-left text-sm text-gray-700 transition hover:bg-green-first/10 hover:text-green-first';

                var label = document.createElement('span');
                label.textContent = category.name;
                link.appendChild(label);

                var arrow = document.createElement('span');
                arrow.className = 'text-gray-300';
                arrow.textContent = '→';
                link.appendChild(arrow);

                suggestions.appendChild(link);
            });

            suggestions.classList.remove('hidden');
        };

        input.addEventListener('input', function () {
            clearTimeout(timer);
            var query = input.value.trim();

            if (query.length < 2) {
                hide();

                return;
            }

            timer = setTimeout(function () {
                var version = ++requestVersion;

                fetch(input.dataset.categorySearchUrl + '?query=' + encodeURIComponent(query), {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                })
                    .then(function (response) {
                        return response.ok ? response.json() : { categories: [] };
                    })
                    .then(function (data) {
                        if (version === requestVersion) {
                            render(data.categories, data.filter_variants);
                        }
                    })
                    .catch(function () {
                        if (version === requestVersion) {
                            hide();
                        }
                    });
            }, 350);
        });

        input.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                hide();
            }
        });

        document.addEventListener('click', function (event) {
            if (!box.contains(event.target)) {
                hide();
            }
        });
    });

    // Галерея изображений товара.
    var galleryModal = document.querySelector('[data-gallery-modal]');

    if (galleryModal) {
        var galleryImage = galleryModal.querySelector('[data-gallery-image]');
        var galleryTitleElement = galleryModal.querySelector('[data-gallery-title]');
        var galleryPriceElement = galleryModal.querySelector('[data-gallery-price]');
        var galleryCounter = galleryModal.querySelector('[data-gallery-counter]');
        var galleryPrev = galleryModal.querySelector('[data-gallery-prev]');
        var galleryNext = galleryModal.querySelector('[data-gallery-next]');
        var galleryClose = galleryModal.querySelector('[data-gallery-close]');
        var galleryStage = galleryModal.querySelector('[data-gallery-stage]');
        var galleryItems = [];
        var galleryIndex = 0;
        var galleryTitle = '';
        var galleryTrigger = null;
        var galleryTouchStartX = null;
        var galleryTouchStartY = null;
        var cardSwipeStartX = null;
        var cardSwipeStartY = null;
        var cardSwipeTrigger = null;
        var suppressGalleryClick = false;
        var gallerySwipeHandled = false;
        var galleryAnimationVersion = 0;

        var showGalleryItem = function (index) {
            if (galleryItems.length === 0) {
                return;
            }

            galleryIndex = (index + galleryItems.length) % galleryItems.length;
            var item = galleryItems[galleryIndex];
            var animationVersion = ++galleryAnimationVersion;

            galleryImage.classList.remove('gallery-image-enter');
            galleryImage.src = item.url;
            galleryImage.alt = item.alt || galleryTitle;

            if (item.width && item.height) {
                galleryImage.setAttribute('width', item.width);
                galleryImage.setAttribute('height', item.height);
            } else {
                galleryImage.removeAttribute('width');
                galleryImage.removeAttribute('height');
            }

            var animateImage = function () {
                if (animationVersion !== galleryAnimationVersion) {
                    return;
                }

                galleryImage.classList.remove('gallery-image-enter');
                void galleryImage.offsetWidth;
                galleryImage.classList.add('gallery-image-enter');
            };

            if (galleryImage.complete) {
                animateImage();
            } else {
                galleryImage.addEventListener('load', animateImage, { once: true });
            }

            galleryCounter.textContent = (galleryIndex + 1) + ' / ' + galleryItems.length;
            galleryPrev.style.display = galleryItems.length < 2 ? 'none' : '';
            galleryNext.style.display = galleryItems.length < 2 ? 'none' : '';
        };

        var openGallery = function (button, initialIndex = 0) {
            try {
                galleryItems = JSON.parse(button.dataset.galleryImages || '[]');
            } catch (error) {
                galleryItems = [];
            }

            if (!Array.isArray(galleryItems) || galleryItems.length === 0) {
                return;
            }

            galleryTitle = button.dataset.galleryTitle || '';
            galleryTrigger = button;
            galleryTitleElement.textContent = galleryTitle;
            galleryPriceElement.textContent = button.dataset.galleryPrice || '';
            galleryModal.classList.remove('hidden');
            galleryModal.classList.add('flex');
            document.body.classList.add('overflow-hidden');
            showGalleryItem(initialIndex);
            galleryClose.focus({ preventScroll: true });
        };

        var closeGallery = function () {
            galleryModal.classList.add('hidden');
            galleryModal.classList.remove('flex');
            document.body.classList.remove('overflow-hidden');
            galleryImage.removeAttribute('src');
            galleryTrigger?.focus({ preventScroll: true });
        };

        document.addEventListener('click', function (event) {
            var button = event.target.closest('[data-gallery-open]');

            if (button) {
                event.preventDefault();

                if (suppressGalleryClick) {
                    suppressGalleryClick = false;

                    return;
                }

                openGallery(button);
            }
        });

        galleryClose.addEventListener('click', closeGallery);
        galleryPrev.addEventListener('click', function () {
            showGalleryItem(galleryIndex - 1);
        });
        galleryNext.addEventListener('click', function () {
            showGalleryItem(galleryIndex + 1);
        });

        galleryImage.addEventListener('click', function () {
            if (gallerySwipeHandled) {
                gallerySwipeHandled = false;

                return;
            }

            showGalleryItem(galleryIndex + 1);
        });

        galleryModal.addEventListener('click', function (event) {
            if (event.target === galleryModal) {
                closeGallery();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (galleryModal.classList.contains('hidden')) {
                return;
            }

            if (event.key === 'Escape') {
                closeGallery();
            } else if (event.key === 'ArrowLeft') {
                showGalleryItem(galleryIndex - 1);
            } else if (event.key === 'ArrowRight') {
                showGalleryItem(galleryIndex + 1);
            }
        });

        var resetGalleryTouch = function () {
            galleryTouchStartX = null;
            galleryTouchStartY = null;
        };

        galleryStage?.addEventListener('touchstart', function (event) {
            if (event.touches.length !== 1) {
                resetGalleryTouch();

                return;
            }

            galleryTouchStartX = event.touches[0].clientX;
            galleryTouchStartY = event.touches[0].clientY;
        }, { passive: true });

        galleryStage?.addEventListener('touchmove', function (event) {
            if (galleryTouchStartX === null || event.touches.length !== 1) {
                return;
            }

            var deltaX = event.touches[0].clientX - galleryTouchStartX;
            var deltaY = event.touches[0].clientY - galleryTouchStartY;

            // Horizontal swipe belongs to the gallery; vertical keeps scrolling.
            if (Math.abs(deltaX) > Math.abs(deltaY) && Math.abs(deltaX) > 10) {
                event.preventDefault();
            }
        }, { passive: false });

        galleryStage?.addEventListener('touchend', function (event) {
            if (galleryTouchStartX === null) {
                return;
            }

            var deltaX = event.changedTouches[0].clientX - galleryTouchStartX;
            var deltaY = event.changedTouches[0].clientY - galleryTouchStartY;
            resetGalleryTouch();

            if (Math.abs(deltaX) < 50 || Math.abs(deltaX) <= Math.abs(deltaY)) {
                return;
            }

            gallerySwipeHandled = true;
            window.setTimeout(function () {
                gallerySwipeHandled = false;
            }, 600);

            showGalleryItem(galleryIndex + (deltaX < 0 ? 1 : -1));
        }, { passive: true });

        galleryStage?.addEventListener('touchcancel', resetGalleryTouch, { passive: true });

        // Swipe left on the card image opens the gallery at the next photo.
        document.addEventListener('touchstart', function (event) {
            var button = event.target.closest('[data-gallery-open]');

            if (!button || event.touches.length !== 1) {
                cardSwipeTrigger = null;

                return;
            }

            cardSwipeTrigger = button;
            cardSwipeStartX = event.touches[0].clientX;
            cardSwipeStartY = event.touches[0].clientY;
        }, { passive: true });

        document.addEventListener('touchend', function (event) {
            if (cardSwipeTrigger === null || cardSwipeStartX === null) {
                return;
            }

            var deltaX = event.changedTouches[0].clientX - cardSwipeStartX;
            var deltaY = event.changedTouches[0].clientY - cardSwipeStartY;
            var button = cardSwipeTrigger;

            cardSwipeTrigger = null;
            cardSwipeStartX = null;
            cardSwipeStartY = null;

            if (deltaX > -50 || Math.abs(deltaX) <= Math.abs(deltaY)) {
                return;
            }

            suppressGalleryClick = true;
            window.setTimeout(function () {
                suppressGalleryClick = false;
            }, 600);

            openGallery(button, 1);
        }, { passive: true });
    }

    // Избранное: сердечко на карточке товара.
    var favoriteCsrfToken = function () {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    };

    var setFavoriteState = function (button, active) {
        button.setAttribute('aria-pressed', active ? 'true' : 'false');
        button.setAttribute('aria-label', active
            ? (button.dataset.removeLabel || 'Убрать из избранного')
            : (button.dataset.addLabel || 'Добавить в избранное'));

        button.classList.toggle('bg-green-first/5', active);
        button.classList.toggle('text-green-first/70', active);
        button.classList.toggle('bg-white/70', !active);
        button.classList.toggle('text-gray-300', !active);
        button.classList.toggle('hover:text-green-first', !active);

        var icon = button.querySelector('svg');

        if (icon) {
            icon.setAttribute('fill', active ? 'currentColor' : 'none');
        }
    };

    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-favorite-toggle]');

        if (!button || button.disabled) {
            return;
        }

        event.preventDefault();
        button.disabled = true;

        fetch(button.dataset.favoriteUrl, {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'X-CSRF-TOKEN': favoriteCsrfToken(),
            },
        })
            .then(function (response) {
                if (!response.ok) {
                    throw new Error('Favorite request failed');
                }

                return response.json();
            })
            .then(function (data) {
                var active = Boolean(data.favorited);
                setFavoriteState(button, active);

                if (!active && button.hasAttribute('data-favorite-remove')) {
                    button.closest('[data-favorite-card]')?.remove();
                }
            })
            .catch(function () {
                // Keep the previous state when the request fails.
            })
            .finally(function () {
                button.disabled = false;
            });
    });

    // Сброс всех фильтров категории.
    document.querySelectorAll('[data-filter-reset]').forEach(function (link) {
        link.addEventListener('click', function (event) {
            event.preventDefault();
            window.location.href = link.dataset.filterReset || link.href;
        });
    });

    // Выбор вариантов фильтра ведёт на SEO-URL: /category/slug;slug/
    document.querySelectorAll('[data-filter-variant]').forEach(function (input) {
        if (input.type === 'radio') {
            input.dataset.wasChecked = input.checked ? '1' : '0';

            input.addEventListener('click', function (event) {
                if (input.dataset.wasChecked === '1') {
                    event.preventDefault();
                    input.checked = false;
                    input.dispatchEvent(new Event('change', { bubbles: true }));
                }

                input.dataset.wasChecked = input.checked ? '1' : '0';
            });
        }

        input.addEventListener('change', function () {
            var form = input.closest('form');

            if (!form || !form.dataset.filterBaseUrl) {
                return;
            }

            var baseUrl = new URL(form.dataset.filterBaseUrl, window.location.origin);
            var selected = [];

            form.querySelectorAll('[data-filter-variant]:checked').forEach(function (item) {
                if (item.dataset.filterSlug) {
                    selected.push(item.dataset.filterSlug);
                }
            });

            var path = baseUrl.pathname.replace(/\/$/, '');

            if (selected.length > 0) {
                path += '/' + selected.join(';') + '/';
            }

            var params = new URLSearchParams(window.location.search);

            form.querySelectorAll('[data-filter-variant]').forEach(function (item) {
                params.delete(item.name);
            });

            form.querySelectorAll('input[type="number"]').forEach(function (item) {
                if (item.value === '') {
                    params.delete(item.name);
                } else {
                    params.set(item.name, item.value);
                }
            });

            params.delete('page');

            var url = new URL(window.location.origin + path);
            url.search = params.toString();

            window.location.href = url.toString();
        });
    });

    // На десктопе подгоняем количество строк описания под высоту фото.
    var alignAdDescriptions = function () {
        if (window.innerWidth < 1024) {
            document.querySelectorAll('[data-ad-description]').forEach(function (description) {
                description.style.removeProperty('-webkit-line-clamp');
                description.style.removeProperty('max-height');
                description.style.removeProperty('line-height');
            });

            return;
        }

        document.querySelectorAll('[data-ads-list] article').forEach(function (article) {
            var description = article.querySelector('[data-ad-description]');
            var image = article.querySelector('[data-gallery-open]') || article.querySelector('[data-ad-image]');

            if (!description || !image) {
                return;
            }

            if (description.classList.contains('is-expanded')) {
                description.style.removeProperty('-webkit-line-clamp');
                description.style.removeProperty('max-height');
                description.style.removeProperty('line-height');

                return;
            }

            var imageBottom = image.getBoundingClientRect().bottom;
            var descriptionTop = description.getBoundingClientRect().top;
            var lineHeight = parseFloat(window.getComputedStyle(description).lineHeight) || 24;
            var available = Math.max(0, imageBottom - descriptionTop);
            var lines = Math.max(3, Math.ceil(available / lineHeight));
            var fittedLineHeight = lines > 0 ? available / lines : lineHeight;

            description.style.setProperty('-webkit-line-clamp', String(lines));
            description.style.maxHeight = available + 'px';
            description.style.lineHeight = fittedLineHeight + 'px';
        });
    };

    var scheduleDescriptionAlignment = function () {
        window.requestAnimationFrame(alignAdDescriptions);
    };

    alignAdDescriptions();
    window.addEventListener('resize', scheduleDescriptionAlignment);
    window.addEventListener('load', scheduleDescriptionAlignment);

    // Полное описание товара и скрытые данные компании.
    // Делегирование позволяет раскрывать и карточки, загруженные кнопкой «Показать ещё».
    document.addEventListener('click', function (event) {
        var expand = event.target.closest('[data-ad-expand]');

        if (expand) {
            var article = expand.closest('article');

            if (!article) {
                return;
            }

            var description = article.querySelector('[data-ad-description]');
            description?.classList.add('is-expanded');
            description?.style.removeProperty('-webkit-line-clamp');
            description?.style.removeProperty('max-height');
            description?.style.removeProperty('line-height');
            article.querySelector('[data-ad-description-fade]')?.classList.add('hidden');
            article.querySelector('[data-ad-gradient]')?.classList.remove('hidden');

            var details = article.querySelector('[data-ad-company-details]');

            if (details) {
                details.classList.add('is-expanded');
                details.setAttribute('aria-hidden', 'false');
            }

            expand.setAttribute('aria-expanded', 'true');
            expand.classList.add('hidden');
            expand.style.display = 'none';

            return;
        }

        var collapse = event.target.closest('[data-ad-collapse]');

        if (!collapse) {
            return;
        }

        var card = collapse.closest('article');

        if (!card) {
            return;
        }

        card.querySelector('[data-ad-description]')?.classList.remove('is-expanded');
        card.querySelector('[data-ad-description-fade]')?.classList.remove('hidden');
        card.querySelector('[data-ad-gradient]')?.classList.add('hidden');
        scheduleDescriptionAlignment();

        var details = card.querySelector('[data-ad-company-details]');

        if (details) {
            details.classList.remove('is-expanded');
            details.setAttribute('aria-hidden', 'true');
        }

        var expandButton = card.querySelector('[data-ad-expand]');

        if (expandButton) {
            expandButton.setAttribute('aria-expanded', 'false');
            expandButton.classList.remove('hidden');
            expandButton.style.display = '';
            expandButton.focus({ preventScroll: true });
        }
    });

    // Подгрузка следующей страницы товаров без перезагрузки.
    var loadMoreButton = document.querySelector('[data-load-more-url]');

    if (loadMoreButton) {
        loadMoreButton.addEventListener('click', function () {
            if (loadMoreButton.disabled || !loadMoreButton.dataset.loadMoreUrl) {
                return;
            }

            var label = loadMoreButton.querySelector('[data-load-more-label]');
            var originalLabel = loadMoreButton.dataset.label || '';
            var loadingLabel = loadMoreButton.dataset.loadingLabel || originalLabel;

            loadMoreButton.disabled = true;

            if (label) {
                label.textContent = loadingLabel;
            }

            fetch(loadMoreButton.dataset.loadMoreUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (response) {
                    if (!response.ok) {
                        throw new Error('Load more failed');
                    }

                    return response.text();
                })
                .then(function (html) {
                    var doc = new DOMParser().parseFromString(html, 'text/html');
                    var list = document.querySelector('[data-ads-list]');
                    var incomingList = doc.querySelector('[data-ads-list]');

                    if (list && incomingList) {
                        Array.from(incomingList.children).forEach(function (item) {
                            list.appendChild(item);
                        });
                    }

                    var pagination = document.querySelector('[data-pagination]');
                    var incomingPagination = doc.querySelector('[data-pagination]');

                    if (pagination && incomingPagination) {
                        pagination.innerHTML = incomingPagination.innerHTML;
                    }

                    var nextButton = doc.querySelector('[data-load-more-url]');
                    var wrapper = loadMoreButton.closest('[data-load-more-wrapper]');

                    if (nextButton) {
                        loadMoreButton.dataset.loadMoreUrl = nextButton.dataset.loadMoreUrl;
                        loadMoreButton.dataset.label = nextButton.dataset.label || originalLabel;
                        loadMoreButton.dataset.loadingLabel = nextButton.dataset.loadingLabel || loadingLabel;
                    } else if (wrapper) {
                        wrapper.remove();
                    }

                    if (label) {
                        label.textContent = loadMoreButton.dataset.label || originalLabel;
                    }

                    scheduleDescriptionAlignment();
                })
                .catch(function () {
                    if (label) {
                        label.textContent = originalLabel;
                    }
                })
                .finally(function () {
                    loadMoreButton.disabled = false;
                });
        });
    }
})();
