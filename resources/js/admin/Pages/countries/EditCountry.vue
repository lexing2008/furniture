<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

interface Country {
    id: number;
    name: string;
    name_en: string;
    subdomain: string;
    timezone: string | null;
    phone_code: string;
    currency: string;
    currency_decimals: number;
    tax_id_label: string;
}

interface CountryFormData {
    phone_code: string;
    currency: string;
    currency_decimals: number;
    tax_id_label: string;
}

const props = defineProps<{
    country: Country;
}>();

const form = useForm<CountryFormData>({
    phone_code: props.country.phone_code,
    currency: props.country.currency,
    currency_decimals: props.country.currency_decimals,
    tax_id_label: props.country.tax_id_label,
});

const submit = () => {
    form.put(route('admin.countries.update', props.country.id));
};

const inputClass =
    'mt-1 block w-full rounded-md border-gray-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500';
const hint = 'mt-1 text-xs text-gray-500';
</script>

<template>
    <Head :title="`Страна: ${country.name}`" />

    <AuthenticatedLayout>
        <template #header>
            Редактирование страны: {{ country.name }}
        </template>

        <Content>
            <form class="max-w-2xl space-y-6" @submit.prevent="submit">
                <div class="text-sm text-gray-600">
                    <p>{{ country.name }} ({{ country.name_en }}) — {{ country.subdomain }}</p>
                    <p v-if="country.timezone" class="mt-1 font-mono">{{ country.timezone }}</p>
                </div>

                <div>
                    <InputLabel for="phone_code" value="Код телефона" />
                    <TextInput
                        id="phone_code"
                        type="text"
                        class="mt-1 block w-full md:w-48"
                        v-model="form.phone_code"
                        placeholder="375"
                    />
                    <p :class="hint">Только цифры, без знака +.</p>
                    <InputError class="mt-2" :message="form.errors.phone_code" />
                </div>

                <div>
                    <InputLabel for="currency" value="Валюта" />
                    <TextInput
                        id="currency"
                        type="text"
                        class="mt-1 block w-full md:w-48 font-mono uppercase"
                        v-model="form.currency"
                        maxlength="3"
                        placeholder="BYN"
                    />
                    <p :class="hint">Три латинские буквы, код ISO 4217.</p>
                    <InputError class="mt-2" :message="form.errors.currency" />
                </div>

                <div>
                    <InputLabel for="currency_decimals" value="Количество знаков после запятой" />
                    <input
                        id="currency_decimals"
                        type="number"
                        min="0"
                        max="4"
                        step="1"
                        :class="[inputClass, 'md:w-48']"
                        v-model.number="form.currency_decimals"
                    />
                    <p :class="hint">Например, 2 для BYN.</p>
                    <InputError class="mt-2" :message="form.errors.currency_decimals" />
                </div>

                <div>
                    <InputLabel for="tax_id_label" value="Название налогового номера" />
                    <TextInput
                        id="tax_id_label"
                        type="text"
                        class="mt-1 block w-full md:w-72"
                        v-model="form.tax_id_label"
                        maxlength="50"
                        placeholder="ИНН или УНП"
                    />
                    <p :class="hint">Например, ИНН для России или УНП для Беларуси.</p>
                    <InputError class="mt-2" :message="form.errors.tax_id_label" />
                </div>

                <div class="flex items-center gap-4">
                    <PrimaryButton :disabled="form.processing">Сохранить</PrimaryButton>

                    <Link :href="route('admin.countries.index')" class="text-sm text-gray-600 underline hover:text-gray-900">
                        К списку стран
                    </Link>

                    <Link
                        :href="route('admin.regions.edit', country.id)"
                        class="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        Редактировать регион
                    </Link>
                </div>
            </form>
        </Content>
    </AuthenticatedLayout>
</template>
