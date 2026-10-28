<script setup lang="ts">
import CompanyCategoriesFields from '@/account/Components/Company/CompanyCategoriesFields.vue';
import CompanyDescriptionFields from '@/account/Components/Company/CompanyDescriptionFields.vue';
import CompanyInfoFields from '@/account/Components/Company/CompanyInfoFields.vue';
import type { CategoryNode, CompanyAboutFields, RegionNode } from '@/account/Components/Company/companyForm';
import AccountFormActions from '@/account/Components/Forms/AccountFormActions.vue';
import AccountPageHeader from '@/account/Components/Forms/AccountPageHeader.vue';
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
        <AccountPageHeader
            icon="company"
            :title="$t('О компании')"
            :description="$t('Расскажите о компании — эти данные видят посетители.')"
        />

        <form class="space-y-6" @submit.prevent="submit">
            <CompanyInfoFields :form="form" :regions="regions" />

            <CompanyDescriptionFields :form="form" />

            <CompanyCategoriesFields :form="form" :categories="categories" />

            <AccountFormActions :processing="form.processing" :success="form.recentlySuccessful" />
        </form>
    </AccountLayout>
</template>
