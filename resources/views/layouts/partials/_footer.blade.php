<footer class="bg-green-first">
    <div class="container mx-auto py-3 px-4 pt-5">

        <div class="flex flex-col md:flex-row justify-between gap-5">
            <div>
                <div class="text-white font-bold mb-2">
                    О нас
                    <div class="h-0.5 w-7 bg-white/90"></div>
                </div>
                <div class="max-w-md text-white/80 text-xs">
                    {{ $region->name }} {{ config('app.domain') }} - это мебельный торговый центр в интернете, где продавцы и
                    производители могут разместить свою мебель и услуги.
                </div>

                <div class="flex flex-col my-2">
                    <div>
                        <a href="{{route('account.register', $region->subdomain)}}" class="text-white/80 hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Регистрация</a>
                    </div>
                    <div>
                        <a href="{{route('account.login')}}" class="text-white/80 hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Вход</a>
                    </div>
                    <div>
                        <a href="#" class="text-xs text-white/80 hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Пользовательское соглашение</a>
                    </div>
                </div>
            </div>
            <div>
                <div class="text-white font-bold mb-2">
                    Каталог мебели
                    <div class="h-0.5 w-7 bg-white/90"></div>
                </div>
                <div class="grid grid-cols-2 gap-1 gap-x-6 text-white/80 text-xs">
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель в гостиную в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Кухная мебель в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель для спальни в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Детская мебель в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Гардеробная мебель в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель в прихожую в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель в ванную и туалет в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Офисная мебель в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Текстиль в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Услуги в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель для отдыха в Бресте</a>
                    </div>


                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель в гостиную в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Кухная мебель в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель для спальни в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Детская мебель в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Гардеробная мебель в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель в прихожую в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель в ванную и туалет в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Офисная мебель в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Текстиль в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Услуги в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель для отдыха в Бресте</a>
                    </div>
                </div>

            </div>

            <div class="max-sm:mt-3">
                <div class="text-white font-bold mb-2">
                    Другие города
                    <div class="h-0.5 w-7 bg-white/90"></div>
                </div>
                <div class="grid grid-cols-2 gap-1 gap-x-6 text-white/80 text-xs">
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель в Бресте</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель в Витебске</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель в Гомеле</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель в Гродно</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель в Минске</a>
                    </div>
                    <div>
                        <a href="#" class="hover:text-black/80 border-b-gray-200/20 border-b hover:border-b-transparent">Мебель в Могилеве</a>
                    </div>

                </div>
            </div>
        </div>

        <hr class="my-6 border-t border-white/15" />

        <div class="mb-2 text-center text-white/70 text-sm">
            &copy; 2026 Мебельный портал {{ $region->subdomain  }}.{{ config('app.domain') }}
        </div>
    </div>
</footer>
