<script setup lang="ts">
import AccountToast from '@/account/Components/AccountToast.vue';
import Sidebar from '@/account/Components/Sidebar/Sidebar.vue';
import { translate } from '@/common/i18n';
import type { PageProps } from '@/account/types';
import { router, usePage } from '@inertiajs/vue3';
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';

const page = usePage<PageProps>();

const verified = computed(() => !!page.props.auth.user.email_verified_at);
const linkSent = computed(() => page.props.flash?.status === 'verification-link-sent');

const resend = () => {
    router.post(route('verification.send'), {}, { preserveScroll: true });
};

const sidebarCollapsed = ref(false);

type Toast = {
    type: 'success' | 'error';
    message: string;
};

const toast = ref<Toast | null>(null);
let toastTimer: number | null = null;

const showToast = (type: Toast['type'], message: string) => {
    toast.value = { type, message };

    if (toastTimer !== null) {
        window.clearTimeout(toastTimer);
    }

    toastTimer = window.setTimeout(() => {
        toast.value = null;
        toastTimer = null;
    }, 3500);
};

const closeToast = () => {
    toast.value = null;

    if (toastTimer !== null) {
        window.clearTimeout(toastTimer);
        toastTimer = null;
    }
};

onBeforeUnmount(() => {
    if (toastTimer !== null) {
        window.clearTimeout(toastTimer);
    }
});

watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            showToast('success', flash.success);
        } else if (flash?.error) {
            showToast('error', flash.error);
        }
    },
    { deep: true, immediate: true },
);

watch(
    () => page.props.errors,
    (errors) => {
        if (errors && Object.keys(errors).length > 0 && !page.props.flash?.success && !page.props.flash?.error) {
            showToast('error', translate('Проверьте правильность заполнения полей.'));
        }
    },
    { deep: true },
);

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

                    <slot />
                </div>
            </main>
        </div>

        <AccountToast :show="toast !== null" :type="toast?.type ?? 'success'" :message="toast?.message ?? ''" @close="closeToast" />
    </div>
</template>
