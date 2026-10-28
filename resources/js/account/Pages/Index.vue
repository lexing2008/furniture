<script setup lang="ts">
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import type { PageProps } from '@/account/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface AdCounts {
    all: number;
    active: number;
    disabled: number;
    moderation: number;
    banned: number;
}

interface Task {
    key: string;
    title: string;
    description: string;
    href: string;
    done: boolean;
}

interface RecentAd {
    id: number;
    title: string;
    price: number;
    currency: string;
    thumb_url: string | null;
    published_at: string | null;
    is_active: boolean;
    is_moderated: boolean;
    is_banned: boolean;
}

const props = defineProps<{
    company: {
        name: string;
        region: string | null;
        site_url: string | null;
    };
    counts: AdCounts;
    tasks: Task[];
    completed_tasks: number;
    progress: number;
    recent_ads: RecentAd[];
}>();

const page = usePage<PageProps>();
const numberLocale = computed(() => (page.props.locale === 'en' ? 'en-US' : 'ru-RU'));

const pendingTasks = computed(() => props.tasks.filter((task) => !task.done));

const taskIconPaths: Record<string, string> = {
    schedule: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    exceptions: 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5',
    delivery: 'M8.25 18.75a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM19.5 18.75a1.5 1.5 0 1 1-3 0 1.5 1.5 0 0 1 3 0ZM2.25 6.75h11.25v10.5H2.25V6.75Zm11.25 3h3.879a1.5 1.5 0 0 1 1.06.44l2.561 2.56a1.5 1.5 0 0 1 .44 1.06v2.94h-7.94V9.75Z',
    payment: 'M2.25 8.25h19.5M2.25 9h19.5m-16.5 5.25h6m-6 2.25h3m-3.75 3h15a2.25 2.25 0 0 0 2.25-2.25V6.75A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25v10.5A2.25 2.25 0 0 0 4.5 19.5Z',
    socials: 'M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244',
    contacts: 'M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z',
    description: 'M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z',
};

const taskIcon = (key: string) => taskIconPaths[key] ?? taskIconPaths.description;

const quickActions = [
    {
        key: 'create',
        title: 'Добавить товар/услугу',
        description: 'Создать новую позицию',
        href: route('account.ads.create'),
        icon: 'M12 4.5v15m7.5-7.5h-15',
        highlight: true,
    },
    {
        key: 'ads',
        title: 'Мои товары/услуги',
        description: 'Все ваши позиции',
        href: route('account.ads.index'),
        icon: 'M9.568 3H5.25A2.25 2.25 0 0 0 3 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 0 0 5.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 0 0 9.568 3Z',
        highlight: false,
    },
    {
        key: 'company',
        title: 'Редактировать компанию',
        description: 'Информация о компании',
        href: route('account.general'),
        icon: 'M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6',
        highlight: false,
    },
    {
        key: 'schedule',
        title: 'График работы',
        description: 'Часы работы и выходные',
        href: route('account.schedule'),
        icon: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
        highlight: false,
    },
];

const adsUrl = (status: string) => route('account.ads.index') + (status === 'all' ? '' : `?status=${status}`);

const statCards = computed(() => [
    {
        key: 'all',
        label: 'Всего позиций',
        hint: 'Все товары и услуги компании, включая скрытые, забаненные и ожидающие модерации.',
        value: props.counts.all,
        href: adsUrl('all'),
        iconClass: 'bg-green-first/10 text-green-first ring-green-first/20',
    },
    {
        key: 'active',
        label: 'Активные',
        hint: 'Включённые и не забаненные позиции. Сюда также входят товары на модерации.',
        value: props.counts.active,
        href: adsUrl('active'),
        iconClass: 'bg-green-first/10 text-green-first ring-green-first/20',
    },
    {
        key: 'disabled',
        label: 'Отключенные',
        hint: 'Позиции, скрытые компанией. Они не забанены и могут быть включены снова.',
        value: props.counts.disabled,
        href: adsUrl('disabled'),
        iconClass: 'bg-gray-100 text-gray-600 ring-gray-200',
    },
    {
        key: 'moderation',
        label: 'На модерации',
        hint: 'Позиции, которые ожидают проверки. Они также могут быть включены.',
        value: props.counts.moderation,
        href: adsUrl('moderation'),
        iconClass: 'bg-amber-50 text-amber-700 ring-amber-100',
    },
    {
        key: 'banned',
        label: 'Забаненные',
        hint: 'Позиции, заблокированные администрацией и скрытые с сайта.',
        value: props.counts.banned,
        href: adsUrl('banned'),
        iconClass: 'bg-red-50 text-red-700 ring-red-100',
    },
]);

