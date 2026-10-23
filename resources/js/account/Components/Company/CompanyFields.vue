<script setup lang="ts">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import TextInput from '@/account/Components/TextInput.vue';
import WysiwygEditor from '@/common/Components/WysiwygEditor.vue';
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import CategoryTree from './CategoryTree.vue';
import { emptyPhone } from './companyForm';
import type { CategoryNode, CompanyFormFields, RegionGroup } from './companyForm';

const props = defineProps<{
    form: InertiaForm<CompanyFormFields>;
    regions: RegionGroup[];
    categories: CategoryNode[];
}>();

// Nested error keys like "phones.0.code" are not covered by the form typings
const errors = computed(() => props.form.errors as Record<string, string | undefined>);

const phoneError = (index: number, field: string) => errors.value[`phones.${index}.${field}`];

const addPhone = () => props.form.phones.push(emptyPhone());

const removePhone = (index: number) => props.form.phones.splice(index, 1);

const section = 'space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs';
const selectClass =
    'mt-1 block w-full rounded-md border-gray-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500';
const smallButton = 'rounded border border-gray-300 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50';
</script>

<template>
    <div class="space-y-6">
        <section :class="section">
            <h2 class="text-lg font-semibold text-gray-900">О компании</h2>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <InputLabel for="legal_name" value="Официальное название компании" />
                    <TextInput id="legal_name" type="text" class="mt-1 block w-full" v-model="form.legal_name" />
                    <InputError class="mt-2" :message="form.errors.legal_name" />
                </div>

                <div>
                    <InputLabel for="tax_id" value="Tax ID" />
                    <TextInput id="tax_id" type="text" class="mt-1 block w-full" v-model="form.tax_id" />
                    <InputError class="mt-2" :message="form.errors.tax_id" />
                </div>

                <div>
                    <InputLabel for="display_name" value="Отображаемое название компании" />
                    <TextInput id="display_name" type="text" class="mt-1 block w-full" v-model="form.display_name" />
                    <InputError class="mt-2" :message="form.errors.display_name" />
                </div>

                <div>
                    <InputLabel for="region_id" value="Регион (город)" />
                    <select id="region_id" v-model="form.region_id" :class="selectClass">
                        <option :value="null" disabled>— Выберите город —</option>
                        <optgroup v-for="group in regions" :key="group.label" :label="group.label">
                            <option v-for="city in group.cities" :key="city.id" :value="city.id">{{ city.name }}</option>
                        </optgroup>
                    </select>
                    <InputError class="mt-2" :message="form.errors.region_id" />
                </div>

                <div class="md:col-span-2">
                    <InputLabel for="website" value="Сайт" />
                    <TextInput id="website" type="text" class="mt-1 block w-full" v-model="form.website" placeholder="https://example.com" />
                    <InputError class="mt-2" :message="form.errors.website" />
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

        <section :class="section">
            <h2 class="text-lg font-semibold text-gray-900">Краткое описание</h2>
            <p class="text-xs text-gray-500">Абзацы, заголовки, жирный и курсив, списки и таблицы. Остальное при сохранении удаляется.</p>

            <WysiwygEditor v-model="form.description" profile="company" :invalid="!!form.errors.description" />
            <InputError :message="form.errors.description" />
        </section>

        <section :class="section">
            <div class="flex items-center justify-between">
                <h2 class="text-lg font-semibold text-gray-900">Телефоны</h2>
                <button type="button" :class="smallButton" @click="addPhone">+ Добавить телефон</button>
            </div>

            <InputError :message="errors.phones" />

            <div v-for="(phone, index) in form.phones" :key="index" class="grid gap-3 md:grid-cols-[6rem_1fr_2fr_auto] md:items-start">
                <div>
                    <InputLabel :for="`phone_code_${index}`" value="Код" />
                    <TextInput :id="`phone_code_${index}`" type="text" class="mt-1 block w-full" v-model="phone.code" placeholder="+375" />
                    <InputError class="mt-1" :message="phoneError(index, 'code')" />
                </div>

                <div>
                    <InputLabel :for="`phone_number_${index}`" value="Номер" />
                    <TextInput :id="`phone_number_${index}`" type="text" class="mt-1 block w-full" v-model="phone.number" placeholder="29 123-45-67" />
                    <InputError class="mt-1" :message="phoneError(index, 'number')" />
                </div>

                <div>
                    <InputLabel :for="`phone_description_${index}`" value="Что за телефон" />
                    <TextInput
                        :id="`phone_description_${index}`"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="phone.description"
                        placeholder="Отдел продаж"
                    />
                    <InputError class="mt-1" :message="phoneError(index, 'description')" />
                </div>

                <div class="md:pt-6">
                    <button type="button" :class="[smallButton, 'text-red-600']" @click="removePhone(index)">Удалить</button>
                </div>
            </div>
        </section>

        <section :class="section">
            <h2 class="text-lg font-semibold text-gray-900">Категории</h2>
            <p class="text-xs text-gray-500">Компания будет показана в выбранных категориях. Можно выбрать только категории без подкатегорий.</p>

            <CategoryTree :nodes="categories" v-model="form.categories" />
            <InputError :message="errors.categories" />
        </section>
    </div>
</template>
