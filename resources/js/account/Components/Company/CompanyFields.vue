<script setup lang="ts">
import type { InertiaForm } from '@inertiajs/vue3';
import CompanyCategoriesFields from './CompanyCategoriesFields.vue';
import CompanyDescriptionFields from './CompanyDescriptionFields.vue';
import CompanyInfoFields from './CompanyInfoFields.vue';
import CompanyPhonesFields from './CompanyPhonesFields.vue';
import type { CategoryNode, CompanyFormFields, RegionGroup } from './companyForm';

defineProps<{
    form: InertiaForm<CompanyFormFields>;
    regions: RegionGroup[];
    categories: CategoryNode[];
}>();
</script>

<template>
    <div class="space-y-6">
        <CompanyInfoFields :form="form" :regions="regions" />
        <CompanyDescriptionFields :form="form" />
        <CompanyPhonesFields :form="form" />
        <CompanyCategoriesFields :form="form" :categories="categories" />
    </div>
</template>
