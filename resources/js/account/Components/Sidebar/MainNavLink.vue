<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    routeName: string;
}>();

// Проверяем, активен ли данный роут (или его дочерние подстраницы)
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
                ? 'bg-white text-green-900 font-semibold shadow-sm'
                : 'text-green-950/85 hover:bg-white/20 hover:text-green-950'
        ]"
    >
        <!-- Индикатор активности у левого края кнопки -->
        <span
            class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-5 rounded-r-full bg-green-700 transition-all duration-200 scale-y-0 opacity-0"
            :class="{ 'scale-y-100 opacity-100': isActive }"
        />

        <!-- Слот для SVG-иконки. Классы автоматически подкрашивают иконку -->
        <div
            class="h-5 w-5 mr-3 transition-colors duration-200"
            :class="[
                isActive
                    ? 'text-green-700'
                    : 'text-green-950/65 group-hover:text-green-950'
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
