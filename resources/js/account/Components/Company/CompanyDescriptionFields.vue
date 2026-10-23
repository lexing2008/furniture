<script setup lang="ts" generic="T extends CompanyDescriptionFields">
import InputError from '@/account/Components/InputError.vue';
import WysiwygEditor from '@/common/Components/WysiwygEditor.vue';
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { CompanyDescriptionFields } from './companyForm';

const props = defineProps<{
    form: InertiaForm<T>;
}>();

// Errors of the fields of a generic form are not resolved by the Inertia typings
const errors = computed(() => props.form.errors as Record<string, string | undefined>);

const section = 'space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs';
</script>

<template>
    <section :class="section">
        <h2 class="text-lg font-semibold text-gray-900">Краткое описание</h2>
        <p class="text-xs text-gray-500">Абзацы, заголовки, жирный и курсив, списки и таблицы. Остальное при сохранении удаляется.</p>

        <WysiwygEditor v-model="form.description" profile="company" :invalid="!!errors.description" />
        <InputError :message="errors.description" />
    </section>
</template>
