<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import RegionForm from '@/admin/Components/Regions/RegionForm.vue';
import { makeRegionForm } from '@/admin/Components/Regions/regionForm';
import type { Language, ParentOption, RegionFormData, TimezoneOption } from '@/admin/Components/Regions/regionForm';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    languages: Language[];
    parents: ParentOption[];
    timezones: TimezoneOption[];
    domain: string;
}>();

const form = useForm<RegionFormData>(makeRegionForm());

const submit = () => {
    form.post(route('admin.regions.store'));
};
</script>

<template>
    <Head title="Добавить регион" />

    <AuthenticatedLayout>
        <template #header>
            Добавить регион
        </template>

        <Content>
            <RegionForm
                :form="form"
                :languages="languages"
                :parents="parents"
                :timezones="timezones"
                :domain="domain"
                @submit="submit"
            />
        </Content>
    </AuthenticatedLayout>
</template>
