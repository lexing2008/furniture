<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import SeoTemplateForm from '@/admin/Components/SeoTemplates/SeoTemplateForm.vue';
import { makeTranslations } from '@/admin/Components/SeoTemplates/seoTemplateForm';
import type { SeoTemplateFormData } from '@/admin/Components/SeoTemplates/seoTemplateForm';
import type { Language } from '@/admin/Components/Categories/categoryForm';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    languages: Language[];
}>();

const form = useForm<SeoTemplateFormData>({
    name: '',
    translations: makeTranslations(props.languages),
});

const submit = () => {
    form.post(route('admin.seo-templates.store'));
};
</script>

<template>
    <Head title="Добавить SEO-шаблон" />

    <AuthenticatedLayout>
        <template #header>
            Добавить SEO-шаблон
        </template>

        <Content>
            <SeoTemplateForm :form="form" :languages="languages" @submit="submit" />
        </Content>
    </AuthenticatedLayout>
</template>
