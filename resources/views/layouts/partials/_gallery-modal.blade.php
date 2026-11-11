<div
    data-gallery-modal
    class="fixed inset-0 z-[100] hidden items-center justify-center bg-black/90 p-4 backdrop-blur-sm sm:p-8"
    role="dialog"
    aria-modal="true"
    aria-label="{{ __('Галерея изображений') }}"
>
    <div class="pointer-events-none absolute inset-x-0 top-0 z-10 bg-gradient-to-b from-black/75 via-black/35 to-transparent px-4 pb-10 pt-4 sm:px-8">
        <div class="max-w-[calc(100%-4rem)]">
            <p data-gallery-title class="truncate text-sm font-semibold text-white sm:text-base"></p>
            <p data-gallery-price class="mt-1 text-xs font-medium text-white/75 sm:text-sm"></p>
        </div>
    </div>

    <button
        type="button"
        data-gallery-close
        class="absolute right-4 top-4 z-20 flex h-11 w-11 cursor-pointer items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white sm:right-6 sm:top-6"
        aria-label="{{ __('Закрыть') }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
        </svg>
    </button>

    <button
        type="button"
        data-gallery-prev
        class="absolute left-2 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white sm:left-6 sm:h-12 sm:w-12"
        aria-label="{{ __('Предыдущее фото') }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
        </svg>
    </button>

    <div data-gallery-stage class="flex max-h-full max-w-full items-center justify-center">
        <img data-gallery-image alt="" class="max-h-[80vh] max-w-[90vw] cursor-pointer select-none object-contain sm:max-w-[80vw]">
    </div>

    <button
        type="button"
        data-gallery-next
        class="absolute right-2 top-1/2 z-10 flex h-11 w-11 -translate-y-1/2 cursor-pointer items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 focus:outline-none focus-visible:ring-2 focus-visible:ring-white sm:right-6 sm:h-12 sm:w-12"
        aria-label="{{ __('Следующее фото') }}"
    >
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
        </svg>
    </button>

    <p data-gallery-counter class="absolute bottom-5 left-1/2 -translate-x-1/2 rounded-full bg-white/10 px-3 py-1 text-xs font-semibold text-white backdrop-blur-sm" aria-live="polite"></p>
</div>
