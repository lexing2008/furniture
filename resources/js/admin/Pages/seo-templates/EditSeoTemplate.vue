<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import SeoTemplateForm from '@/admin/Components/SeoTemplates/SeoTemplateForm.vue';
import { makeTranslations } from '@/admin/Components/SeoTemplates/seoTemplateForm';
import type {
    ExistingSeoTemplateTranslation,
    SeoTemplateFormData,
} from '@/admin/Components/SeoTemplates/seoTemplateForm';
import type { Language } from '@/admin/Components/Categories/categoryForm';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    template: {
        id: number;
        name: string;
        // Keyed by language code; an empty PHP collection arrives as []
        translations: Record<string, ExistingSeoTemplateTranslation> | [];
    };
    languages: Language[];
}>();

const form = useForm<SeoTemplateFormData>({
    name: props.template.name,
    translations: makeTranslations(
        props.languages,
        Array.isArray(props.template.translations) ? {} : props.template.translations,
    ),
});

const submit = () => {
    form.put(route('admin.seo-templates.update', props.template.id));
};
</script>

<template>
    <Head title="Редактирование SEO-шаблона" />

    <AuthenticatedLayout>
        <template #header>
            Редактирование SEO-шаблона
        </template>

        <Content>
            <SeoTemplateForm :form="form" :languages="languages" @submit="submit" />
        </Content>
    </AuthenticatedLayout>
</template>
