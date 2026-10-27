<script setup lang="ts">
import AdForm from '@/account/Components/Ads/AdForm.vue';
import type { AdData, AdFilter } from '@/account/Components/Ads/adForm';
import type { CategoryNode, RegionNode } from '@/account/Components/Company/companyForm';
import AccountPageHeader from '@/account/Components/Forms/AccountPageHeader.vue';
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    ad: AdData;
    categories: CategoryNode[];
    regions: RegionNode[];
    currency: string | null;
    currencyDecimals: number;
    filters: AdFilter[];
}>();
</script>

<template>
    <Head :title="$t('Редактирование товар/услуги')" />

    <AccountLayout>
        <AccountPageHeader icon="ads" :title="$t('Редактирование товар/услуги')" :description="$t('Обновите информацию о товаре или услуге.')">
            <template #actions>
                <Link
                    :href="route('account.ads.index')"
                    class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 shadow-xs transition hover:border-green-first hover:text-green-800"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                    {{ $t('Мои товары и услуги') }}
                </Link>
            </template>
        </AccountPageHeader>

        <AdForm
            :ad="ad"
            :categories="categories"
            :regions="regions"
            :initial-filters="filters"
            :currency="currency"
            :currency-decimals="currencyDecimals"
            :copy-url="`${route('account.ads.create')}?copy=${ad.id}`"
        />
    </AccountLayout>
</template>
