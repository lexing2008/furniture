<script setup lang="ts" generic="T extends CompanyDescriptionFields">
import InputError from '@/account/Components/InputError.vue';
import WysiwygEditor from '@/common/Components/WysiwygEditor.vue';
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { CompanyDescriptionFields } from './companyForm';
import { formCard, formTitle } from '@/account/Components/Forms/formStyles';

const props = defineProps<{
    form: InertiaForm<T>;
}>();

// Errors of the fields of a generic form are not resolved by the Inertia typings
const errors = computed(() => props.form.errors as Record<string, string | undefined>);

</script>

<template>
    <section :class="formCard">
        <h2 :class="[formTitle, errors.description ? 'text-red-600' : 'text-gray-900']">{{ $t('Краткое описание') }}</h2>
        <p class="text-sm leading-6 text-gray-500">{{ $t('Абзацы, заголовки, жирный и курсив, списки и таблицы. Остальное при сохранении удаляется.') }}</p>

        <WysiwygEditor
            v-model="form.description"
            profile="company"
            height-key="account-wysiwyg"
            :invalid="!!errors.description"
            resizable
        />
        <InputError :message="errors.description" />
    </section>
</template>
