<script setup lang="ts">
import { ref, onMounted, onUnmounted } from 'vue'
import {Link} from "@inertiajs/vue3";


// Булево состояние видимости меню (TS выводит тип boolean автоматически)
const isMenuOpen = ref<boolean>(false)

// Ссылка на DOM-элемент контейнера для проверки клика извне
const profileContainer = ref<HTMLDivElement | null>(null)

// Переключение состояния меню
const toggleMenu = (): void => {
    isMenuOpen.value = !isMenuOpen.value
}

// Обработчик клика извне с типизацией глобального события MouseEvent
const closeMenuOnClickOutside = (event: MouseEvent): void => {
    const target = event.target as Node

    if (profileContainer.value && !profileContainer.value.contains(target)) {
        isMenuOpen.value = false
    }
}

// Жизненный цикл компонента
onMounted(() => {
    document.addEventListener('click', closeMenuOnClickOutside)
})

onUnmounted(() => {
    document.removeEventListener('click', closeMenuOnClickOutside)
})

// Методы обработки кликов по элементам меню


</script>

<template>
    <!-- Контейнер профиля (занимает футер сайдбара) -->
    <div ref="profileContainer" class="relative border-t border-slate-800 p-4">

        <!-- Кнопка-триггер (клик переключает меню) -->
        <button
            @click="toggleMenu"
            class="flex w-full items-center text-left focus:outline-hidden group"
            aria-haspopup="true"
            :aria-expanded="isMenuOpen"
        >
<!--            <img-->
<!--                class="h-9 w-9 rounded-full object-cover"-->
<!--                src=""-->
<!--                alt="Аватар"-->
<!--            >-->
            <div class="ml-3 overflow-hidden flex-1">
                <p class="text-sm font-medium text-white group-hover:text-sky-400 transition-colors truncate">
                    {{ $page.props.auth.user.name }}
                </p>
                <p class="text-xs text-slate-400 truncate">{{ $page.props.auth.user.email }}</p>
            </div>

            <!-- Иконка стрелочки, которая плавно переворачивается при открытии -->
            <svg
                class="h-4 w-4 text-slate-400 group-hover:text-white transition-transform duration-200"
                :class="{ 'rotate-180': isMenuOpen }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Всплывающее меню с плавной анимацией Tailwind -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="transform opacity-0 scale-95"
            enter-to-class="transform opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="transform opacity-100 scale-100"
            leave-to-class="transform opacity-0 scale-95"
        >
            <div
                v-show="isMenuOpen"
                class="absolute bottom-full left-4 right-4 mb-2 rounded-lg bg-slate-900 p-2 shadow-xl border border-slate-800 z-50"
            >
                <!-- Ссылка на Профиль -->
                <a
                    :href="route('admin.profile.edit')"
                    class="flex items-center rounded-md px-3 py-2 text-sm text-slate-300 hover:bg-slate-800 hover:text-white transition-colors"
                >
                    <svg class="mr-2 h-4 w-4 text-slate-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                    Profile
                </a>

                <hr class="my-1 border-slate-800">

                <!-- Кнопка Выхода -->
                <Link
                    :href="route('logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center rounded-md px-3 py-2 text-sm text-rose-400 hover:bg-rose-950/30 hover:text-rose-300 transition-colors text-left"
                >
                    <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Log Out
                </Link>
            </div>
        </Transition>

    </div>
</template>

<style scoped>

</style>
