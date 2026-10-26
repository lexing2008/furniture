<script setup lang="ts">
import AdForm from '@/account/Components/Ads/AdForm.vue';
import type { AdCopySource, AdFilter } from '@/account/Components/Ads/adForm';
import type { CategoryNode, RegionNode } from '@/account/Components/Company/companyForm';
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps<{
    categories: CategoryNode[];
    regions: RegionNode[];
    defaultRegionId: number | null;
    currency: string | null;
    currencyDecimals: number;
    source: AdCopySource | null;
    filters: AdFilter[];
}>();
</script>

<template>
    <Head :title="$t('Добавление товара/услуги')" />

    <AccountLayout>
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-gray-900">{{ $t('Добавление товара/услуги') }}</h1>
            <Link :href="route('account.ads.index')" class="text-sm text-gray-600 underline hover:text-gray-900">
                {{ $t('Мои товары и услуги') }}
            </Link>
        </div>

        <AdForm
            :categories="categories"
            :regions="regions"
            :default-region-id="defaultRegionId"
            :currency="currency"
            :currency-decimals="currencyDecimals"
            :source="source"
            :initial-filters="filters"
        />
    </AccountLayout>
</template>
