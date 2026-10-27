<script setup lang="ts" generic="T extends CompanyInfoFields">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import TextInput from '@/account/Components/TextInput.vue';
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { CompanyInfoFields, RegionNode } from './companyForm';
import RegionTree from './RegionTree.vue';
import { formCard, formGrid, formTitle } from '@/account/Components/Forms/formStyles';

const props = defineProps<{
    form: InertiaForm<T>;
    regions: RegionNode[];
}>();

// Errors of the fields of a generic form are not resolved by the Inertia typings
const errors = computed(() => props.form.errors as Record<string, string | undefined>);

</script>

<template>
    <section :class="formCard">
        <h2 :class="[formTitle, 'text-gray-900']">{{ $t('Данные компании') }}</h2>

        <div :class="formGrid">
            <div>
                <InputLabel for="legal_name" :value="$t('Официальное название компании')" required :invalid="!!errors.legal_name" />
                <TextInput id="legal_name" type="text" class="mt-1 block w-full" v-model="form.legal_name" :invalid="!!errors.legal_name" />
                <InputError class="mt-2" :message="errors.legal_name" />
            </div>

            <div>
                <InputLabel for="tax_id" :value="$t('Tax ID')" required :invalid="!!errors.tax_id" />
                <TextInput id="tax_id" type="text" class="mt-1 block w-full" v-model="form.tax_id" :invalid="!!errors.tax_id" />
                <InputError class="mt-2" :message="errors.tax_id" />
            </div>

            <div class="md:col-span-2">
                <InputLabel for="display_name" :value="$t('Отображаемое название компании')" required :invalid="!!errors.display_name" />
                <TextInput id="display_name" type="text" class="mt-1 block w-full" v-model="form.display_name" :invalid="!!errors.display_name" />
                <InputError class="mt-2" :message="errors.display_name" />
            </div>
        </div>

        <div>
            <InputLabel :value="$t('Регион (город)')" required :invalid="!!errors.region_id" />
            <RegionTree v-model="form.region_id" :nodes="regions" :invalid="!!errors.region_id" />
            <InputError class="mt-2" :message="errors.region_id" />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <InputLabel for="legal_address" :value="$t('Юридический адрес')" required :invalid="!!errors.legal_address" />
                <TextInput id="legal_address" type="text" class="mt-1 block w-full" v-model="form.legal_address" :invalid="!!errors.legal_address" />
                <InputError class="mt-2" :message="errors.legal_address" />
            </div>

            <div>
                <InputLabel for="actual_address" :value="$t('Фактический адрес')" :invalid="!!errors.actual_address" />
                <TextInput id="actual_address" type="text" class="mt-1 block w-full" v-model="form.actual_address" :invalid="!!errors.actual_address" />
                <InputError class="mt-2" :message="errors.actual_address" />
            </div>
        </div>

        <div>
            <InputLabel for="website" :value="$t('Сайт')" :invalid="!!errors.website" />
            <TextInput id="website" type="text" class="mt-1 block w-full" v-model="form.website" placeholder="https://example.com" :invalid="!!errors.website" />
            <InputError class="mt-2" :message="errors.website" />
        </div>

        <label class="flex items-center gap-2">
            <input
                type="checkbox"
                v-model="form.is_online_store"
                class="rounded-sm border-gray-300 text-green-first shadow-xs focus:ring-green-first"
            />
            <span class="text-sm text-gray-700">{{ $t('Является интернет-магазином') }}</span>
        </label>
    </section>
</template>
