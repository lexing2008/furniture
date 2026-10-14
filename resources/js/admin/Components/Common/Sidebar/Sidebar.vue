<script setup lang="ts">
import { ref, watch } from 'vue'
import SidebarProfile from './SidebarProfile.vue'
import NavGroup from "@/admin/Components/Common/Sidebar/NavGroup.vue";
import SubNavLink from "@/admin/Components/Common/Sidebar/SubNavLink.vue";
import MainNavLink from "@/admin/Components/Common/Sidebar/MainNavLink.vue";

// Внутреннее реактивное состояние для мобильного отображения
const isSidebarOpen = ref<boolean>(false)

// (Опционально) Блокируем прокрутку страницы, когда мобильное меню открыто
watch(isSidebarOpen, (newValue) => {
    if (newValue) {
        document.body.classList.add('overflow-hidden')
    } else {
        document.body.classList.remove('overflow-hidden')
    }
})
</script>

<template>
    <!-- Кнопка-бургер: должна быть видна ТОЛЬКО на мобильных (md:hidden) -->
    <!-- Обычно её рендерят в верхней панели (Navbar) основного контента -->
    <div class="md:hidden fixed top-4 left-4 z-40">
        <button
            @click="isSidebarOpen = true"
            class="p-2 rounded-lg bg-slate-900 text-white hover:bg-slate-800 focus:outline-none shadow-md"
        >
            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
            </svg>
        </button>
    </div>

    <!-- Мобильная подложка (Z-index чуть меньше сайдбара) -->
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
            class="fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm md:hidden"
        ></div>
    </Transition>

    <!-- Боковая панель -->
    <!-- Динамический класс :class управляет сдвигом на мобильных (-translate-x-full) -->
    <aside
        class="fixed inset-y-0 left-0 z-50 flex w-80 md:w-72 flex-col bg-slate-950 text-white transition-transform duration-300 ease-in-out md:translate-x-0 md:relative"
        :class="{ 'translate-x-0': isSidebarOpen, '-translate-x-full': !isSidebarOpen }"
    >
        <!-- Брендинг / Логотип -->
        <div class="flex h-16 items-center justify-between px-6 border-b border-slate-800">
            <span class="text-xl font-bold tracking-wider">{{ $page.props.app_name }}</span>

            <!-- Кнопка закрытия для мобильных -->
            <button
                @click="isSidebarOpen = false"
                class="md:hidden text-gray-400 hover:text-white p-1 rounded-md hover:bg-slate-900 focus:outline-none"
            >
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        <!-- Навигационные ссылки -->
        <nav class="flex-1 space-y-1 px-4 py-4 overflow-y-auto">
            <!-- Главная -->
            <MainNavLink routeName="admin.index">
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11V11a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                    </svg>
                </template>
                Главная
            </MainNavLink>

            <!-- Категории мебели -->
            <NavGroup title="Категории" routes="admin.categories.*">
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                    </svg>
                </template>

                <template #links>
                    <SubNavLink name="Добавить" routeName="admin.categories.create" />
                    <SubNavLink name="Все категории" routeName="admin.categories.index" />
                </template>
            </NavGroup>

            <!-- Языки -->
            <NavGroup title="Языки" routes="admin.languages.*">
                <!-- Иконка передается в именной слот #icon -->
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5c-.345 2.896-1.472 5.745-3.339 8.25" />
                    </svg>
                </template>

                <template #links>
                    <SubNavLink name="Добавить" routeName="admin.languages.create" />
                    <SubNavLink name="Все языки" routeName="admin.languages.index" />
                </template>
            </NavGroup>

            <!-- Пользователи (персонал) -->
            <NavGroup v-if="$page.props.auth.user.role === 'admin'" title="Пользователи" routes="admin.users.*">
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </template>

                <template #links>
                    <SubNavLink name="Добавить" routeName="admin.users.create" />
                    <SubNavLink name="Все пользователи" routeName="admin.users.index" />
                </template>
            </NavGroup>


            <!-- Аналитика -->
            <MainNavLink routeName="dashboard">
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14" />
                    </svg>
                </template>
                Аналитика
            </MainNavLink>

            <!-- Настройки -->
            <MainNavLink routeName="logout">
                <template #icon>
                    <svg class="h-5 w-5 mr-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /></svg>
                </template>
                Настройки
            </MainNavLink>

            <!-- ГРУППА С ПОДМЕНЮ (Аналитика) -->
            <NavGroup title="Маркетинг">
                <!-- Иконка передается в именной слот #icon -->
                <template #icon>
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 00-2-2h-2a2 2 0 00-2 2v14" />
                    </svg>
                </template>

                <!-- Ссылки передаются в именной слот #links -->
                <template #links>
                    <SubNavLink name="Обзор" routeName="admin.index" />
                    <SubNavLink name="Продажи" routeName="dashboard" />
                    <SubNavLink name="Пользователи" routeName="admin.index" />
                </template>
            </NavGroup>
        </nav>

        <!-- Подключение компонента профиля -->
        <SidebarProfile />
    </aside>
</template>

<style scoped>

</style>
