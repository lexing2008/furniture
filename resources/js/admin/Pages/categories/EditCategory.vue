<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import CategoryForm from '@/admin/Components/Categories/CategoryForm.vue';
import { makeTranslations } from '@/admin/Components/Categories/categoryForm';
import type {
    CategoryFormData,
    ExistingTranslation,
    Language,
    ParentOption,
} from '@/admin/Components/Categories/categoryForm';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    category: {
        id: number;
        parent_id: number | null;
        is_active: boolean;
        // Keyed by language code; an empty PHP collection arrives as []
        translations: Record<string, ExistingTranslation> | [];
    };
    languages: Language[];
    parents: ParentOption[];
}>();

const form = useForm<CategoryFormData>({
    parent_id: props.category.parent_id,
    is_active: props.category.is_active,
    translations: makeTranslations(
        props.languages,
        Array.isArray(props.category.translations) ? {} : props.category.translations,
    ),
});

const submit = () => {
    form.put(route('admin.categories.update', props.category.id));
};
</script>

<template>
    <Head title="Редактирование категории" />

    <AuthenticatedLayout>
        <template #header>
            Редактирование категории
        </template>

        <Content>
            <CategoryForm
                :form="form"
                :languages="languages"
                :parents="parents"
                @submit="submit"
            />
        </Content>
    </AuthenticatedLayout>
</template>
