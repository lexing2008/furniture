<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, ref } from 'vue';

// Булево состояние видимости меню профиля
const isMenuOpen = ref<boolean>(false);

// Ссылка на DOM-элемент контейнера для проверки клика извне
const profileContainer = ref<HTMLDivElement | null>(null);

const toggleMenu = (): void => {
    isMenuOpen.value = !isMenuOpen.value;
};

const closeMenuOnClickOutside = (event: MouseEvent): void => {
    const target = event.target as Node;

    if (profileContainer.value && !profileContainer.value.contains(target)) {
        isMenuOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', closeMenuOnClickOutside);
});

onUnmounted(() => {
    document.removeEventListener('click', closeMenuOnClickOutside);
});
</script>

<template>
    <!-- Контейнер профиля (занимает футер сайдбара) -->
    <div ref="profileContainer" class="relative border-t border-green-950/15 p-4">
        <!-- Кнопка-триггер (клик переключает меню) -->
        <button
            @click="toggleMenu"
            class="flex w-full items-center text-left focus:outline-hidden group"
            aria-haspopup="true"
            :aria-expanded="isMenuOpen"
        >
            <div class="ml-3 overflow-hidden flex-1">
                <p class="text-sm font-semibold text-green-950 transition-colors truncate">
                    {{ $page.props.auth.user.name }}
                </p>
                <p class="text-xs text-green-950/70 truncate">{{ $page.props.auth.user.email }}</p>
            </div>

            <!-- Иконка стрелочки, которая плавно переворачивается при открытии -->
            <svg
                class="h-4 w-4 text-green-950/60 group-hover:text-green-950 transition-transform duration-200"
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
                class="absolute bottom-full left-4 right-4 mb-2 rounded-lg bg-white p-2 shadow-xl border border-green-950/10 z-50"
            >
                <!-- Ссылка на смену пароля -->
                <Link
                    :href="route('account.password.edit')"
                    class="flex items-center rounded-md px-3 py-2 text-sm text-green-950 hover:bg-green-50 hover:text-green-900 transition-colors"
                >
                    <svg class="mr-2 h-4 w-4 text-green-700" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                    </svg>
                    {{ $t('Изменить пароль') }}
                </Link>

                <hr class="my-1 border-green-950/10">

                <!-- Кнопка Выхода -->
                <Link
                    :href="route('account.logout')"
                    method="post"
                    as="button"
                    class="flex w-full items-center rounded-md px-3 py-2 text-sm text-rose-600 hover:bg-rose-50 hover:text-rose-700 transition-colors text-left"
                >
                    <svg class="mr-2 h-4 w-4 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    {{ $t('Выйти') }}
                </Link>
            </div>
        </Transition>
    </div>
</template>
