<div
    data-company-info-modal
    data-company-info-error="{{ __('Не удалось загрузить информацию.') }}"
    class="fixed inset-0 z-[110] hidden items-center justify-center bg-black/50 p-4 backdrop-blur-sm"
    role="dialog"
    aria-modal="true"
    aria-labelledby="company-info-title"
>
    <div class="relative flex max-h-[85vh] w-full max-w-lg flex-col overflow-hidden rounded-2xl bg-white shadow-2xl">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 px-5 py-4">
            <h2 id="company-info-title" data-company-info-title class="text-lg font-bold tracking-tight text-gray-900"></h2>
            <button
                type="button"
                data-company-info-close
                class="flex h-9 w-9 shrink-0 cursor-pointer items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 focus:outline-none focus-visible:ring-2 focus-visible:ring-green-first"
                aria-label="{{ __('Закрыть') }}"
            >
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <div data-company-info-loading class="hidden items-center justify-center gap-2 px-5 py-10 text-sm text-gray-500">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" class="h-5 w-5 animate-spin text-green-first">
                <circle cx="12" cy="12" r="9" stroke="currentColor" stroke-width="3" class="opacity-20" />
                <path d="M21 12a9 9 0 0 0-9-9" stroke="currentColor" stroke-width="3" stroke-linecap="round" />
            </svg>
            <span>{{ __('Загрузка информации...') }}</span>
        </div>

        <div data-company-info-body class="rich-text hidden overflow-y-auto px-5 py-5 text-sm leading-6 text-gray-700"></div>
    </div>
</div>