const formatPrice = (ad: RecentAd) =>
    `${new Intl.NumberFormat(numberLocale.value, { minimumFractionDigits: 2, maximumFractionDigits: 2 }).format(ad.price)} ${ad.currency}`;

const adStatus = (ad: RecentAd) => {
    if (ad.is_banned) {
        return { label: 'Забанен', classes: 'bg-red-50 text-red-700 ring-red-200' };
    }

    if (!ad.is_moderated) {
        return { label: 'На модерации', classes: 'bg-amber-50 text-amber-700 ring-amber-200' };
    }

    if (!ad.is_active) {
        return { label: 'Отключён', classes: 'bg-gray-100 text-gray-600 ring-gray-200' };
    }

    return { label: 'Активен', classes: 'bg-green-first/10 text-green-first ring-green-first/20' };
};
</script>

<template>
    <Head :title="$t('Кабинет компании')" />

    <AccountLayout>
        <div class="space-y-5 sm:space-y-6">
            <!-- Приветствие -->
            <section class="relative overflow-hidden rounded-3xl bg-green-first px-5 py-7 text-white shadow-xl shadow-green-first/20 sm:px-8 sm:py-9">
                <div class="pointer-events-none absolute -right-16 -top-24 h-72 w-72 rounded-full bg-white/10 blur-2xl"></div>
                <div class="pointer-events-none absolute -bottom-32 left-1/3 h-64 w-64 rounded-full bg-white/10 blur-3xl"></div>

                <div class="relative flex flex-col gap-6 lg:flex-row lg:items-end lg:justify-between">
                    <div class="max-w-2xl">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-[11px] font-semibold uppercase tracking-[0.2em] text-white/70">
                                {{ $t('Кабинет компании') }}
                            </p>
                            <span v-if="company.region" class="rounded-full bg-white/10 px-2.5 py-1 text-[11px] font-medium text-white ring-1 ring-white/15">
                                {{ company.region }}
                            </span>
                        </div>
                        <h1 class="mt-2 text-2xl font-bold tracking-tight sm:text-3xl">
                            {{ $t('Здравствуйте, :name', { name: company.name }) }}
                        </h1>
                        <p class="mt-2 max-w-xl text-sm leading-6 text-white/80">
                            {{ $t('Управляйте товарами, следите за актуальностью и заполняйте профиль компании.') }}
                        </p>
                    </div>

                    <div class="flex flex-wrap items-center gap-2">
                        <Link
                            :href="route('account.ads.create')"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-white px-4 py-2.5 text-sm font-semibold text-green-first shadow-sm transition hover:bg-white/90 hover:shadow-md active:scale-[0.98]"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                            {{ $t('Добавить товар/услугу') }}
                        </Link>
                        <a
                            v-if="company.site_url"
                            :href="company.site_url"
                            target="_blank"
                            rel="noopener noreferrer"
                            class="inline-flex cursor-pointer items-center gap-2 rounded-full bg-white/10 px-4 py-2.5 text-sm font-medium text-white ring-1 ring-white/20 transition hover:bg-white hover:text-green-first hover:ring-white"
                        >
                            {{ $t('Открыть сайт') }}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H18m0 0v4.5M18 6l-7.5 7.5M6 6h4.5A2.25 2.25 0 0 1 12.75 8.25v9A2.25 2.25 0 0 1 10.5 19.5h-6A2.25 2.25 0 0 1 2.25 17.25v-9A2.25 2.25 0 0 1 4.5 6Z" />
                            </svg>
                        </a>
                    </div>
                </div>
            </section>

            <!-- Статистика -->
            <section>
                <div class="mb-3 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold tracking-tight text-gray-900">{{ $t('Товары и услуги') }}</h2>
                        <p class="mt-0.5 text-sm text-gray-500">{{ $t('Сводка по позициям компании и их статусам.') }}</p>
                    </div>
                    <Link :href="route('account.ads.index')" class="inline-flex cursor-pointer items-center gap-1 text-xs font-semibold text-green-first transition hover:text-green-first/70">
                        {{ $t('Все товары/услуги') }}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                        </svg>
                    </Link>
                </div>

                <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                <Link
                    v-for="stat in statCards"
                    :key="stat.key"
                    :href="stat.href"
                    :title="$t(stat.hint)"
                    class="group relative rounded-2xl border border-gray-200/80 bg-white p-4 shadow-sm transition duration-200 hover:z-20 hover:-translate-y-0.5 hover:border-green-first/30 hover:shadow-md focus-within:z-20"
                >
                    <div class="flex items-start justify-between gap-2">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl ring-1" :class="stat.iconClass">
                            <svg v-if="stat.key === 'all'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                            </svg>
                            <svg v-else-if="stat.key === 'active'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <svg v-else-if="stat.key === 'disabled'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M18.364 18.364A9 9 0 0 0 5.636 5.636m12.728 12.728A9 9 0 0 1 5.636 5.636m12.728 12.728L5.636 5.636" />
                            </svg>
                            <svg v-else-if="stat.key === 'moderation'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 text-gray-300 transition group-hover:text-green-first">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                        </svg>
                    </div>
                    <p class="mt-4 text-3xl font-bold tracking-tight text-gray-900">{{ stat.value }}</p>
                    <p class="mt-0.5 flex items-center gap-1.5 text-xs font-medium text-gray-500">
                        {{ $t(stat.label) }}
                        <span class="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-gray-300 text-[9px] font-bold leading-none text-gray-400" aria-hidden="true">?</span>
                    </p>

                    <div class="pointer-events-none absolute right-0 top-full z-30 mt-2 w-60 translate-y-1 rounded-xl bg-gray-950 p-3 text-left text-xs leading-5 text-gray-100 opacity-0 shadow-xl transition duration-150 group-hover:translate-y-0 group-hover:opacity-100 group-focus-within:translate-y-0 group-focus-within:opacity-100">
                        <span class="absolute -top-1 right-4 h-2 w-2 rotate-45 bg-gray-950"></span>
                        {{ $t(stat.hint) }}
                    </div>
                </Link>
                </div>
            </section>

            <div class="grid gap-5 xl:grid-cols-[minmax(0,1fr)_340px] xl:gap-6">
                <!-- Заполнение профиля -->
                <section class="flex flex-col rounded-3xl border border-gray-200/80 bg-white p-5 shadow-sm sm:p-6">
                    <div class="flex flex-wrap items-end justify-between gap-3">
                        <div>
                            <h2 class="text-lg font-bold tracking-tight text-gray-900">{{ $t('Завершите оформление') }}</h2>
                            <p class="mt-1 text-sm text-gray-500">
                                {{ $t('Заполнено :done из :total', { done: completed_tasks, total: tasks.length }) }}
                            </p>
                        </div>
                        <span class="rounded-full bg-green-first/10 px-3 py-1.5 text-sm font-bold text-green-first ring-1 ring-green-first/20">
                            {{ progress }}%
                        </span>
                    </div>

                    <div class="mt-4 h-2 overflow-hidden rounded-full bg-gray-100">
                        <div
                            class="h-full rounded-full bg-green-first transition-all duration-500"
                            :style="{ width: `${progress}%` }"
                        ></div>
                    </div>

                    <div v-if="pendingTasks.length" class="mt-5 space-y-2.5">
                        <Link
                            v-for="task in pendingTasks"
                            :key="task.key"
                            :href="task.href"
                            class="group flex items-start gap-3 rounded-2xl border border-gray-100 bg-gray-50/60 p-3.5 transition hover:border-green-first/30 hover:bg-green-first/5"
                        >
                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-700 ring-1 ring-amber-100">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="taskIcon(task.key)" />
                                </svg>
                            </span>
                            <span class="min-w-0 flex-1">
                                <span class="block text-sm font-semibold text-gray-900">{{ $t(task.title) }}</span>
                                <span class="mt-0.5 block text-xs leading-5 text-gray-500">{{ $t(task.description) }}</span>
                            </span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="mt-1.5 h-4 w-4 shrink-0 text-gray-300 transition group-hover:translate-x-0.5 group-hover:text-green-first">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                            </svg>
                        </Link>
                    </div>

                    <div v-else class="mt-5 flex items-center gap-3 rounded-2xl border border-green-first/20 bg-green-first/5 p-4">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-green-first/15 text-green-first">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.4" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $t('Все основные разделы заполнены.') }}</p>
                            <p class="mt-0.5 text-xs text-gray-600">{{ $t('Профиль компании готов к работе.') }}</p>
                        </div>
                    </div>

                    <div class="mt-3 flex flex-col gap-3 rounded-2xl border border-gray-100 bg-gray-50/60 p-3.5 sm:flex-row sm:items-center sm:justify-between">
                        <div class="flex items-start gap-2.5">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-green-first/10 text-green-first ring-1 ring-green-first/15">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5V4.5m0 0L5.25 11.25M12 4.5l6.75 6.75" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-xs font-semibold text-gray-800">{{ $t('Поддерживайте актуальность') }}</p>
                                <p class="mt-0.5 text-[11px] leading-4 text-gray-500">{{ $t('Поднимайте товары, чтобы они оставались заметными.') }}</p>
                            </div>
                        </div>
                        <Link :href="route('account.ads.index')" class="inline-flex shrink-0 cursor-pointer items-center gap-1 rounded-full border border-green-first/20 bg-white px-3 py-1.5 text-[11px] font-semibold text-green-first transition hover:bg-green-first/5">
                            {{ $t('К товарам') }}
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3 w-3">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                            </svg>
                        </Link>
                    </div>

                    <div class="mt-auto pt-4">
                        <div class="flex items-center gap-2.5 rounded-2xl border border-green-first/10 bg-green-first/5 p-3.5">
                            <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-white text-green-first shadow-sm ring-1 ring-green-first/15">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 18v-5.25m0 0a6.75 6.75 0 0 0 6.75-6.75c0-.621-.504-1.125-1.125-1.125H6.375c-.621 0-1.125.504-1.125 1.125A6.75 6.75 0 0 0 12 12.75Zm0 0a3 3 0 0 1-3-3m3 3a3 3 0 0 0 3-3m-3 8.25a.75.75 0 0 0 .75-.75v-.75h-1.5v.75c0 .414.336.75.75.75Z" />
                                </svg>
                            </span>
                            <p class="min-w-0 text-xs leading-5 text-gray-500">
                                {{ $t('Заполненный профиль помогает посетителям быстрее выбрать компанию.') }}
                            </p>
                        </div>
                    </div>
                </section>

                <!-- Быстрые действия -->
                <aside class="flex flex-col rounded-3xl border border-gray-200/80 bg-white p-5 shadow-sm sm:p-6">
                    <h2 class="text-lg font-bold tracking-tight text-gray-900">{{ $t('Быстрые действия') }}</h2>
                    <div class="mt-4 space-y-2">
                        <Link
                            v-for="action in quickActions"
                            :key="action.key"
                            :href="action.href"
                            class="group flex items-center gap-3 rounded-xl px-2 py-2.5 transition hover:bg-green-first/10"
                        >
                            <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-green-first/10 text-green-first ring-1 ring-green-first/20">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" :d="action.icon" />
                                </svg>
                            </span>
                            <span class="flex-1 text-sm font-medium text-gray-800">{{ $t(action.title) }}</span>
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4 text-gray-300 transition group-hover:text-green-first">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                            </svg>
                        </Link>
                    </div>

                    <div class="mt-auto pt-5">
                        <div class="rounded-2xl border border-gray-100 bg-gray-50/60 p-3">
                            <div class="flex items-center justify-between gap-2 text-[11px] font-medium text-gray-500">
                                <span>{{ $t('Профиль компании') }}</span>
                                <span class="font-bold text-green-first">{{ progress }}%</span>
                            </div>
                            <div class="mt-2 h-1.5 overflow-hidden rounded-full bg-gray-200/70">
                                <div class="h-full rounded-full bg-green-first transition-all duration-500" :style="{ width: `${progress}%` }"></div>
                            </div>
                        </div>
                    </div>
                </aside>
            </div>

            <!-- Последние товары -->
            <section class="rounded-3xl border border-gray-200/80 bg-white p-5 shadow-sm sm:p-6">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold tracking-tight text-gray-900">{{ $t('Последние товары/услуги') }}</h2>
                        <p class="mt-1 text-sm text-gray-500">{{ $t('Быстрый доступ к недавно добавленным позициям.') }}</p>
                    </div>
                    <Link :href="route('account.ads.index')" class="inline-flex cursor-pointer items-center gap-1.5 rounded-full border border-green-first/30 bg-green-first/10 px-3.5 py-2 text-xs font-semibold text-green-first transition hover:bg-green-first/15">
                        {{ $t('Все товары/услуги') }}
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-3.5 w-3.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5 15.75 12l-7.5 7.5" />
                        </svg>
                    </Link>
                </div>

                <div v-if="recent_ads.length" class="mt-4 grid gap-3 md:grid-cols-2 lg:grid-cols-3">
                    <Link
                        v-for="ad in recent_ads"
                        :key="ad.id"
                        :href="route('account.ads.edit', ad.id)"
                        class="group flex items-start gap-3 rounded-2xl border border-gray-100 bg-gray-50/60 p-3 transition hover:border-green-first/30 hover:bg-green-first/5"
                    >
                        <div class="h-14 w-14 shrink-0 overflow-hidden rounded-xl bg-gray-100 ring-1 ring-gray-200">
                            <img v-if="ad.thumb_url" :src="ad.thumb_url" :alt="ad.title" loading="lazy" class="h-full w-full object-cover" />
                            <div v-else class="flex h-full w-full items-center justify-center text-gray-300">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" />
                                </svg>
                            </div>
                        </div>
                        <div class="min-w-0 flex-1">
                            <p class="truncate text-sm font-semibold text-gray-900">{{ ad.title }}</p>
                            <span class="mt-1 inline-flex rounded-full px-2 py-0.5 text-[10px] font-semibold ring-1 ring-inset" :class="adStatus(ad).classes">
                                {{ $t(adStatus(ad).label) }}
                            </span>
                            <div class="mt-1.5 flex flex-wrap items-baseline gap-x-2 gap-y-0.5">
                                <span class="text-sm font-bold text-gray-900">{{ formatPrice(ad) }}</span>
                                <span v-if="ad.published_at" class="text-[10px] text-gray-400">{{ ad.published_at }}</span>
                            </div>
                        </div>
                    </Link>
                </div>

                <div v-else class="mt-4 flex flex-col items-start gap-3 rounded-2xl border border-dashed border-gray-200 bg-gray-50/60 p-5 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex items-start gap-3">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-green-first/10 text-green-first ring-1 ring-green-first/20">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                            </svg>
                        </span>
                        <div>
                            <p class="text-sm font-semibold text-gray-900">{{ $t('Пока нет товаров/услуг.') }}</p>
                            <p class="mt-0.5 text-xs text-gray-500">{{ $t('Добавьте первый товар или услугу, чтобы начать продавать.') }}</p>
                        </div>
                    </div>
                    <Link :href="route('account.ads.create')" class="inline-flex cursor-pointer items-center gap-1.5 rounded-full bg-green-first px-4 py-2 text-xs font-semibold text-white transition hover:bg-green-first/80">
                        {{ $t('Добавить первый товар') }}
                    </Link>
                </div>
            </section>
        </div>
    </AccountLayout>
</template>
