<script setup lang="ts" generic="T extends { phones: PhoneData[] }">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import TextInput from '@/account/Components/TextInput.vue';
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { emptyPhone } from './companyForm';
import type { PhoneData } from './companyForm';

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

const section = 'space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs';
const smallButton = 'cursor-pointer rounded border border-gray-300 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50';
</script>

<template>
    <section :class="section">
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-gray-900">{{ $t('Телефоны') }}</h2>
            <button type="button" :class="smallButton" @click="addPhone">{{ $t('+ Добавить телефон') }}</button>
        </div>

        <InputError :message="errors.phones" />

        <div v-for="(phone, index) in form.phones" :key="index" class="grid gap-3 md:grid-cols-[6rem_1fr_2fr_auto] md:items-start">
            <div>
                <InputLabel :for="`phone_code_${index}`" :value="$t('Код')" />
                <TextInput
                    :id="`phone_code_${index}`"
                    type="text"
                    class="mt-1 block w-full"
                    v-model="phone.code"
                    :placeholder="codePlaceholder"
                />
                <InputError class="mt-1" :message="phoneError(index, 'code')" />
            </div>

            <div>
                <InputLabel :for="`phone_number_${index}`" :value="$t('Номер')" />
                <TextInput :id="`phone_number_${index}`" type="text" class="mt-1 block w-full" v-model="phone.number" placeholder="29 123-45-67" />
                <InputError class="mt-1" :message="phoneError(index, 'number')" />
            </div>

            <div>
                <InputLabel :for="`phone_description_${index}`" :value="$t('Что за телефон')" />
                <TextInput
                    :id="`phone_description_${index}`"
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
