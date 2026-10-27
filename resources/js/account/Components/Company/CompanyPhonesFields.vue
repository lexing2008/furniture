<script setup lang="ts" generic="T extends { phones: PhoneData[] }">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import TextInput from '@/account/Components/TextInput.vue';
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { emptyPhone } from './companyForm';
import type { PhoneData } from './companyForm';
import { formCard, formTitle } from '@/account/Components/Forms/formStyles';

const props = defineProps<{
    form: InertiaForm<T>;
    // Phone code of the country without "+"
    phoneCode?: string | null;
}>();

// Nested error keys like "phones.0.code" are not covered by the form typings
const errors = computed(() => props.form.errors as Record<string, string | undefined>);

const phoneError = (index: number, field: string) => errors.value[`phones.${index}.${field}`];

const addPhone = () => props.form.phones.push(emptyPhone());

const removePhone = (index: number) => props.form.phones.splice(index, 1);

const codePlaceholder = computed(() => (props.phoneCode ? `+${props.phoneCode}` : '+'));

const smallButton =
    'inline-flex cursor-pointer items-center gap-1 rounded-full border border-gray-300 bg-white px-3 py-1.5 text-xs font-medium text-gray-600 transition hover:border-green-first hover:text-green-800';
const addButton =
    'inline-flex cursor-pointer items-center gap-1 rounded-full border border-green-200 bg-green-50 px-3 py-1.5 text-xs font-semibold text-green-800 transition hover:bg-green-100';
</script>

<template>
    <section :class="formCard">
        <div class="flex items-center justify-between">
            <h2 :class="[formTitle, errors.phones ? 'text-red-600' : 'text-gray-900']">{{ $t('Телефоны') }} <span class="text-red-600">*</span></h2>
            <button type="button" :class="addButton" @click="addPhone">{{ $t('+ Добавить телефон') }}</button>
        </div>

        <InputError :message="errors.phones" />

        <div v-for="(phone, index) in form.phones" :key="index" class="grid gap-3 rounded-xl border border-gray-200/80 bg-gray-50/50 p-4 md:grid-cols-[6rem_1fr_2fr_auto] md:items-start">
            <div>
                <InputLabel :for="`phone_code_${index}`" :value="$t('Код')" required :invalid="!!phoneError(index, 'code')" />
                <TextInput
                    :id="`phone_code_${index}`"
                    :invalid="!!phoneError(index, 'code')"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="phone.code"
                    :placeholder="codePlaceholder"
                />
                <InputError class="mt-1" :message="phoneError(index, 'code')" />
            </div>

            <div>
                <InputLabel :for="`phone_number_${index}`" :value="$t('Номер')" required :invalid="!!phoneError(index, 'number')" />
                <TextInput :id="`phone_number_${index}`" type="text" class="mt-1 block w-full" v-model="phone.number" placeholder="29 123-45-67" :invalid="!!phoneError(index, 'number')" />
                <InputError class="mt-1" :message="phoneError(index, 'number')" />
            </div>

            <div>
                <InputLabel :for="`phone_description_${index}`" :value="$t('Что за телефон')" :invalid="!!phoneError(index, 'description')" />
                <TextInput
                    :id="`phone_description_${index}`"
                    :invalid="!!phoneError(index, 'description')"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="phone.description"
                    :placeholder="$t('Отдел продаж')"
                />
                <InputError class="mt-1" :message="phoneError(index, 'description')" />
            </div>

            <div class="md:pt-6">
                <button type="button" :class="[smallButton, 'text-red-600']" @click="removePhone(index)">{{ $t('Удалить') }}</button>
            </div>
        </div>
    </section>
</template>
