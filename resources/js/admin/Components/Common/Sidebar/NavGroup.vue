<script setup lang="ts">
import { computed, ref } from 'vue';

const props = defineProps<{
    title: string;
    active?: boolean;
    // Шаблоны роутов группы, например 'admin.languages.*'
    routes?: string | string[];
}>();

// Группа активна, если текущий роут совпадает с одним из её шаблонов
const isActive = computed(() => {
    if (props.active) {
        return true;
    }

    if (!props.routes || typeof route === 'undefined') {
        return false;
    }

    const patterns = Array.isArray(props.routes) ? props.routes : [props.routes];

    return patterns.some((pattern) => route().current(pattern));
});

// Лейаут пересоздаётся при каждом переходе, поэтому группа текущей страницы открывается сразу
const isOpen = ref(isActive.value);
</script>

<template>
    <div class="space-y-1">
        <!-- Кнопка-триггер группы -->
        <button
            @click="isOpen = !isOpen"
            type="button"
            class="w-full flex items-center justify-between rounded-lg px-4 py-3 text-sm font-medium transition-colors group text-slate-400 hover:bg-slate-900/50 hover:text-white"
            :class="{ 'bg-slate-900/40 text-slate-200': isOpen || isActive }"
        >
            <div class="flex items-center">
                <!-- Слот для иконки родительского пункта -->
                <div
                    class="h-5 w-5 mr-3 transition-colors"
                    :class="[isOpen || isActive ? 'text-sky-400' : 'text-slate-400 group-hover:text-slate-200']"
                >
                    <slot name="icon" />
                </div>
                <span>{{ title }}</span>
            </div>

            <!-- Иконка стрелочки (поворачивается при открытии) -->
            <svg
                class="h-4 w-4 transform transition-transform duration-200 text-slate-500 group-hover:text-slate-300"
                :class="{ 'rotate-180 text-sky-400': isOpen }"
                fill="none"
                viewBox="0 0 24 24"
                stroke="currentColor"
            >
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
        </button>

        <!-- Анимированное подменю -->
        <transition
            enter-active-class="transition duration-150 ease-out"
            enter-from-class="transform scale-95 opacity-0 -translate-y-2"
            enter-to-class="transform scale-100 opacity-100 translate-y-0"
            leave-active-class="transition duration-100 ease-in"
            leave-from-class="transform scale-100 opacity-100 translate-y-0"
            leave-to-class="transform scale-95 opacity-0 -translate-y-2"
        >
            <div v-show="isOpen" class="pl-5 pr-2 pb-1 space-y-1 relative border-l border-slate-800 ml-6">
                <!-- Слот для дочерних ссылок -->
                <slot name="links" />
            </div>
        </transition>
    </div>
</template>
