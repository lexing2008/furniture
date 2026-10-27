<script setup lang="ts" generic="T extends { categories: number[] }">
import InputError from '@/account/Components/InputError.vue';
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import CategoryTree from './CategoryTree.vue';
import type { CategoryNode } from './companyForm';

const props = defineProps<{
    form: InertiaForm<T>;
    categories: CategoryNode[];
}>();

const errors = computed(() => props.form.errors as Record<string, string | undefined>);

const section = 'space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs';
</script>

<template>
    <section :class="section">
        <h2 class="text-lg font-semibold" :class="errors.categories ? 'text-red-600' : 'text-gray-900'">{{ $t('Категории') }} <span class="text-red-600">*</span></h2>
        <p class="text-xs text-gray-500">{{ $t('Компания будет показана в выбранных категориях. Можно выбрать только категории без подкатегорий.') }}</p>

        <CategoryTree :nodes="categories" v-model="form.categories" :invalid="!!errors.categories" />
        <InputError :message="errors.categories" />
    </section>
</template>
