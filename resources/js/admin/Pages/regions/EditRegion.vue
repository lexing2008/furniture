<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import RegionForm from '@/admin/Components/Regions/RegionForm.vue';
import { makeRegionForm } from '@/admin/Components/Regions/regionForm';
import type {
    ExistingRegion,
    Language,
    ParentOption,
    RegionFormData,
    TimezoneOption,
} from '@/admin/Components/Regions/regionForm';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    region: ExistingRegion & { id: number };
    languages: Language[];
    parents: ParentOption[];
    timezones: TimezoneOption[];
    domain: string;
}>();

const form = useForm<RegionFormData>(makeRegionForm(props.region));

const submit = () => {
    form.put(route('admin.regions.update', props.region.id));
};
</script>

<template>
    <Head title="Редактирование региона" />

    <AuthenticatedLayout>
        <template #header>
            Редактирование региона
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
