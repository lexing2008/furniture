<script setup lang="ts">
import AdActualize from '@/account/Components/Ads/AdActualize.vue';
import AdPriceEditor from '@/account/Components/Ads/AdPriceEditor.vue';
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import { translate } from '@/common/i18n';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

interface AdRow {
    id: number;
    title: string;
    article: string | null;
    number: string | null;
    category_id: number;
    category: string;
    price: number;
    currency: string;
    thumb_url: string | null;
    images_count: number;
    filter_count: number;
    published_at: string | null;
    is_active: boolean;
    is_moderated: boolean;
    is_banned: boolean;
    created_at: string | null;
}

interface AdFilters {
    category_id: number | null;
    area_id: number | null;
    city_id: number | null;
    price_from: number | null;
    price_to: number | null;
    has_filters: 'with' | 'without' | null;
    photo: 'with' | 'without' | null;
    has_video: 'with' | 'without' | null;
}

interface Option {
    id: number;
    label: string;
}

interface CityOption extends Option {
    area_id: number;
}

type AdStatus = 'all' | 'active' | 'disabled' | 'moderation' | 'banned';

const props = defineProps<{
    ads: AdRow[];
    currencyDecimals: number;
    status: AdStatus;
    search: string | null;
    adFilters: AdFilters;
    categoryOptions: Option[];
    regionFilterOptions: {
        areas: Option[];
        cities: CityOption[];
    };
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
const areaId = ref<number | null>(props.adFilters.area_id);
const cityId = ref<number | null>(props.adFilters.city_id);
const priceFrom = ref(props.adFilters.price_from === null ? '' : String(props.adFilters.price_from));
const priceTo = ref(props.adFilters.price_to === null ? '' : String(props.adFilters.price_to));
const hasFilters = ref<'with' | 'without' | null>(props.adFilters.has_filters);
const photo = ref<'with' | 'without' | null>(props.adFilters.photo);
const hasVideo = ref<'with' | 'without' | null>(props.adFilters.has_video);
const togglingId = ref<number | null>(null);
const activeStates = ref<Record<number, boolean>>({});
const toggleError = ref<string | null>(null);

const isActive = (ad: AdRow): boolean => activeStates.value[ad.id] ?? ad.is_active;

const csrfToken = (): string => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const toggleActive = async (ad: AdRow) => {
    if (togglingId.value !== null) {
        return;
    }

    togglingId.value = ad.id;
    toggleError.value = null;

    try {
        const response = await fetch(route('account.ads.active.update', ad.id), {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
        });
        const data = (await response.json().catch(() => ({}))) as { is_active?: boolean; message?: string };

        if (!response.ok) {
            throw new Error(data.message ?? translate('Не удалось изменить статус.'));
        }

        activeStates.value = {
            ...activeStates.value,
            [ad.id]: Boolean(data.is_active),
        };
    } catch (error) {
        toggleError.value = error instanceof Error ? error.message : translate('Не удалось изменить статус.');
    } finally {
        togglingId.value = null;
    }
};

const availableCities = computed(() =>
    areaId.value === null
        ? []
        : props.regionFilterOptions.cities.filter((city) => city.area_id === areaId.value),
);

watch(areaId, () => {
    if (cityId.value !== null && !availableCities.value.some((city) => city.id === cityId.value)) {
        cityId.value = null;
    }
});

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
        areaId.value = value.area_id;
        cityId.value = value.city_id;
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
        areaId.value !== null ||
        cityId.value !== null ||
        priceFrom.value !== '' ||
        priceTo.value !== '' ||
        hasFilters.value !== null ||
        photo.value !== null ||
        hasVideo.value !== null,
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

    if (areaId.value !== null) {
        params.area_id = String(areaId.value);
    }

    if (cityId.value !== null) {
        params.city_id = String(cityId.value);
    }

    if (priceFrom.value !== '') {
        params.price_from = priceFrom.value;
    }

    if (priceTo.value !== '') {
        params.price_to = priceTo.value;
    }

    if (hasFilters.value !== null) {
        params.has_filters = hasFilters.value;
    }

    if (photo.value !== null) {
        params.photo = photo.value;
    }

    if (hasVideo.value !== null) {
        params.has_video = hasVideo.value;
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

const filterByCategory = (id: number) => {
    categoryId.value = id;
    applyFilters();
};

const resetFilters = () => {
    categoryId.value = null;
    areaId.value = null;
    cityId.value = null;
    priceFrom.value = '';
    priceTo.value = '';
    hasFilters.value = null;
    photo.value = null;
    hasVideo.value = null;
    applyFilters();
};

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

        <div
            v-if="status === 'moderation'"
            class="flex items-start gap-3 rounded-2xl border border-amber-200/80 bg-amber-50/80 px-4 py-3.5 shadow-sm"
        >
            <span class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-amber-100 text-amber-700">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                </svg>
            </span>
            <div class="min-w-0 pt-0.5">
                <p class="text-sm font-semibold text-amber-900">{{ $t('Товары на модерации отображаются на сайте') }}</p>
                <p class="mt-0.5 text-xs leading-5 text-amber-800">{{ $t('Статус «На модерации» не влияет на отображение на сайте.') }}</p>
            </div>
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
                    type="text"
                    autocomplete="off"
                    enterkeyhint="search"
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
                        class="inline-flex h-9 cursor-pointer items-center justify-center rounded-full bg-green-first px-4 text-xs font-semibold uppercase tracking-widest text-white transition hover:bg-green-first/70"
                    >
                        {{ $t('Найти') }}
                    </button>
                </div>
            </div>
        </form>

        <form class="rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm sm:p-5" @submit.prevent="applyFilters">
            <div class="flex items-center gap-2">
                <span class="flex h-8 w-8 items-center justify-center rounded-full bg-green-first/10 text-green-first">
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
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $t('Область') }}</span>
                    <select v-model="areaId" class="block w-full rounded-xl border-gray-300 bg-white text-sm shadow-xs focus:border-green-first focus:ring-green-first">
                        <option :value="null">{{ $t('Все области') }}</option>
                        <option v-for="option in regionFilterOptions.areas" :key="option.id" :value="option.id">{{ option.label }}</option>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $t('Город') }}</span>
                    <select
                        v-model="cityId"
                        class="block w-full rounded-xl border-gray-300 bg-white text-sm shadow-xs focus:border-green-first focus:ring-green-first disabled:bg-gray-50 disabled:text-gray-400"
                        :disabled="areaId === null"
                    >
                        <option :value="null">{{ areaId === null ? $t('Сначала выберите область') : $t('Все города') }}</option>
                        <option v-for="option in availableCities" :key="option.id" :value="option.id">{{ option.label }}</option>
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

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $t('Параметры фильтра') }}</span>
                    <select v-model="hasFilters" class="block w-full rounded-xl border-gray-300 bg-white text-sm shadow-xs focus:border-green-first focus:ring-green-first">
                        <option :value="null">{{ $t('Все') }}</option>
                        <option value="with">{{ $t('С параметрами фильтра') }}</option>
                        <option value="without">{{ $t('Без параметров фильтра') }}</option>
                    </select>
                </label>

                <label class="block">
                    <span class="mb-1.5 block text-xs font-semibold uppercase tracking-wide text-gray-500">{{ $t('Видео') }}</span>
                    <select v-model="hasVideo" class="block w-full rounded-xl border-gray-300 bg-white text-sm shadow-xs focus:border-green-first focus:ring-green-first">
                        <option :value="null">{{ $t('Все') }}</option>
                        <option value="with">{{ $t('С видео') }}</option>
                        <option value="without">{{ $t('Без видео') }}</option>
                    </select>
                </label>
            </div>

            <div class="mt-4 flex justify-end gap-2 border-t border-gray-100 pt-4">
                    <button type="button" class="cursor-pointer rounded-full border border-gray-200 px-4 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50" @click="resetFilters">
                        {{ $t('Сбросить') }}
                    </button>
                    <button type="submit" class="cursor-pointer rounded-full bg-green-first px-5 py-2 text-sm font-semibold text-white transition hover:bg-green-first/70">
                        {{ $t('Применить') }}
                    </button>
            </div>
        </form>

        <p v-if="hasAppliedSearch || hasAppliedFilters" class="text-xs text-gray-500">
            {{ $t('Найдено: :count', { count: ads.length }) }}
        </p>

        <div v-if="toggleError" class="rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
            {{ toggleError }}
        </div>

        <div class="overflow-x-auto rounded-2xl border border-gray-200/80 bg-white shadow-sm">
            <table v-if="ads.length > 0" class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-1.5 py-3">{{ $t('Название') }}</th>
                        <th class="px-1.5 py-3">{{ $t('Артикул / номер') }}</th>
                        <th class="px-1.5 py-3">{{ $t('Категория') }}</th>
                        <th class="px-1.5 py-3">{{ $t('Статус') }}</th>
                        <th class="px-1.5 py-3">{{ $t('Цена') }}</th>
                        <th class="px-1.5 py-3">{{ $t('Параметры') }}</th>
                        <th class="px-1.5 py-3">{{ $t('Актуален на') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr
                        v-for="ad in ads"
                        :key="ad.id"
                        class="transition hover:bg-gray-50/70"
                        :class="isActive(ad) ? '' : 'opacity-70'"
                    >
                        <td class="px-1.5 py-3">
                            <div class="flex min-w-0 items-center gap-3">
                                <div class="relative h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-gray-100 ring-1 ring-gray-200">
                                    <img v-if="ad.thumb_url" :src="ad.thumb_url" :alt="ad.title" loading="lazy" class="h-full w-full object-cover" />
                                    <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                        </svg>
                                    </div>
                                    <span
                                        v-if="ad.images_count > 0"
                                        class="pointer-events-none absolute bottom-1 right-1 z-10 inline-flex items-center gap-0.5 rounded-full bg-gray-950/70 px-1.5 py-0.5 text-[10px] font-semibold leading-none text-white shadow-sm backdrop-blur-sm"
                                        :title="$t('Изображений: :count', { count: ad.images_count })"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-2.5 w-2.5">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                        </svg>
                                        {{ ad.images_count }}
                                    </span>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <p class="truncate font-semibold text-gray-900">{{ ad.title }}</p>
                                        <Link
                                            :href="route('account.ads.edit', ad.id)"
                                            class="inline-flex h-7 w-7 shrink-0 items-center justify-center rounded-full border border-green-first/20 bg-green-first/10 text-green-first transition hover:bg-green-first/15"
                                            :title="$t('Редактировать')"
                                            :aria-label="$t('Редактировать')"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
                                            </svg>
                                        </Link>
                                        <Link
                                            :href="route('account.ads.create', { copy: ad.id })"
                                            class="inline-flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center rounded-full border border-gray-200 bg-white text-gray-500 transition hover:border-green-first/20 hover:bg-green-first/10 hover:text-green-first"
                                            :title="$t('Создать похожее')"
                                            :aria-label="$t('Создать похожее')"
                                        >
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A1.125 1.125 0 0 0 14.625 4.125h-9.75A1.125 1.125 0 0 0 3.75 5.25v9.75A1.125 1.125 0 0 0 4.875 16.125H9" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 8.25h9A1.125 1.125 0 0 1 19.125 9.375v9A1.125 1.125 0 0 1 18 19.5H9a1.125 1.125 0 0 1-1.125-1.125v-9A1.125 1.125 0 0 1 9 8.25Z" />
                                            </svg>
                                        </Link>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-1.5 py-3 text-gray-600">
                            <div class="space-y-0.5">
                                <p v-if="ad.article">{{ ad.article }}</p>
                                <p v-if="ad.number" :class="ad.article ? 'text-xs text-gray-400' : ''">{{ ad.number }}</p>
                                <p v-if="!ad.article && !ad.number">—</p>
                            </div>
                        </td>
                        <td class="px-1.5 py-3 text-gray-600">
                            <button
                                type="button"
                                class="cursor-pointer rounded-full px-2 py-1 text-left text-sm text-gray-600 transition hover:bg-green-first/10 hover:text-green-first"
                                :title="$t('Показать товары этой категории')"
                                @click="filterByCategory(ad.category_id)"
                            >
                                {{ ad.category }}
                            </button>
                        </td>
                        <td class="whitespace-nowrap px-1.5 py-3">
                            <div class="flex flex-wrap items-center gap-1.5">
                                <button
                                    type="button"
                                    class="inline-flex h-7 w-7 cursor-pointer items-center justify-center rounded-full transition disabled:cursor-wait disabled:opacity-50"
                                    :class="isActive(ad) ? 'bg-green-first/15 text-green-first hover:bg-green-first/25' : 'bg-red-50 text-red-600 ring-1 ring-red-200 hover:bg-red-100'"
                                    :title="isActive(ad) ? $t('Активен') : $t('Не активен')"
                                    :aria-label="isActive(ad) ? $t('Активен') : $t('Не активен')"
                                    :disabled="togglingId === ad.id"
                                    @click="toggleActive(ad)"
                                >
                                    <svg v-if="isActive(ad)" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 9v6m-4.5-6v6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                    </svg>
                                </button>
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
                        <td class="whitespace-nowrap px-1.5 py-3 font-medium text-gray-800">
                            <AdPriceEditor :ad-id="ad.id" :price="ad.price" :currency="ad.currency" :decimals="currencyDecimals" />
                        </td>
                        <td class="px-1.5 py-3">
                            <span class="inline-flex items-center gap-1.5 rounded-full bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-700">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 6h9.75M10.5 6a1.5 1.5 0 1 1-3 0m3 0a1.5 1.5 0 1 0-3 0M3.75 6H7.5m3 12h9.75m-9.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-3.75 0H7.5m9-6h3.75m-3.75 0a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m-9.75 0h9.75" />
                                </svg>
                                {{ ad.filter_count }}
                            </span>
                        </td>
                        <td class="px-1.5 py-3 align-top">
                            <AdActualize :ad-id="ad.id" :published-at="ad.published_at" />
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-else class="px-4 py-10 text-center text-sm text-gray-500">{{ $t('Нет товаров и услуг.') }}</p>
        </div>
    </AccountLayout>
</template>
