<script setup lang="ts">
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface AdRow {
    id: number;
    title: string;
    article: string | null;
    number: string | null;
    category: string;
    price: number;
    currency: string;
    thumb_url: string | null;
    filter_count: number;
    created_at: string | null;
}

type AdStatus = 'all' | 'active' | 'disabled' | 'moderation' | 'banned';

const props = defineProps<{
    ads: AdRow[];
    currencyDecimals: number;
    status: AdStatus;
    search: string | null;
}>();

const statusTitles: Record<AdStatus, string> = {
    all: 'Мои товары/услуги',
    active: 'Активные товары/услуги',
    disabled: 'Отключенные товары/услуги',
    moderation: 'Товары/услуги на модерации',
    banned: 'Забаненные товары/услуги',
};

const pageTitle = computed(() => statusTitles[props.status] ?? statusTitles.all);
const search = ref(props.search ?? '');

watch(
    () => props.search,
    (value) => {
        search.value = value ?? '';
    },
);

const submitSearch = () => {
    const params: Record<string, string> = {};

    if (props.status !== 'all') {
        params.status = props.status;
    }

    const value = search.value.trim();

    if (value !== '') {
        params.search = value;
    }

    router.get(route('account.ads.index'), params, {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const clearSearch = () => {
    search.value = '';
    submitSearch();
};

const formatPrice = (ad: AdRow, decimals: number): string => `${ad.price.toFixed(decimals)} ${ad.currency}`;
</script>

<template>
    <Head :title="$t(pageTitle)" />

    <AccountLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-gray-900">{{ $t(pageTitle) }}</h1>
            <Link
                :href="route('account.ads.create')"
                class="rounded-full bg-green-first px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-first/70"
            >
                {{ $t('Добавить товар/услугу') }}
            </Link>
        </div>

        <form class="rounded-2xl border border-gray-200/80 bg-white p-2 shadow-sm" @submit.prevent="submitSearch">
            <div class="relative">
                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.604 10.604Z" />
                    </svg>
                </span>

                <input
                    v-model="search"
                    type="search"
                    class="h-12 w-full rounded-xl border-0 bg-transparent pl-11 pr-32 text-sm text-gray-900 placeholder-gray-400 focus:outline-none focus:ring-0"
                    :placeholder="$t('Поиск по названию, артикулу или номеру')"
                />

                <div class="absolute inset-y-0 right-2 flex items-center gap-1">
                    <button
                        v-if="search.trim() !== ''"
                        type="button"
                        class="flex h-8 w-8 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                        :aria-label="$t('Очистить поиск')"
                        @click="clearSearch"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                    <button
                        type="submit"
                        class="inline-flex h-9 items-center justify-center rounded-full bg-green-first px-4 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-green-first/70"
                    >
                        {{ $t('Найти') }}
                    </button>
                </div>
            </div>
        </form>

        <p v-if="search.trim() !== ''" class="text-xs text-gray-500">
            {{ $t('Найдено: :count', { count: ads.length }) }}
        </p>

        <div class="overflow-x-auto rounded-2xl border border-gray-200/80 bg-white shadow-sm">
            <table v-if="ads.length > 0" class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">{{ $t('Название') }}</th>
                        <th class="px-4 py-3">{{ $t('Номер в собственной системе') }}</th>
                        <th class="px-4 py-3">{{ $t('Категория') }}</th>
                        <th class="px-4 py-3">{{ $t('Цена') }}</th>
                        <th class="px-4 py-3">{{ $t('Параметры') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="ad in ads" :key="ad.id" class="transition hover:bg-gray-50/70">
                        <td class="px-4 py-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-gray-100 ring-1 ring-gray-200">
                                    <img
                                        v-if="ad.thumb_url"
                                        :src="ad.thumb_url"
                                        :alt="ad.title"
                                        loading="lazy"
                                        class="h-full w-full object-cover"
                                    />
                                    <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-900">{{ ad.title }}</p>
                                    <p v-if="ad.article" class="mt-0.5 truncate text-xs text-gray-500">
                                        {{ $t('Артикул') }}: {{ ad.article }}
                                    </p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-4 py-3 text-gray-600">{{ ad.number || '—' }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ ad.category }}</td>
                        <td class="whitespace-nowrap px-4 py-3 font-medium text-gray-800">{{ formatPrice(ad, currencyDecimals) }}</td>
                        <td class="px-4 py-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                </svg>
                                {{ ad.filter_count }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <Link
                                :href="route('account.ads.edit', ad.id)"
                                class="inline-flex items-center rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-800 transition hover:bg-green-100"
                            >
                                {{ $t('Редактировать') }}
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-else class="px-4 py-10 text-center text-sm text-gray-500">{{ $t('Нет товаров и услуг.') }}</p>
        </div>
    </AccountLayout>
</template>
