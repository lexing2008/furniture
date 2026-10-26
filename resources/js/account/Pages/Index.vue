<script setup lang="ts">
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head :title="$t('Кабинет компании')" />

    <AccountLayout>
        <h1 class="text-2xl font-bold text-gray-900">{{ $t('Кабинет компании') }}</h1>
    </AccountLayout>
</template>
