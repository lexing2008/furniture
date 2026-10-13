<script setup lang="ts">
import { computed } from 'vue';
import { Link } from '@inertiajs/vue3';

const props = defineProps<{
    name: string;
    routeName: string;
}>();

// Проверяем, активен ли данный роут в текущий момент
// (работает и для точного совпадения, и для вложенных роутов, например, 'orders.*')
const isActive = computed(() => {
    // Используем опциональную цепочку на случай, если route() еще не инициализирован
    return typeof route !== 'undefined' ? route().current(props.routeName) : false;
});
</script>

<template>
    <Link
        :href="route(props.routeName)"
        class="group relative block rounded-md px-3 py-2 text-sm font-medium transition-all duration-200"
        :class="[
            isActive
                ? 'bg-slate-900 text-sky-400 font-semibold'
                : 'text-slate-400 hover:bg-slate-900/60 hover:text-slate-200'
        ]"
    >
        <!-- Красивый светящийся маркер слева для активного пункта -->
        <span
            class="absolute left-0 top-1/2 -translate-y-1/2 w-1 h-4 rounded-r-full bg-sky-400 transition-all duration-200 scale-y-0 opacity-0"
            :class="{ 'scale-y-100 opacity-100': isActive }"
        />

        <!-- Сдвигаем текст чуть вправо при ховере или если пункт активен -->
        <span
            class="inline-block transition-transform duration-200"
            :class="{ 'translate-x-1': isActive, 'group-hover:translate-x-1': !isActive }"
        >
            {{ props.name }}
        </span>
    </Link>
</template>
