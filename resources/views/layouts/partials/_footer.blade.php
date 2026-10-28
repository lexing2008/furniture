<footer class="bg-green-first">
    @php
        $locale = app()->getLocale();
        $regionName = $locale === 'en' ? $region->name_en : $region->name_in;
        $capitalCities = collect($areas)
            ->flatMap(fn ($area) => $area['cities'])
            ->filter(fn ($city) => $city->is_capital)
            ->values();
    @endphp

    <div class="container mx-auto py-3 px-4 pt-5">

        <div class="flex flex-col md:flex-row justify-between gap-5">
            <div>
                <div class="text-white font-bold mb-2">
                    {{ __('О нас') }}
                    <div class="h-0.5 w-7 bg-white/90"></div>
                </div>
                <div class="max-w-md text-white/80 text-xs">
                    {{ __(':region :domain — это мебельный торговый центр в интернете, где продавцы и производители могут разместить свою мебель и услуги.', [
                        'region' => $locale === 'en' ? $region->name_en : $region->name,
                        'domain' => config('app.domain'),
                    ]) }}
                </div>

                <div class="flex flex-col my-2">
                    <div>
                        <a href="{{ route('account.register', $region->subdomain) }}" class="text-white/80 hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Регистрация') }}</a>
                    </div>
                    <div>
                        <a href="{{ route('account.login') }}" class="text-white/80 hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Вход') }}</a>
                    </div>
                    <div>
                        <a href="#" class="text-xs text-white/80 hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">{{ __('Пользовательское соглашение') }}</a>
                    </div>
                </div>
            </div>

            <div>
                <div class="text-white font-bold mb-2">
                    {{ __('Каталог мебели') }}
                    <div class="h-0.5 w-7 bg-white/90"></div>
                </div>
                <div class="grid grid-cols-2 gap-1 gap-x-6 text-white/80 text-xs">
                    @foreach ($menuCategories as $category)
                        <div>
                            <a href="{{ $category['url'] }}" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">
                                {{ __(':name в :region', ['name' => $category['name'], 'region' => $regionName]) }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="max-sm:mt-3">
                <div class="text-white font-bold mb-2">
                    {{ __('Другие города') }}
                    <div class="h-0.5 w-7 bg-white/90"></div>
                </div>
                <div class="grid grid-cols-2 gap-1 gap-x-6 text-white/80 text-xs">
                    @foreach ($capitalCities as $city)
                        <div>
                            <a href="{{ $city->url() }}" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">
                                {{ __('Мебель в :city', ['city' => $locale === 'en' ? $city->name_en : $city->name_in]) }}
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <hr class="my-6 border-t border-white/15" />

        <div class="mb-2 text-center text-white/70 text-sm">
            {{ __('© :year Мебельный портал :domain', [
                'year' => date('Y'),
                'domain' => $region->subdomain.'.'.config('app.domain'),
            ]) }}
        </div>
    </div>
</footer>
