<script setup lang="ts">
import GuestLayout from '@/account/Layouts/GuestLayout.vue';
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    email: string;
    token: string;
}>();

const form = useForm({
    token: props.token,
    email: props.email,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('account.password.store'), {
        onFinish: () => {
            form.reset('password', 'password_confirmation');
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="$t('Reset Password')" />

        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $t('Новый пароль') }}</h1>
        <p class="mt-2 text-sm leading-6 text-gray-600">{{ $t('Придумайте новый пароль для входа в кабинет.') }}</p>

        <form class="mt-6" @submit.prevent="submit">
            <div>
                <InputLabel for="email" :value="$t('Email')" required :invalid="!!form.errors.email" />

                <TextInput
                    id="email"
                    :invalid="!!form.errors.email"
                    type="email"
                    class="mt-1 block w-full"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel for="password" :value="$t('Password')" required :invalid="!!form.errors.password" />

                <TextInput
                    id="password"
                    :invalid="!!form.errors.password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-4">
                <InputLabel
                    for="password_confirmation"
                    :value="$t('Confirm Password')"
                    required
                    :invalid="!!form.errors.password_confirmation"
                />

                <TextInput
                    id="password_confirmation"
                    :invalid="!!form.errors.password_confirmation"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ $t('Сохранить новый пароль') }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
