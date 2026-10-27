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
    is_active: boolean;
    is_moderated: boolean;
    is_banned: boolean;
    created_at: string | null;
}

interface AdFilters {
    category_id: number | null;
    region_id: number | null;
    price_from: number | null;
    price_to: number | null;
    has_filters: boolean;
    photo: 'with' | 'without' | null;
    has_video: boolean;
}

interface Option {
    id: number;
    label: string;
}

type AdStatus = 'all' | 'active' | 'disabled' | 'moderation' | 'banned';

const props = defineProps<{
    ads: AdRow[];
    currencyDecimals: number;
    status: AdStatus;
    search: string | null;
    adFilters: AdFilters;
    categoryOptions: Option[];
    regionOptions: Option[];
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
const categoryId = ref<number | null>(props.adFilters.category_id);
const regionId = ref<number | null>(props.adFilters.region_id);
const priceFrom = ref(props.adFilters.price_from === null ? '' : String(props.adFilters.price_from));
const priceTo = ref(props.adFilters.price_to === null ? '' : String(props.adFilters.price_to));
const hasFilters = ref(props.adFilters.has_filters);
const photo = ref<'with' | 'without' | null>(props.adFilters.photo);
const hasVideo = ref(props.adFilters.has_video);

watch(
    () => props.search,
    (value) => {
        search.value = value ?? '';
    },
);

watch(
    () => props.adFilters,
    (value) => {
        categoryId.value = value.category_id;
        regionId.value = value.region_id;
        priceFrom.value = value.price_from === null ? '' : String(value.price_from);
        priceTo.value = value.price_to === null ? '' : String(value.price_to);
        hasFilters.value = value.has_filters;
        photo.value = value.photo;
        hasVideo.value = value.has_video;
    },
    { deep: true },
);

const hasAppliedSearch = computed(() => search.value.trim() !== '');
const hasAppliedFilters = computed(
    () =>
        categoryId.value !== null ||
        regionId.value !== null ||
        priceFrom.value !== '' ||
        priceTo.value !== '' ||
        hasFilters.value ||
        photo.value !== null ||
        hasVideo.value,
);

const queryParams = (): Record<string, string> => {
    const params: Record<string, string> = {};

    if (props.status !== 'all') {
        params.status = props.status;
    }

    if (search.value.trim() !== '') {
        params.search = search.value.trim();
    }

    if (categoryId.value !== null) {
        params.category_id = String(categoryId.value);
    }

    if (regionId.value !== null) {
        params.region_id = String(regionId.value);
    }

    if (priceFrom.value !== '') {
        params.price_from = priceFrom.value;
    }

    if (priceTo.value !== '') {
        params.price_to = priceTo.value;
    }

    if (hasFilters.value) {
        params.has_filters = '1';
    }

    if (photo.value !== null) {
        params.photo = photo.value;
    }

    if (hasVideo.value) {
        params.has_video = '1';
    }

    return params;
};

const applyFilters = () => {
    router.get(route('account.ads.index'), queryParams(), {
        preserveState: true,
        preserveScroll: true,
        replace: true,
    });
};

const submitSearch = () => {
    applyFilters();
};

const clearSearch = () => {
    search.value = '';
    applyFilters();
};

const resetFilters = () => {
    categoryId.value = null;
    regionId.value = null;
    priceFrom.value = '';
    priceTo.value = '';
    hasFilters.value = false;
    photo.value = null;
    hasVideo.value = false;
    applyFilters();
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

        <form class="rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm sm:p-5" @submit.prevent="applyFilters">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-50 text-green-700">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                    </svg>
                </span>
                <h2 class="text-sm font-semibold text-gray-800">{{ $t('Фильтры') }}</h2>
            </div>

            <div class="mt-4 grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $t('Категория') }}</span>
                    <select v-model="categoryId" class="block w-full rounded-xl border-gray-300 bg-white text-sm shadow-xs focus:border-green-first focus:ring-green-first">
                        <option :value="null">{{ $t('Все категории') }}</option>
                        <option v-for="option in categoryOptions" :key="option.id" :value="option.id">{{ option.label }}</option>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $t('Регион') }}</span>
                    <select v-model="regionId" class="block w-full rounded-xl border-gray-300 bg-white text-sm shadow-xs focus:border-green-first focus:ring-green-first">
                        <option :value="null">{{ $t('Все регионы') }}</option>
                        <option v-for="option in regionOptions" :key="option.id" :value="option.id">{{ option.label }}</option>
                    </select>
                </label>

                <div>
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $t('Цена') }}</span>
                    <div class="flex items-center gap-2">
                        <input v-model="priceFrom" type="number" min="0" step="0.01" :placeholder="$t('От')" class="block w-full rounded-xl border-gray-300 text-sm shadow-xs focus:border-green-first focus:ring-green-first" />
                        <span class="text-gray-400">—</span>
                        <input v-model="priceTo" type="number" min="0" step="0.01" :placeholder="$t('До')" class="block w-full rounded-xl border-gray-300 text-sm shadow-xs focus:border-green-first focus:ring-green-first" />
                    </div>
                </div>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $t('Фото') }}</span>
                    <select v-model="photo" class="block w-full rounded-xl border-gray-300 bg-white text-sm shadow-xs focus:border-green-first focus:ring-green-first">
                        <option :value="null">{{ $t('Все') }}</option>
                        <option value="with">{{ $t('С фото') }}</option>
                        <option value="without">{{ $t('Без фото') }}</option>
                    </select>
                </label>
            </div>

            <div class="mt-4 flex flex-col gap-3 border-t border-gray-100 pt-4 sm:flex-row sm:items-center sm:justify-between">
                <div class="flex flex-wrap items-center gap-5">
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                        <input v-model="hasFilters" type="checkbox" class="rounded-sm border-gray-300 text-green-first shadow-xs focus:ring-green-first" />
                        {{ $t('Есть параметры фильтров') }}
                    </label>
                    <label class="flex cursor-pointer items-center gap-2 text-sm text-gray-700">
                        <input v-model="hasVideo" type="checkbox" class="rounded-sm border-gray-300 text-green-first shadow-xs focus:ring-green-first" />
                        {{ $t('Есть видео') }}
                    </label>
                </div>

                <div class="flex items-center justify-end gap-2">
                    <button type="button" class="rounded-full border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50" @click="resetFilters">
                        {{ $t('Сбросить') }}
                    </button>
                    <button type="submit" class="rounded-full bg-green-first px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-first/70">
                        {{ $t('Применить') }}
                    </button>
                </div>
            </div>
        </form>

        <p v-if="hasAppliedSearch || hasAppliedFilters" class="text-xs text-gray-500">
            {{ $t('Найдено: :count', { count: ads.length }) }}
        </p>

        <div class="overflow-x-auto rounded-2xl border border-gray-200/80 bg-white shadow-sm">
            <table v-if="ads.length > 0" class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-3 py-3">{{ $t('Название') }}</th>
                        <th class="px-3 py-3">{{ $t('Артикул') }}</th>
                        <th class="px-3 py-3">{{ $t('Номер в собственной системе') }}</th>
                        <th class="px-3 py-3">{{ $t('Категория') }}</th>
                        <th class="px-3 py-3">{{ $t('Статус') }}</th>
                        <th class="px-3 py-3">{{ $t('Цена') }}</th>
                        <th class="px-3 py-3">{{ $t('Параметры') }}</th>
                        <th class="px-3 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="ad in ads" :key="ad.id" class="transition hover:bg-gray-50/70">
                        <td class="px-3 py-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-gray-100 ring-1 ring-gray-200">
                                    <img v-if="ad.thumb_url" :src="ad.thumb_url" :alt="ad.title" loading="lazy" class="h-full w-full object-cover" />
                                    <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0">
                                    <p class="truncate font-semibold text-gray-900">{{ ad.title }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 text-gray-600">{{ ad.article || '—' }}</td>
                        <td class="whitespace-nowrap px-3 py-3 text-gray-600">{{ ad.number || '—' }}</td>
                        <td class="px-3 py-3 text-gray-600">{{ ad.category }}</td>
                        <td class="whitespace-nowrap px-3 py-3">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <span
                                    class="inline-flex h-7 w-7 items-center justify-center rounded-full"
                                    :class="ad.is_active ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-400'"
                                    :title="ad.is_active ? $t('Активен') : $t('Отключён')"
                                    :aria-label="ad.is_active ? $t('Активен') : $t('Отключён')"
                                >
                                    <svg v-if="ad.is_active" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5-6v6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </span>
                                <span v-if="ad.is_banned" class="inline-flex items-center gap-1 rounded-full bg-red-50 px-2 py-1 text-[11px] font-medium text-red-700 ring-1 ring-red-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                                    </svg>
                                    {{ $t('Забанен') }}
                                </span>
                                <span v-else-if="!ad.is_moderated" class="inline-flex items-center gap-1 rounded-full bg-amber-50 px-2 py-1 text-[11px] font-medium text-amber-700 ring-1 ring-amber-200">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    {{ $t('На модерации') }}
                                </span>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-3 font-medium text-gray-800">{{ formatPrice(ad, currencyDecimals) }}</td>
                        <td class="px-3 py-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                </svg>
                                {{ ad.filter_count }}
                            </span>
                        </td>
                        <td class="px-3 py-3 text-right">
                            <Link
                                :href="route('account.ads.edit', ad.id)"
                                class="inline-flex h-8 w-8 items-center justify-center rounded-full border border-green-200 bg-green-50 text-green-800 transition hover:bg-green-100"
                                :title="$t('Редактировать')"
                                :aria-label="$t('Редактировать')"
                            >
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                </svg>
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-else class="px-4 py-10 text-center text-sm text-gray-500">{{ $t('Нет товаров и услуг.') }}</p>
        </div>
    </AccountLayout>
</template>
