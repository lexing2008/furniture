@if ($paginator->hasPages())
    <nav
        role="navigation"
        aria-label="{{ __('Пагинация') }}"
        class="mt-8 flex flex-col gap-4 border-t border-gray-100 pt-6 sm:flex-row sm:items-center sm:justify-between"
    >
        <p class="text-center text-xs text-gray-400 sm:text-left">
            {{ __('Показано :first–:last из :total', [
                'first' => $paginator->firstItem(),
                'last' => $paginator->lastItem(),
                'total' => $paginator->total(),
            ]) }}
        </p>

        <div class="flex flex-wrap items-center justify-center gap-1.5">
            @if ($paginator->onFirstPage())
                <span class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-xl border border-gray-100 text-gray-300" aria-disabled="true" aria-label="{{ __('Назад') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </span>
            @else
                <a
                    href="{{ $paginator->previousPageUrl() }}"
                    rel="prev"
                    aria-label="{{ __('Назад') }}"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:border-green-first/30 hover:text-green-first"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </a>
            @endif

            @foreach ($elements as $element)
                @if (is_string($element))
                    <span class="inline-flex h-9 w-9 items-center justify-center text-xs text-gray-300" aria-hidden="true">{{ $element }}</span>
                @endif

                @if (is_array($element))
                    @foreach ($element as $page => $url)
                        @if ($page == $paginator->currentPage())
                            <span aria-current="page" class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-xl bg-green-first px-2 text-xs font-bold text-white shadow-sm shadow-green-first/25">
                                {{ $page }}
                            </span>
                        @else
                            <a
                                href="{{ $url }}"
                                class="inline-flex h-9 min-w-[2.25rem] items-center justify-center rounded-xl border border-transparent px-2 text-xs font-medium text-gray-500 transition hover:border-gray-200 hover:bg-white hover:text-green-first"
                                aria-label="{{ __('Страница :page', ['page' => $page]) }}"
                            >
                                {{ $page }}
                            </a>
                        @endif
                    @endforeach
                @endif
            @endforeach

            @if ($paginator->hasMorePages())
                <a
                    href="{{ $paginator->nextPageUrl() }}"
                    rel="next"
                    aria-label="{{ __('Вперёд') }}"
                    class="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-gray-200 bg-white text-gray-500 shadow-sm transition hover:border-green-first/30 hover:text-green-first"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </a>
            @else
                <span class="inline-flex h-9 w-9 cursor-not-allowed items-center justify-center rounded-xl border border-gray-100 text-gray-300" aria-disabled="true" aria-label="{{ __('Вперёд') }}">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
                    </svg>
                </span>
            @endif
        </div>
    </nav>
@endif
