<script setup lang="ts">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import CompanyFields from '@/account/Components/Company/CompanyFields.vue';
import { emptyPhone } from '@/account/Components/Company/companyForm';
import type { CategoryNode, CompanyFormFields, RegionNode } from '@/account/Components/Company/companyForm';
import type { PageProps } from '@/account/types';
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';

interface RegisterFormData extends CompanyFormFields {
    name: string;
    email: string;
    password: string;
    password_confirmation: string;
    terms: boolean;
}

const props = defineProps<{
    regions: RegionNode[];
    defaultRegionId: number | null;
    phoneCode: string | null;
    categories: CategoryNode[];
    terms: { rules: string; privacy: string };
}>();

const page = usePage<PageProps>();

const form = useForm<RegisterFormData>({
    region_id: props.defaultRegionId,
    legal_name: '',
    tax_id: '',
    display_name: '',
    description: '',
    is_online_store: false,
    website: '',
    phones: [emptyPhone()],
    categories: [],
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    terms: false,
});

const submit = () => {
    form.post(route('account.register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};

const section = 'space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs';
</script>

<template>
    <Head :title="$t('Регистрация компании')" />

    <div class="min-h-screen bg-gray-50 py-8">
        <div class="mx-auto max-w-4xl space-y-6 px-4">
            <div>
                <p class="text-sm text-gray-500">{{ page.props.app_name }}</p>
                <h1 class="text-2xl font-bold text-gray-900">{{ $t('Регистрация компании') }}</h1>
            </div>

            <form class="space-y-6" @submit.prevent="submit">
                <CompanyFields :form="form" :regions="regions" :phone-code="phoneCode" :categories="categories" />

                <section :class="section">
                    <h2 class="text-lg font-semibold text-gray-900">{{ $t('Ваш аккаунт') }}</h2>
                    <p class="text-xs text-gray-500">{{ $t('Этим аккаунтом вы будете входить в кабинет. На почту придёт письмо со ссылкой подтверждения.') }}</p>

                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <InputLabel for="name" :value="$t('Имя')" required :invalid="!!form.errors.name" />
                            <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" autocomplete="name" :invalid="!!form.errors.name" />
                            <InputError class="mt-2" :message="form.errors.name" />
                        </div>

                        <div>
                            <InputLabel for="email" :value="$t('Email')" required :invalid="!!form.errors.email" />
                            <TextInput id="email" type="email" class="mt-1 block w-full" v-model="form.email" autocomplete="username" :invalid="!!form.errors.email" />
                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <InputLabel for="password" :value="$t('Пароль')" required :invalid="!!form.errors.password" />
                            <TextInput id="password" type="password" class="mt-1 block w-full" v-model="form.password" autocomplete="new-password" :invalid="!!form.errors.password" />
                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <div>
                            <InputLabel for="password_confirmation" :value="$t('Повторите пароль')" required :invalid="!!form.errors.password_confirmation" />
                            <TextInput
                                id="password_confirmation"
                                :invalid="!!form.errors.password_confirmation"
                                type="password"
                                class="mt-1 block w-full"
                                v-model="form.password_confirmation"
                                autocomplete="new-password"
                            />
                            <InputError class="mt-2" :message="form.errors.password_confirmation" />
                        </div>
                    </div>
                </section>

                <div class="space-y-4">
                    <label class="flex items-start gap-2" :class="{ 'text-red-600': !!form.errors.terms }">
                        <input
                            type="checkbox"
                            v-model="form.terms"
                            class="mt-0.5 rounded-sm text-indigo-600 shadow-xs focus:ring-indigo-500"
                            :class="form.errors.terms ? 'border-red-500' : 'border-gray-300'"
                        />
                        <span class="text-sm" :class="form.errors.terms ? 'text-red-600' : 'text-gray-700'">
                            {{ $t('Я согласен с') }} <span class="text-red-600">*</span>
                            <a :href="terms.rules" target="_blank" class="text-indigo-600 underline">{{ $t('Правилами использования веб-сайта') }}</a>
                            {{ $t('и') }}
                            <a :href="terms.privacy" target="_blank" class="text-indigo-600 underline">{{ $t('политикой обработки персональных данных') }}</a>
                        </span>
                    </label>
                    <InputError :message="form.errors.terms" />

                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <PrimaryButton :disabled="form.processing">{{ $t('Зарегистрировать компанию') }}</PrimaryButton>
                        <Link :href="route('account.login')" class="text-sm text-gray-600 underline hover:text-gray-900">{{ $t('Уже есть кабинет? Войти') }}</Link>
                    </div>
                </div>
            </form>
        </div>
    </div>
</template>
