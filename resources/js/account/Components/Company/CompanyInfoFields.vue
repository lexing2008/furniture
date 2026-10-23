<script setup lang="ts" generic="T extends CompanyInfoFields">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import TextInput from '@/account/Components/TextInput.vue';
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { CompanyInfoFields, RegionGroup } from './companyForm';

const props = defineProps<{
    form: InertiaForm<T>;
    regions: RegionGroup[];
}>();

// Errors of the fields of a generic form are not resolved by the Inertia typings
const errors = computed(() => props.form.errors as Record<string, string | undefined>);

const section = 'space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs';
const selectClass =
    'mt-1 block w-full rounded-md border-gray-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500';
</script>

<template>
    <section :class="section">
        <h2 class="text-lg font-semibold text-gray-900">Данные компании</h2>

        <div class="grid gap-4 md:grid-cols-2">
            <div>
                <InputLabel for="legal_name" value="Официальное название компании" />
                <TextInput id="legal_name" type="text" class="mt-1 block w-full" v-model="form.legal_name" />
                <InputError class="mt-2" :message="errors.legal_name" />
            </div>

            <div>
                <InputLabel for="tax_id" value="Tax ID" />
                <TextInput id="tax_id" type="text" class="mt-1 block w-full" v-model="form.tax_id" />
                <InputError class="mt-2" :message="errors.tax_id" />
            </div>

            <div>
                <InputLabel for="display_name" value="Отображаемое название компании" />
                <TextInput id="display_name" type="text" class="mt-1 block w-full" v-model="form.display_name" />
                <InputError class="mt-2" :message="errors.display_name" />
            </div>

            <div>
                <InputLabel for="region_id" value="Регион (город)" />
                <select id="region_id" v-model="form.region_id" :class="selectClass">
                    <option :value="null" disabled>— Выберите город —</option>
                    <optgroup v-for="group in regions" :key="group.label" :label="group.label">
                        <option v-for="city in group.cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                    </optgroup>
                </select>
                <InputError class="mt-2" :message="errors.region_id" />
            </div>

            <div class="md:col-span-2">
                <InputLabel for="website" value="Сайт" />
                <TextInput id="website" type="text" class="mt-1 block w-full" v-model="form.website" placeholder="https://example.com" />
                <InputError class="mt-2" :message="errors.website" />
            </div>
        </div>

        <label class="flex items-center gap-2">
            <input
                type="checkbox"
                v-model="form.is_online_store"
                class="rounded-sm border-gray-300 text-indigo-600 shadow-xs focus:ring-indigo-500"
            />
            <span class="text-sm text-gray-700">Является интернет-магазином</span>
        </label>
    </section>
</template>
