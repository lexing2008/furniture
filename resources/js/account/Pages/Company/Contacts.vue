<script setup lang="ts">
import CompanyPhonesFields from '@/account/Components/Company/CompanyPhonesFields.vue';
import { emptyPhone } from '@/account/Components/Company/companyForm';
import type { CompanyContactsFields } from '@/account/Components/Company/companyForm';
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import type { PageProps } from '@/account/types';
import { Head, useForm, usePage } from '@inertiajs/vue3';

const props = defineProps<{
    company: CompanyContactsFields;
    phoneCode: string | null;
}>();

const page = usePage<PageProps>();

const form = useForm<CompanyContactsFields>({
    email: props.company.email,
    phones: props.company.phones.length ? props.company.phones.map((phone) => ({ ...phone })) : [emptyPhone()],
});

const submit = () => {
    form.put(route('account.contacts.update'), { preserveScroll: true });
};

const section = 'space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs';
</script>

<template>
    <Head :title="$t('Контактная информация')" />

    <AccountLayout>
        <h1 class="text-2xl font-bold text-gray-900">{{ $t('Контактная информация') }}</h1>

        <form class="space-y-6" @submit.prevent="submit">
            <section :class="section">
                <h2 class="text-lg font-semibold" :class="form.errors.email ? 'text-red-600' : 'text-gray-900'">{{ $t('Email') }}</h2>

                <div>
                    <InputLabel for="email" :value="$t('Контактный email компании')" :invalid="!!form.errors.email" />
                    <TextInput id="email" type="email" class="mt-1 block w-full md:w-1/2" v-model="form.email" :invalid="!!form.errors.email" />
                    <p class="mt-1 text-xs text-gray-500">
                        {{ $t('Виден посетителям. Email для входа (:email) не меняется.', { email: page.props.auth.user.email }) }}
                    </p>
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
            </section>

            <CompanyPhonesFields :form="form" :phone-code="phoneCode" />

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">{{ $t('Сохранить') }}</PrimaryButton>
                <span v-if="form.recentlySuccessful" class="text-sm text-green-700">{{ $t('Сохранено') }}</span>
            </div>
        </form>
    </AccountLayout>
</template>
