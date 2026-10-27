<script setup lang="ts">
import { translate } from '@/common/i18n';
import { computed, nextTick, onBeforeUnmount, ref, watch } from 'vue';

const props = defineProps<{
    adId: number;
    price: number;
    currency: string;
    decimals: number;
}>();

const emit = defineEmits<{
    saved: [price: number];
}>();

const editing = ref(false);
const saving = ref(false);
const hoverLocked = ref(false);
const error = ref<string | null>(null);
const value = ref('');
const input = ref<HTMLInputElement | null>(null);
const displayPrice = ref(props.price);
const toast = ref<{ type: 'success' | 'error'; message: string } | null>(null);
let toastTimer: number | null = null;

const formattedPrice = computed(() => `${displayPrice.value.toFixed(props.decimals)} ${props.currency}`);
const toastTitle = computed(() => (toast.value?.type === 'success' ? translate('Готово') : translate('Ошибка')));
const step = computed(() => String(1 / 10 ** props.decimals));

watch(
    () => props.price,
    (price) => {
        displayPrice.value = price;

        if (!editing.value) {
            value.value = price.toFixed(props.decimals);
        }
    },
);

const csrfToken = (): string => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const showToast = (type: 'success' | 'error', message: string) => {
    toast.value = { type, message };

    if (toastTimer !== null) {
        window.clearTimeout(toastTimer);
    }

    toastTimer = window.setTimeout(() => {
        toast.value = null;
        toastTimer = null;
    }, 3500);
};

onBeforeUnmount(() => {
    if (toastTimer !== null) {
        window.clearTimeout(toastTimer);
    }
});

const startEditing = async (force = false) => {
    if (editing.value || (hoverLocked.value && !force)) {
        return;
    }

    if (force) {
        hoverLocked.value = false;
    }

    value.value = displayPrice.value.toFixed(props.decimals);
    error.value = null;
    editing.value = true;

    await nextTick();
    input.value?.focus();
    input.value?.select();
};

const cancel = () => {
    if (saving.value) {
        return;
    }

    hoverLocked.value = true;
    editing.value = false;
    error.value = null;
};

const onLeave = () => {
    if (saving.value) {
        hoverLocked.value = false;

        return;
    }

    if (document.activeElement === input.value) {
        return;
    }

    hoverLocked.value = false;

    if (Math.abs(Number(value.value) - displayPrice.value) < 0.0000001) {
        editing.value = false;
        error.value = null;
    }
};

const save = async () => {
    if (!editing.value || saving.value) {
        return;
    }

    const raw = String(value.value).trim();
    const price = Number(raw);

    if (raw === '' || !Number.isFinite(price) || price < 0) {
        error.value = translate('Введите корректную цену.');

        return;
    }

    if (Math.abs(price - displayPrice.value) < 0.0000001) {
        hoverLocked.value = true;
        editing.value = false;

        return;
    }

    saving.value = true;
    error.value = null;

    try {
        const response = await fetch(route('account.ads.price.update', props.adId), {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ price: raw }),
        });
        const data = (await response.json().catch(() => ({}))) as {
            price?: number;
            message?: string;
            errors?: Record<string, string[]>;
        };

        if (!response.ok) {
            error.value = data.errors?.price?.[0] ?? data.message ?? translate('Не удалось сохранить цену.');
            showToast('error', error.value);

            return;
        }

        displayPrice.value = Number(data.price ?? price);
        hoverLocked.value = true;
        editing.value = false;
        emit('saved', displayPrice.value);
        showToast('success', translate('Цена сохранена.'));
    } catch {
        error.value = translate('Не удалось сохранить цену.');
        showToast('error', error.value);
    } finally {
        saving.value = false;
    }
};
</script>

