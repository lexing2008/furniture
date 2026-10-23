<script setup lang="ts">
import MainNavLink from '@/account/Components/Sidebar/MainNavLink.vue';
import SidebarProfile from '@/account/Components/Sidebar/SidebarProfile.vue';
import { onUnmounted, ref, watch } from 'vue';

// Внутреннее реактивное состояние для мобильного отображения
const isSidebarOpen = ref<boolean>(false);

// Блокируем прокрутку страницы, когда мобильное меню открыто
watch(isSidebarOpen, (newValue) => {
    if (newValue) {
        document.body.classList.add('overflow-hidden');
    } else {
        document.body.classList.remove('overflow-hidden');
    }
});

// После перехода по ссылке лейаут пересоздаётся, а класс на <body> остался бы
onUnmounted(() => {
    document.body.classList.remove('overflow-hidden');
});
</script>

<template>
    <!-- Кнопка-бургер: видна только на мобильных -->
    <div class="md:hidden fixed top-4 left-4 z-40">
        <button
            @click="isSidebarOpen = true"
            class="p-2 rounded-lg bg-green-second text-white hover:opacity-90 focus:outline-hidden shadow-md"
        >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Мобильная подложка -->
    <Transition
        enter-active-class="transition-opacity ease-linear duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity ease-linear duration-300"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="isSidebarOpen"
            @click="isSidebarOpen = false"
            class="fixed inset-0 z-40 bg-green-950/50 backdrop-blur-xs md:hidden"
        ></div>
    </Transition>

    <!-- Боковая панель -->
    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-80 md:w-72 flex-col bg-green-first text-green-950 transition-transform duration-300 ease-in-out md:translate-x-0 md:relative"
        :class="{ 'translate-x-0': isSidebarOpen, '-translate-x-full': !isSidebarOpen }"
    >
        <!-- Брендинг / Логотип -->
        <div class="flex h-16 items-center justify-between px-6 border-b border-green-950/15">
            <div class="overflow-hidden">
                <p class="text-xl font-bold tracking-wider truncate text-green-950">{{ $page.props.app_name }}</p>
                <p class="text-xs text-green-950/70">Кабинет компании</p>
            </div>

            <!-- Кнопка закрытия для мобильных -->
            <button
                @click="isSidebarOpen = false"
                class="md:hidden text-green-950/70 hover:text-green-950 p-1 rounded-md hover:bg-white/20 focus:outline-hidden"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Навигационные ссылки -->
        <nav class="flex-1 space-y-1 px-4 py-4 overflow-y-auto">
            <!-- О компании -->
            <MainNavLink routeName="account.index">
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                    </svg>
                </template>
                О компании
            </MainNavLink>

            <!-- Контактная информация -->
            <MainNavLink routeName="account.contacts.edit">
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                    </svg>
                </template>
                Контактная информация
            </MainNavLink>

            <!-- Соцсети -->
            <MainNavLink routeName="account.socials.edit">
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342a3 3 0 100-2.684m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684zm0 9.316a3 3 0 105.367 2.684 3 3 0 00-5.367-2.684z" />
                    </svg>
                </template>
                Соцсети
            </MainNavLink>

            <!-- Изменить пароль -->
            <MainNavLink routeName="account.password.edit">
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                </template>
                Изменить пароль
            </MainNavLink>
        </nav>

        <!-- Профиль и выход -->
        <SidebarProfile />
    </aside>
</template>
