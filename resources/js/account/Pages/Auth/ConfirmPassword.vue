<script setup lang="ts">
import GuestLayout from '@/account/Layouts/GuestLayout.vue';
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';

const form = useForm({
    password: '',
});

const submit = () => {
    form.post(route('password.confirm'), {
        onFinish: () => {
            form.reset();
        },
    });
};
</script>

<template>
    <GuestLayout>
        <Head :title="$t('Confirm Password')" />

        <h1 class="text-2xl font-bold tracking-tight text-gray-900">{{ $t('Подтверждение пароля') }}</h1>
        <p class="mt-2 text-sm leading-6 text-gray-600">
            {{ $t('Это защищённый раздел. Подтвердите пароль, чтобы продолжить.') }}
        </p>

        <form class="mt-6" @submit.prevent="submit">
            <div>
                <InputLabel for="password" :value="$t('Password')" required :invalid="!!form.errors.password" />
                <TextInput
                    id="password"
                    :invalid="!!form.errors.password"
                    type="password"
                    class="mt-1 block w-full"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                    autofocus
                />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="mt-6">
                <PrimaryButton
                    class="w-full justify-center py-3"
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    {{ $t('Подтвердить') }}
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
