<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import FilterForm from '@/admin/Components/Filters/FilterForm.vue';
import type {
    CategoryInfo,
    FilterFormData,
    FilterTypeOption,
    SeoTemplateOption,
} from '@/admin/Components/Filters/filterForm';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    category: CategoryInfo;
    types: FilterTypeOption[];
    seoTemplates: SeoTemplateOption[];
}>();

const form = useForm<FilterFormData>({
    type: props.types[0].value,
    digits_after_point: null,
    is_active: true,
    name: '',
    unit: '',
    variants: [],
});

const submit = () => {
    form.post(route('admin.categories.filters.store', props.category.id));
};
</script>

<template>
    <Head title="Добавить фильтр" />

    <AuthenticatedLayout>
        <template #header>
            Добавить фильтр: {{ category.name }}
        </template>

        <Content>
            <Link
                :href="route('admin.categories.filters.index', category.id)"
                class="mb-6 inline-block text-sm text-gray-600 underline hover:text-gray-900"
            >
                ← Назад к фильтрам категории «{{ category.name }}»
            </Link>

            <FilterForm
                :form="form"
                :types="types"
                :seo-templates="seoTemplates"
                :category-id="category.id"
                :type-locked="false"
                @submit="submit"
            />
        </Content>
    </AuthenticatedLayout>
</template>
