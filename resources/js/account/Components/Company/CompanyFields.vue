<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import CompanyCategoriesFields from './CompanyCategoriesFields.vue';
import CompanyDescriptionFields from './CompanyDescriptionFields.vue';
import CompanyInfoFields from './CompanyInfoFields.vue';
import CompanyPhonesFields from './CompanyPhonesFields.vue';
import type { CategoryNode, CompanyFormFields, RegionNode } from './companyForm';

defineProps<{
    form: InertiaForm<CompanyFormFields>;
    regions: RegionNode[];
    categories: CategoryNode[];
    phoneCode?: string | null;
    taxIdLabel?: string;
}>();
</script>

<template>
    <div class="space-y-6">
        <CompanyInfoFields :form="form" :regions="regions" :tax-id-label="taxIdLabel" />
        <CompanyDescriptionFields :form="form" />
        <CompanyPhonesFields :form="form" :phone-code="phoneCode" />
        <CompanyCategoriesFields :form="form" :categories="categories" />
    </div>
</template>
