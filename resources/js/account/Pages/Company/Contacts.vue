<script setup lang="ts">
import CompanyPhonesFields from '@/account/Components/Company/CompanyPhonesFields.vue';
import { emptyPhone } from '@/account/Components/Company/companyForm';
import type { CompanyContactsFields } from '@/account/Components/Company/companyForm';
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import AccountFormActions from '@/account/Components/Forms/AccountFormActions.vue';
import AccountPageHeader from '@/account/Components/Forms/AccountPageHeader.vue';
import { formCard, formTitle } from '@/account/Components/Forms/formStyles';
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

</script>

<template>
    <Head :title="$t('Контактная информация')" />

    <AccountLayout>
        <AccountPageHeader
            icon="contacts"
            :title="$t('Контактная информация')"
            :description="$t('Способы связи с компанией для посетителей.')"
        />

        <form class="space-y-6" @submit.prevent="submit">
            <section :class="formCard">
                <h2 :class="[formTitle, form.errors.email ? 'text-red-600' : 'text-gray-900']">{{ $t('Email') }}</h2>

                <div>
                    <InputLabel for="email" :value="$t('Контактный email компании')" :invalid="!!form.errors.email" />
                    <TextInput id="email" type="email" class="mt-1 block w-full md:w-1/2" v-model="form.email" :invalid="!!form.errors.email" />
                    <p class="mt-1 text-sm leading-6 text-gray-500">
                        {{ $t('Виден посетителям. Email для входа (:email) не меняется.', { email: page.props.auth.user.email }) }}
                    </p>
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>
            </section>

            <CompanyPhonesFields :form="form" :phone-code="phoneCode" />

            <AccountFormActions :processing="form.processing" :success="form.recentlySuccessful" />
        </form>
    </AccountLayout>
</template>
