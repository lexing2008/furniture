<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';

type AdStatus = 'all' | 'active' | 'disabled' | 'moderation' | 'banned';

const props = defineProps<{
    status: AdStatus;
    label: string;
    count: number;
    active: boolean;
}>();

const href = computed(() =>
    props.status === 'all'
        ? route('account.ads.index')
        : route('account.ads.index', { status: props.status }),
);
</script>

<template>
    <Link
        :href="href"
        :aria-current="active ? 'page' : undefined"
        class="group flex items-center justify-between gap-2 rounded-lg py-2 pl-3 pr-2 text-[13px] font-medium transition-all duration-200"
        :class="active ? 'bg-white text-green-900 shadow-xs' : 'text-green-950/75 hover:bg-white/15 hover:text-green-950'"
    >
        <span class="flex min-w-0 items-center gap-2">
            <span
                class="h-1.5 w-1.5 shrink-0 rounded-full transition-colors"
                :class="active ? 'bg-green-700' : 'bg-green-950/30 group-hover:bg-green-950/60'"
            ></span>
            <span class="truncate">{{ label }}</span>
        </span>

        <span
            class="min-w-6 rounded-full px-1.5 py-0.5 text-center text-[10px] font-semibold tabular-nums"
            :class="active ? 'bg-green-700 text-white' : 'bg-green-950/10 text-green-950/80'"
        >
            {{ count }}
        </span>
    </Link>
</template>
