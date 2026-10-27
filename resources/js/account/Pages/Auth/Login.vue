<script setup lang="ts">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps<{
    canResetPassword?: boolean;
    status?: string;
}>();

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const showPassword = ref(false);

const submit = () => {
    form.post(route('account.login'), {
        onFinish: () => {
            form.reset('password');
        },
    });
};

const benefits = [
    'Размещайте товары и услуги',
    'Управляйте графиком, доставкой и оплатой',
    'Следите за статусом публикаций',
];
</script>

<template>
    <Head :title="$t('Log in')" />

    <section class="relative overflow-hidden bg-gray-50 py-8 sm:py-12 lg:py-16">
        <div class="pointer-events-none absolute -left-40 top-10 h-80 w-80 rounded-full bg-green-first/10 blur-3xl"></div>
        <div class="pointer-events-none absolute -right-32 bottom-0 h-96 w-96 rounded-full bg-green-second/10 blur-3xl"></div>

        <div class="relative mx-auto grid w-full max-w-6xl gap-8 px-4 lg:grid-cols-[1.05fr_0.95fr] lg:items-stretch">
            <!-- Brand panel -->
            <aside class="relative hidden overflow-hidden rounded-[2rem] bg-gradient-to-br from-green-first via-green-first to-green-second p-10 text-white shadow-xl lg:flex lg:flex-col">
                <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full border border-white/15"></div>
                <div class="pointer-events-none absolute -bottom-32 -left-24 h-80 w-80 rounded-full border border-white/10"></div>
                <div class="pointer-events-none absolute right-16 top-1/3 h-24 w-24 rounded-full bg-white/10 blur-2xl"></div>

                <div class="relative flex items-center gap-3">
                    <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-7 w-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 21h18M5 21V8l7-5 7 5v13M9 21v-6h6v6" />
                        </svg>
                    </div>
                    <span class="text-lg font-semibold tracking-wide">{{ $page.props.app_name }}</span>
                </div>

                <div class="relative mt-16 max-w-md">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-white/60">
                        {{ $t('Кабинет компании') }}
                    </p>
                    <h1 class="mt-4 text-4xl font-bold leading-tight tracking-tight">
                        {{ $t('Вход в кабинет компании') }}
                    </h1>
                    <p class="mt-4 text-base leading-7 text-white/75">
                        {{ $t('Управляйте компанией, товарами и услугами в одном месте.') }}
                    </p>
                </div>

                <ul class="relative mt-10 space-y-4">
                    <li v-for="benefit in benefits" :key="benefit" class="flex items-center gap-3 text-sm text-white/85">
                        <span class="flex h-6 w-6 shrink-0 items-center justify-center rounded-full bg-white/15">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-3.5 w-3.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                        </span>
                        {{ $t(benefit) }}
                    </li>
                </ul>

                <div class="relative mt-auto pt-12 text-xs text-white/50">
                    {{ $t('Доступ для владельцев компаний') }}
                </div>
            </aside>

            <!-- Login card -->
            <div class="mx-auto w-full max-w-md lg:max-w-none">
                <div class="mb-6 lg:hidden">
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-gray-400">
                        {{ $t('Кабинет компании') }}
                    </p>
                    <h1 class="mt-2 text-2xl font-bold text-gray-900">
                        {{ $t('Вход в кабинет компании') }}
                    </h1>
                </div>

                <div class="rounded-[2rem] border border-gray-100 bg-white p-6 shadow-xl shadow-gray-200/50 sm:p-10">
                    <div class="mb-8">
                        <p class="text-sm font-medium text-green-700">{{ $t('Добро пожаловать') }}</p>
                        <h2 class="mt-2 text-2xl font-bold tracking-tight text-gray-900">
                            {{ $t('Вход в кабинет') }}
                        </h2>
                        <p class="mt-2 text-sm leading-6 text-gray-500">
                            {{ $t('Войдите, чтобы управлять компанией.') }}
                        </p>
                    </div>

                    <div v-if="status" class="mb-5 rounded-2xl border border-green-200 bg-green-50 px-4 py-3 text-sm font-medium text-green-700">
                        {{ status }}
                    </div>

                    <form class="space-y-5" @submit.prevent="submit">
                        <div>
                            <InputLabel for="email" :value="$t('Email')" required :invalid="!!form.errors.email" />

                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                                    </svg>
                                </span>

                                <TextInput
                                    id="email"
                                    :invalid="!!form.errors.email"
                                    type="email"
                                    class="block w-full rounded-xl! pl-11 pr-4"
                                    v-model="form.email"
                                    required
                                    autofocus
                                    autocomplete="username"
                                />
                            </div>

                            <InputError class="mt-2" :message="form.errors.email" />
                        </div>

                        <div>
                            <div class="flex items-center justify-between gap-3">
                                <InputLabel for="password" :value="$t('Password')" required :invalid="!!form.errors.password" />

                                <Link
                                    v-if="canResetPassword"
                                    :href="route('account.password.request')"
                                    class="text-xs font-medium text-green-700 transition hover:text-green-900 hover:underline"
                                >
                                    {{ $t('Forgot your password?') }}
                                </Link>
                            </div>

                            <div class="relative mt-1">
                                <span class="pointer-events-none absolute inset-y-0 left-4 flex items-center text-gray-400">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                                    </svg>
                                </span>

                                <TextInput
                                    id="password"
                                    :invalid="!!form.errors.password"
                                    :type="showPassword ? 'text' : 'password'"
                                    class="block w-full rounded-xl! pl-11 pr-12"
                                    v-model="form.password"
                                    required
                                    autocomplete="current-password"
                                />

                                <button
                                    type="button"
                                    class="absolute inset-y-0 right-2 flex w-9 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-50 hover:text-green-700 focus:outline-none focus:ring-2 focus:ring-green-first/30"
                                    :aria-label="showPassword ? $t('Скрыть пароль') : $t('Показать пароль')"
                                    @click="showPassword = !showPassword"
                                >
                                    <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
                                    </svg>
                                    <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                    </svg>
                                </button>
                            </div>

                            <InputError class="mt-2" :message="form.errors.password" />
                        </div>

                        <label class="flex cursor-pointer items-center gap-3 text-sm text-gray-600">
                            <input
                                v-model="form.remember"
                                type="checkbox"
                                class="h-4 w-4 rounded border-gray-300 text-green-first shadow-xs focus:ring-green-first"
                            />
                            {{ $t('Remember me') }}
                        </label>

                        <PrimaryButton
                            class="w-full justify-center py-3"
                            :class="{ 'opacity-25': form.processing }"
                            :disabled="form.processing"
                        >
                            {{ $t('Log in') }}
                        </PrimaryButton>
                    </form>

                    <div class="mt-7 rounded-2xl border border-green-100 bg-green-50/70 px-4 py-4 text-center text-sm text-gray-600">
                        {{ $t('Нет кабинета?') }}
                        <Link
                            :href="route('account.register')"
                            class="font-semibold text-green-800 underline-offset-2 hover:underline"
                        >
                            {{ $t('Зарегистрировать компанию') }}
                        </Link>
                    </div>
                </div>

                <p class="mt-5 text-center text-xs leading-5 text-gray-400">
                    {{ $t('Защищённый вход для владельцев компаний') }}
                </p>
            </div>
        </div>
    </section>
</template>
