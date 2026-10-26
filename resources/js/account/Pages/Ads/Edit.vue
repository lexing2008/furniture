<script setup lang="ts">
import AdForm from '@/account/Components/Ads/AdForm.vue';
import type { AdData, AdFilter } from '@/account/Components/Ads/adForm';
import type { CategoryNode, RegionNode } from '@/account/Components/Company/companyForm';
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
        <div class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-2xl font-bold text-gray-900">{{ $t('Редактирование товар/услуги') }}</h1>
            <Link :href="route('account.ads.index')" class="text-sm text-gray-600 underline hover:text-gray-900">
                {{ $t('Мои товары и услуги') }}
            </Link>
        </div>

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
