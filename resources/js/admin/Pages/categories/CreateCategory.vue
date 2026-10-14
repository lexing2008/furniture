<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import CategoryForm from '@/admin/Components/Categories/CategoryForm.vue';
import { makeTranslations } from '@/admin/Components/Categories/categoryForm';
import type { CategoryFormData, Language, ParentOption, SeoTemplateOption } from '@/admin/Components/Categories/categoryForm';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    languages: Language[];
    parents: ParentOption[];
    seoTemplates: SeoTemplateOption[];
}>();

const form = useForm<CategoryFormData>({
    parent_id: null,
    is_active: true,
    translations: makeTranslations(props.languages),
});

const submit = () => {
    form.post(route('admin.categories.store'));
};
</script>

<template>
    <Head title="Добавить категорию" />

    <AuthenticatedLayout>
        <template #header>
            Добавить категорию
        </template>

        <Content>
            <CategoryForm
                :form="form"
                :languages="languages"
                :parents="parents"
                :seo-templates="seoTemplates"
                @submit="submit"
            />
        </Content>
    </AuthenticatedLayout>
</template>
