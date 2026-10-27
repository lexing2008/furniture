<script setup lang="ts">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const currentPasswordInput = ref<HTMLInputElement | null>(null);
const passwordInput = ref<HTMLInputElement | null>(null);

const form = useForm({
    current_password: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('account.password.update'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
        },
        onError: () => {
            if (form.errors.password) {
                form.reset('password', 'password_confirmation');
                passwordInput.value?.focus();
            }
            if (form.errors.current_password) {
                form.reset('current_password');
                currentPasswordInput.value?.focus();
            }
        },
    });
};

const section = 'space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs';
const hint = 'mt-1 text-xs text-gray-500';
</script>

<template>
    <Head :title="$t('Изменить пароль')" />

    <AccountLayout>
        <h1 class="text-2xl font-bold text-gray-900">{{ $t('Изменить пароль') }}</h1>

        <form class="space-y-6" @submit.prevent="submit">
            <section :class="section">
                <h2 class="text-lg font-semibold text-gray-900">{{ $t('Пароль для входа') }}</h2>
                <p :class="hint">{{ $t('Используйте длинный пароль, который не применяется на других сайтах.') }}</p>

                <div>
                    <InputLabel for="current_password" :value="$t('Текущий пароль')" required :invalid="!!form.errors.current_password" />
                    <TextInput
                        id="current_password"
                        :invalid="!!form.errors.current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        class="mt-1 block w-full md:w-1/2"
                        autocomplete="current-password"
                    />
                    <InputError class="mt-2" :message="form.errors.current_password" />
                </div>

                <div>
                    <InputLabel for="password" :value="$t('Новый пароль')" required :invalid="!!form.errors.password" />
                    <TextInput
                        id="password"
                        :invalid="!!form.errors.password"
                        ref="passwordInput"
                        v-model="form.password"
                        type="password"
                        class="mt-1 block w-full md:w-1/2"
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password" />
                </div>

                <div>
                    <InputLabel for="password_confirmation" :value="$t('Повторите новый пароль')" required :invalid="!!form.errors.password_confirmation" />
                    <TextInput
                        id="password_confirmation"
                        :invalid="!!form.errors.password_confirmation"
                        v-model="form.password_confirmation"
                        type="password"
                        class="mt-1 block w-full md:w-1/2"
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>
            </section>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">{{ $t('Изменить пароль') }}</PrimaryButton>
                <span v-if="form.recentlySuccessful" class="text-sm text-green-700">{{ $t('Пароль изменён') }}</span>
            </div>
        </form>
    </AccountLayout>
</template>
