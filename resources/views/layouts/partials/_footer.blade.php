@php
    $locale = app()->getLocale();
    $regionName = $locale === 'en' ? $region->name_en : $region->name_in;
    $regionLabel = $locale === 'en' ? $region->name_en : $region->name;
    $capitalCities = collect($areas)
        ->flatMap(fn ($area) => $area['cities'])
        ->filter(fn ($city) => $city->is_capital && $city->id !== $region->id)
        ->values();
@endphp

<footer class="relative overflow-hidden bg-green-first text-white">
    <div class="pointer-events-none absolute inset-0 bg-gradient-to-br from-green-first via-green-first to-green-second"></div>
    <div class="pointer-events-none absolute -right-24 -top-32 h-80 w-80 rounded-full bg-white/5 blur-3xl"></div>
    <div class="pointer-events-none absolute -bottom-40 left-1/4 h-80 w-80 rounded-full bg-white/10 blur-3xl"></div>

    <div class="relative border-b border-white/15">
        <div class="mx-auto flex max-w-[1600px] flex-col gap-4 px-4 py-5 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <div class="flex items-center gap-3">
                <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-white/10 text-white ring-1 ring-white/15">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.7" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 10.5 12 3l9 7.5M5.25 9.75V21h4.5v-5.25h4.5V21h4.5V9.75" />
                    </svg>
                </span>
                <div>
                    <p class="text-sm font-semibold">{{ __('Разместить мебель') }}</p>
                    <p class="mt-0.5 text-xs text-white/80">{{ __('Создайте кабинет компании и начните размещать товары и услуги.') }}</p>
                </div>
            </div>

            <a
                href="{{ route('account.register', $region->subdomain) }}"
                class="inline-flex w-fit cursor-pointer items-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-green-first shadow-sm transition hover:bg-white/90"
            >
                {{ __('Регистрация') }}
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                </svg>
            </a>
        </div>
    </div>

    <div class="relative mx-auto max-w-[1600px] px-4 py-10 sm:px-6 lg:px-8">
        <div class="grid gap-10 md:grid-cols-2 xl:grid-cols-[1.5fr_1.2fr_0.8fr_0.9fr]">
            <!-- Brand -->
            <div>
                <a href="{{ $country->url() }}" class="inline-flex items-center gap-3">
                    <span class="text-xl font-bold tracking-wide">{{ config('app.name') }}</span>
                </a>

                <p class="mt-4 max-w-md text-sm leading-6 text-white/85">
                    {{ __(':region :domain — это мебельный торговый центр в интернете, где продавцы и производители могут разместить свою мебель и услуги.', [
                        'region' => $locale === 'en' ? $region->name_en : $region->name,
                        'domain' => config('app.domain'),
                    ]) }}
                </p>

                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-white/10 px-3 py-1.5 text-xs text-white/80 ring-1 ring-white/10">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" />
                        </svg>
                        {{ $regionLabel }}
                    </span>
                    <span class="inline-flex items-center rounded-full bg-white/10 px-3 py-1.5 text-xs text-white/80 ring-1 ring-white/10">
                        {{ $locale === 'en' ? $country->name_en : $country->name }}
                    </span>
                </div>
            </div>

            <!-- Catalog -->
            <div>
                <h3 class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/85">
                    <span class="h-px w-5 bg-white/70"></span>
                    {{ __('Каталог мебели') }}
                </h3>
                <div class="grid gap-x-5 gap-y-2 sm:grid-cols-2 xl:grid-cols-1">
                    @foreach ($menuCategories as $category)
                        <a
                            href="{{ $category['url'] }}"
                            class="group flex items-center justify-between gap-3 text-[13px] leading-5 text-white/85 transition hover:text-white"
                        >
                            <span>{{ __(':name в :region', ['name' => $category['name'], 'region' => $regionName]) }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5 shrink-0 text-white/25 transition group-hover:translate-x-0.5 group-hover:text-white">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                            </svg>
                        </a>
                    @endforeach
                </div>
            </div>

            <!-- Cities -->
            <div>
                <h3 class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/85">
                    <span class="h-px w-5 bg-white/70"></span>
                    {{ __('Другие города') }}
                </h3>
                <div class="space-y-2">
                    @forelse ($capitalCities as $city)
                        <a href="{{ $city->url() }}" class="group flex items-center gap-2 text-[13px] leading-5 text-white/85 transition hover:text-white">
                            <span class="h-1 w-1 rounded-full bg-white/70 transition group-hover:bg-white"></span>
                            {{ __('Мебель в :city', ['city' => $locale === 'en' ? $city->name_en : $city->name_in]) }}
                        </a>
                    @empty
                        <p class="text-xs leading-5 text-white/40">{{ __('Пока нет других городов.') }}</p>
                    @endforelse
                </div>
            </div>

            <!-- Information -->
            <div>
                <h3 class="mb-4 flex items-center gap-2 text-xs font-semibold uppercase tracking-[0.18em] text-white/85">
                    <span class="h-px w-5 bg-white/70"></span>
                    {{ __('Информация') }}
                </h3>
                <div class="space-y-2.5">
                    <a href="#" class="block text-[13px] leading-5 text-white/85 transition hover:text-white">{{ __('О проекте') }}</a>
                    <a href="#" class="block text-[13px] leading-5 text-white/85 transition hover:text-white">{{ __('Пользовательское соглашение') }}</a>
                    <a href="{{ route('account.register', $region->subdomain) }}" class="block text-[13px] leading-5 text-white/85 transition hover:text-white">{{ __('Регистрация') }}</a>
                    <a href="{{ route('account.login') }}" class="block text-[13px] leading-5 text-white/85 transition hover:text-white">{{ __('Вход') }}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="relative border-t border-white/15">
        <div class="mx-auto flex max-w-[1600px] flex-col gap-2 px-4 py-5 text-xs text-white/65 sm:flex-row sm:items-center sm:justify-between sm:px-6 lg:px-8">
            <p>{{ __('© :year Мебельный портал :domain', ['year' => date('Y'), 'domain' => $region->subdomain.'.'.config('app.domain')]) }}</p>
            <p>{{ __('Все права защищены.') }}</p>
        </div>
    </div>
</footer>
