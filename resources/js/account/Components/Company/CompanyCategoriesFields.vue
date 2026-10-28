<script setup lang="ts" generic="T extends { categories: number[] }">
import InputError from '@/account/Components/InputError.vue';
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import CategoryTree from './CategoryTree.vue';
import type { CategoryNode } from './companyForm';
import { formCard, formTitle } from '@/account/Components/Forms/formStyles';

const props = defineProps<{
    form: InertiaForm<T>;
    categories: CategoryNode[];
}>();

const errors = computed(() => props.form.errors as Record<string, string | undefined>);

</script>

<template>
    <section :class="formCard">
        <h2 :class="[formTitle, errors.categories ? 'text-red-600' : 'text-gray-900']">{{ $t('Категории') }} <span class="text-red-600">*</span></h2>
        <p class="text-sm leading-6 text-gray-500">{{ $t('Компания будет показана в выбранных категориях. Можно выбрать только категории без подкатегорий.') }}</p>

        <CategoryTree :nodes="categories" v-model="form.categories" :invalid="!!errors.categories" />
        <InputError :message="errors.categories" />
    </section>
</template>
