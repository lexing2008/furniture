<script setup lang="ts">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import AccountFormActions from '@/account/Components/Forms/AccountFormActions.vue';
import AccountPageHeader from '@/account/Components/Forms/AccountPageHeader.vue';
import { formCard, formTitle } from '@/account/Components/Forms/formStyles';
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

const hint = 'mt-1 text-sm leading-6 text-gray-500';
</script>

<template>
    <Head :title="$t('Изменить пароль')" />

    <AccountLayout>
        <AccountPageHeader
            icon="password"
            :title="$t('Изменить пароль')"
            :description="$t('Обновите пароль для входа в кабинет.')"
        />

        <form class="space-y-6" @submit.prevent="submit">
            <section :class="formCard">
                <h2 :class="[formTitle, 'text-gray-900']">{{ $t('Пароль для входа') }}</h2>
                <p :class="hint">{{ $t('Используйте длинный пароль, который не применяется на других сайтах.') }}</p>

                <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_18rem]">
                    <div class="space-y-5">
                <div>
                    <InputLabel for="current_password" :value="$t('Текущий пароль')" required :invalid="!!form.errors.current_password" />
                    <TextInput
                        id="current_password"
                        :invalid="!!form.errors.current_password"
                        ref="currentPasswordInput"
                        v-model="form.current_password"
                        type="password"
                        class="mt-1 block w-full"
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
                        class="mt-1 block w-full"
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
                        class="mt-1 block w-full"
                        autocomplete="new-password"
                    />
                    <InputError class="mt-2" :message="form.errors.password_confirmation" />
                </div>
                    </div>

                    <aside class="h-fit rounded-2xl border border-green-100 bg-green-50/60 p-5">
                        <span class="flex h-10 w-10 items-center justify-center rounded-xl bg-white text-green-700 shadow-xs">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z" />
                            </svg>
                        </span>
                        <h3 class="mt-4 text-sm font-semibold text-green-950">{{ $t('Надёжный пароль') }}</h3>
                        <ul class="mt-3 space-y-2 text-sm leading-5 text-green-900/80">
                            <li>{{ $t('Не менее 8 символов.') }}</li>
                            <li>{{ $t('Не используйте тот же пароль на других сайтах.') }}</li>
                            <li>{{ $t('Смените пароль, если считаете, что он мог быть скомпрометирован.') }}</li>
                        </ul>
                    </aside>
                </div>
            </section>

            <AccountFormActions
                :processing="form.processing"
                :success="form.recentlySuccessful"
                :label="$t('Изменить пароль')"
                :success-message="$t('Пароль изменён')"
            />
        </form>
    </AccountLayout>
</template>
