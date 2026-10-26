<script setup lang="ts">
import CompanyCategoriesFields from '@/account/Components/Company/CompanyCategoriesFields.vue';
import CompanyDescriptionFields from '@/account/Components/Company/CompanyDescriptionFields.vue';
import CompanyInfoFields from '@/account/Components/Company/CompanyInfoFields.vue';
import type { CategoryNode, CompanyAboutFields, RegionNode } from '@/account/Components/Company/companyForm';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    company: CompanyAboutFields;
    regions: RegionNode[];
    categories: CategoryNode[];
}>();

const form = useForm<CompanyAboutFields>({
    ...props.company,
});

const submit = () => {
    form.put(route('account.general.update'), { preserveScroll: true });
};
</script>

<template>
    <Head :title="$t('О компании')" />

    <AccountLayout>
        <h1 class="text-2xl font-bold text-gray-900">{{ $t('О компании') }}</h1>

        <form class="space-y-6" @submit.prevent="submit">
            <CompanyInfoFields :form="form" :regions="regions" />

            <CompanyDescriptionFields :form="form" />

            <CompanyCategoriesFields :form="form" :categories="categories" />

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">{{ $t('Сохранить') }}</PrimaryButton>
                <span v-if="form.recentlySuccessful" class="text-sm text-green-700">{{ $t('Сохранено') }}</span>
            </div>
        </form>
    </AccountLayout>
</template>
