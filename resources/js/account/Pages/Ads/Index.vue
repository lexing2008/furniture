<script setup lang="ts">
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

interface AdRow {
    id: number;
    title: string;
    number: string;
    category: string;
    price: number;
    currency: string;
    created_at: string | null;
}

defineProps<{
    ads: AdRow[];
    currencyDecimals: number;
}>();

const formatPrice = (ad: AdRow, decimals: number): string => `${ad.price.toFixed(decimals)} ${ad.currency}`;
</script>

<template>
    <Head :title="$t('Мои товары и услуги')" />

    <AccountLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-gray-900">{{ $t('Мои товары и услуги') }}</h1>
            <Link
                :href="route('account.ads.create')"
                class="rounded-full bg-green-first px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white hover:bg-green-first/70"
            >
                {{ $t('Добавить товар/услугу') }}
            </Link>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white shadow-xs">
            <table v-if="ads.length > 0" class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-xs uppercase tracking-wide text-gray-500">
                    <tr>
                        <th class="px-4 py-3">{{ $t('Название') }}</th>
                        <th class="px-4 py-3">{{ $t('Номер в собственной системе') }}</th>
                        <th class="px-4 py-3">{{ $t('Категория') }}</th>
                        <th class="px-4 py-3">{{ $t('Цена') }}</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="ad in ads" :key="ad.id">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ ad.title }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ ad.number }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ ad.category }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ formatPrice(ad, currencyDecimals) }}</td>
                        <td class="px-4 py-3 text-right">
                            <Link :href="route('account.ads.edit', ad.id)" class="text-green-700 underline hover:text-green-900">
                                {{ $t('Редактировать') }}
                            </Link>
                        </td>
                    </tr>
                </tbody>
            </table>
            <p v-else class="px-4 py-8 text-center text-sm text-gray-500">{{ $t('Нет товаров и услуг.') }}</p>
        </div>
    </AccountLayout>
</template>
