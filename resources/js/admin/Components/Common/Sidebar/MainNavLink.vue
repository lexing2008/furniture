<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    routeName: string;
}>();

// Проверяем, активен ли данный роут (или его дочерние подстраницы, например dashboard.*)
const isActive = computed(() => {
    return typeof route !== 'undefined' ? route().current(props.routeName) : false;
});
</script>

<template>
    <Link
        :href="route(props.routeName)"
        class="group relative flex items-center rounded-lg px-4 py-3 text-sm font-medium transition-all duration-200"
        :class="[
            isActive
                ? 'bg-slate-800 text-white font-semibold'
                : 'text-slate-400 hover:bg-slate-900/60 hover:text-white'
        ]"
    >
        <!-- Светящийся индикатор активности у левого края кнопки -->
        <span
            class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 rounded-r-full bg-sky-400 transition-all duration-200 scale-y-0 opacity-0"
            :class="{ 'scale-y-100 opacity-100': isActive }"
        />

        <!-- Слот для SVG-иконки. Классы автоматически подкрашивают иконку -->
        <div
            class="h-5 w-5 mr-3 transition-colors duration-200"
            :class="[
                isActive
                    ? 'text-sky-400'
                    : 'text-slate-400 group-hover:text-slate-200'
            ]"
        >
            <slot name="icon" />
        </div>

        <!-- Текст ссылки, который плавно сдвигается при наведении или активности -->
        <span
            class="transition-transform duration-200"
            :class="{ 'translate-x-0.5': isActive, 'group-hover:translate-x-0.5': !isActive }"
        >
            <slot />
        </span>
    </Link>
</template>
