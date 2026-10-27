<script setup lang="ts">
import GuestLayout from '@/account/Layouts/GuestLayout.vue';
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    status?: string;
}>();

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('account.password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head :title="$t('Forgot Password')" />

        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $t('Восстановление пароля') }}</h1>
        <p class="mt-2 text-sm leading-6 text-gray-600">
            {{ $t('Забыли пароль? Не страшно. Укажите email, и мы отправим ссылку для сброса пароля.') }}
        </p>

        <div
            v-if="status"
            class="mb-4 text-sm font-medium text-green-600"
        >
            {{ status }}
        </div>

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

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ $t('Отправить ссылку для сброса пароля') }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
