<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import FilterForm from '@/admin/Components/Filters/FilterForm.vue';
import { emptyVariant } from '@/admin/Components/Filters/filterForm';
import type {
    CategoryInfo,
    FilterFormData,
    FilterTypeOption,
    SeoTemplateOption,
} from '@/admin/Components/Filters/filterForm';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    category: CategoryInfo;
    filter: FilterFormData & { id: number };
    types: FilterTypeOption[];
    seoTemplates: SeoTemplateOption[];
}>();

const form = useForm<FilterFormData>({
    type: props.filter.type,
    digits_after_point: props.filter.digits_after_point,
    is_active: props.filter.is_active,
    name: props.filter.name,
    unit: props.filter.unit,
    variants: props.filter.variants,
});

// "+ Добавить вариант" on the translation page leads here: variants are created in the base language
if (
    typeof window !== 'undefined' &&
    new URLSearchParams(window.location.search).has('add') &&
    props.types.find((type) => type.value === props.filter.type)?.has_variants
) {
    form.variants.push(emptyVariant());
}

const submit = () => {
    form.put(route('admin.filters.update', props.filter.id));
};
</script>

<template>
    <Head title="Редактирование фильтра" />

    <AuthenticatedLayout>
        <template #header>
            Редактирование фильтра: {{ category.name }}
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
                type-locked
                @submit="submit"
            />
        </Content>
    </AuthenticatedLayout>
</template>
