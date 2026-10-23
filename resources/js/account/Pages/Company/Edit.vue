<script setup lang="ts">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import CompanyFields from '@/account/Components/Company/CompanyFields.vue';
import type { CategoryNode, CompanyFormFields, RegionGroup } from '@/account/Components/Company/companyForm';
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import type { PageProps } from '@/account/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

interface EditFormData extends CompanyFormFields {
    email: string;
    socials: Record<string, string>;
}

const props = defineProps<{
    company: EditFormData;
    // key => title of a social network
    socials: Record<string, string>;
    regions: RegionGroup[];
    categories: CategoryNode[];
    regionName: string;
}>();

const page = usePage<PageProps>();

const form = useForm<EditFormData>({
    ...props.company,
    // Every network has a field, even when it is empty
    socials: Object.fromEntries(Object.keys(props.socials).map((key) => [key, props.company.socials[key] ?? ''])),
});

const errors = computed(() => form.errors as Record<string, string | undefined>);

const verified = computed(() => !!page.props.auth.user.email_verified_at);
const linkSent = computed(() => page.props.flash?.status === 'verification-link-sent');

const submit = () => {
    form.put(route('account.update'), { preserveScroll: true });
};

const resend = () => {
    router.post(route('verification.send'), {}, { preserveScroll: true });
};

const section = 'space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs';
</script>

<template>
    <Head title="Кабинет компании" />

    <AccountLayout>
        <div v-if="!verified" class="space-y-2 rounded-md bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
            <p>
                Почта <b>{{ page.props.auth.user.email }}</b> ещё не подтверждена. Письмо со ссылкой отправлено при регистрации.
            </p>
            <p v-if="linkSent" class="text-green-700">Новое письмо отправлено.</p>
            <button v-else type="button" class="font-medium underline" @click="resend">Отправить письмо ещё раз</button>
        </div>

        <h1 class="text-2xl font-bold text-gray-900">{{ company.display_name }}</h1>

        <form class="space-y-6" @submit.prevent="submit">
            <CompanyFields :form="form" :regions="regions" :categories="categories" />

            <section :class="section">
                <h2 class="text-lg font-semibold text-gray-900">Контакты и соцсети</h2>

                <div>
                    <InputLabel for="email" value="Контактный email компании" />
                    <TextInput id="email" type="email" class="mt-1 block w-full md:w-1/2" v-model="form.email" />
                    <p class="mt-1 text-xs text-gray-500">Виден посетителям. Email для входа ({{ page.props.auth.user.email }}) не меняется.</p>
                    <InputError class="mt-2" :message="form.errors.email" />
                </div>

                <div class="grid gap-4 md:grid-cols-2">
                    <div v-for="(title, key) in socials" :key="key">
                        <InputLabel :for="`social_${key}`" :value="title" />
                        <TextInput :id="`social_${key}`" type="text" class="mt-1 block w-full" v-model="form.socials[key]" placeholder="https://" />
                        <InputError class="mt-2" :message="errors[`socials.${key}`]" />
                    </div>
                </div>
            </section>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Сохранить</PrimaryButton>
                <span v-if="form.recentlySuccessful" class="text-sm text-green-700">Сохранено</span>
            </div>
        </form>
    </AccountLayout>
</template>
