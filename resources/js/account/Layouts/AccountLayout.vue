<script setup lang="ts">
import Sidebar from '@/account/Components/Sidebar/Sidebar.vue';
import type { PageProps } from '@/account/types';
import { router, usePage } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';

const page = usePage<PageProps>();

const verified = computed(() => !!page.props.auth.user.email_verified_at);
const linkSent = computed(() => page.props.flash?.status === 'verification-link-sent');

const resend = () => {
    router.post(route('verification.send'), {}, { preserveScroll: true });
};

const sidebarCollapsed = ref(false);

onMounted(() => {
    sidebarCollapsed.value = localStorage.getItem('account-sidebar-collapsed') === '1';
});

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('account-sidebar-collapsed', sidebarCollapsed.value ? '1' : '0');
};
</script>

<template>
    <div class="flex min-h-screen bg-gray-50">
        <!-- Боковая панель, как в /admin -->
        <Sidebar :collapsed="sidebarCollapsed" @toggle="toggleSidebar" />

        <button
            v-if="sidebarCollapsed"
            type="button"
            class="fixed left-0 top-1/2 z-40 hidden -translate-y-1/2 cursor-pointer items-center justify-center rounded-r-2xl bg-green-first px-1.5 py-4 text-white shadow-lg shadow-green-950/20 transition hover:bg-green-second md:flex"
            :title="$t('Показать меню')"
            :aria-label="$t('Показать меню')"
            @click="toggleSidebar"
        >
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" />
            </svg>
        </button>

        <div class="min-w-0 flex-1 min-h-screen bg-gray-50">
            <main>
                <div class="mx-auto max-w-[1600px] space-y-6 px-4 pb-24 pt-16 md:px-8 md:pt-10">
                    <!-- Почта ещё не подтверждена: напоминаем на всех страницах кабинета -->
                    <div v-if="!verified" class="space-y-2 rounded-xl border border-yellow-200 bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                        <p>
                            {{ $t('Почта') }} <b>{{ page.props.auth.user.email }}</b>
                            {{ $t('ещё не подтверждена. Письмо со ссылкой отправлено при регистрации.') }}
                        </p>
                        <p v-if="linkSent" class="text-green-700">{{ $t('Новое письмо отправлено.') }}</p>
                        <button v-else type="button" class="font-medium underline" @click="resend">
                            {{ $t('Отправить письмо ещё раз') }}
                        </button>
                    </div>

                    <div v-if="page.props.flash?.success" class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-700">
                        {{ page.props.flash.success }}
                    </div>
                    <div v-if="page.props.flash?.error" class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">
                        {{ page.props.flash.error }}
                    </div>

                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