<template>
    <div class="group min-w-28" @mouseenter="startEditing(false)" @mouseleave="onLeave">
        <button
            v-if="!editing"
            type="button"
            class="inline-flex items-center gap-1.5 rounded-full px-2 py-1 text-left text-sm font-medium text-gray-800 transition hover:bg-green-first/10 hover:text-green-first"
            :title="$t('Изменить цену')"
            @click="startEditing(true)"
        >
            {{ formattedPrice }}
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-3.5 w-3.5 text-green-first opacity-0 transition group-hover:opacity-100">
                <path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" />
            </svg>
        </button>

        <div v-else class="relative">
            <div class="flex items-center gap-1">
                <input
                    ref="input"
                    v-model="value"
                    type="number"
                    min="0"
                    :step="step"
                    class="w-28 rounded-full border px-2 py-1 text-sm shadow-xs focus:outline-none"
                    :class="error ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-green-first focus:ring-green-first'"
                    @blur="save"
                    @keydown.enter.prevent="save"
                    @keydown.esc.prevent="cancel"
                />
                <span class="text-xs font-medium text-gray-500">{{ currency }}</span>
                <svg v-if="saving" class="h-3.5 w-3.5 animate-spin text-green-first" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z" />
                </svg>
            </div>
            <p v-if="error" class="absolute left-0 top-full z-10 mt-1 w-48 rounded-md bg-red-50 px-2 py-1 text-[10px] leading-3 text-red-700 shadow-sm">{{ error }}</p>
        </div>

        <Teleport to="body">
            <Transition
                enter-active-class="transition duration-300 ease-out"
                enter-from-class="translate-y-4 scale-95 opacity-0"
                enter-to-class="translate-y-0 scale-100 opacity-100"
                leave-active-class="transition duration-200 ease-in"
                leave-from-class="translate-y-0 scale-100 opacity-100"
                leave-to-class="translate-y-2 scale-95 opacity-0"
            >
                <div
                    v-if="toast"
                    :key="toast.type + toast.message"
                    class="fixed bottom-4 left-4 right-4 z-[120] overflow-hidden rounded-2xl border bg-white/95 ring-1 ring-black/5 backdrop-blur sm:bottom-6 sm:left-auto sm:right-6 sm:w-96"
                    :class="toast.type === 'success'
                        ? 'border-green-first/20 shadow-[0_26px_65px_-22px_rgba(103,181,42,0.65),0_10px_30px_-15px_rgba(15,23,42,0.18)]'
                        : 'border-red-100 shadow-[0_26px_65px_-22px_rgba(239,68,68,0.5),0_10px_30px_-15px_rgba(15,23,42,0.18)]'"
                    :role="toast.type === 'error' ? 'alert' : 'status'"
                    :aria-live="toast.type === 'error' ? 'assertive' : 'polite'"
                >
                    <span
                        class="absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b"
                        :class="toast.type === 'success' ? 'from-green-first to-green-first/70' : 'from-red-400 to-rose-600'"
                    ></span>

                    <div class="flex items-start gap-3 p-4 pl-5">
                        <span
                            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br text-white shadow-lg"
                            :class="toast.type === 'success' ? 'from-green-first to-green-first/70 shadow-green-first/20' : 'from-red-400 to-rose-600 shadow-red-200/60'"
                        >
                            <svg v-if="toast.type === 'success'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.6" stroke="currentColor" class="h-5 w-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                            </svg>
                        </span>

                        <div class="min-w-0 flex-1 pt-0.5">
                            <p class="text-sm font-bold text-gray-900">{{ toastTitle }}</p>
                            <p class="mt-1 text-sm leading-5 text-gray-600">{{ toast.message }}</p>
                        </div>

                        <button
                            type="button"
                            class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                            :aria-label="$t('Закрыть')"
                            :title="$t('Закрыть')"
                            @click="toast = null"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>

                    <div class="h-1 bg-gray-100">
                        <div
                            class="toast-progress h-full origin-left"
                            :class="toast.type === 'success' ? 'bg-green-first' : 'bg-red-500'"
                        ></div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </div>
</template>

<style scoped>
.toast-progress {
    animation: toast-progress 3.5s linear forwards;
}

@keyframes toast-progress {
    from {
        transform: scaleX(1);
    }

    to {
        transform: scaleX(0);
    }
}
</style>
