<script setup lang="ts">
withDefaults(
    defineProps<{
        processing?: boolean;
        success?: boolean;
        disabled?: boolean;
        label?: string;
        successMessage?: string;
        hint?: string;
    }>(),
    { processing: false, success: false, disabled: false },
);
</script>

<template>
    <div class="sticky bottom-4 z-20 flex flex-col gap-3 rounded-2xl border border-gray-200/80 bg-white/95 p-4 shadow-lg shadow-gray-300/30 backdrop-blur sm:flex-row sm:items-center sm:justify-between">
        <div class="min-w-0 text-sm" aria-live="polite">
            <span v-if="success" class="inline-flex items-center gap-2 font-medium text-green-700">
                <span class="flex h-5 w-5 items-center justify-center rounded-full bg-green-100">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-3 w-3">
                        <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                    </svg>
                </span>
                {{ successMessage ?? $t('Сохранено') }}
            </span>
            <span v-else-if="hint !== ''" class="text-gray-500">{{ hint ?? $t('Поля со звёздочкой обязательны.') }}</span>
        </div>

        <div class="flex flex-col-reverse gap-2 sm:flex-row sm:items-center">
            <slot name="actions" />
            <button
                type="submit"
                :disabled="processing || disabled"
                class="inline-flex w-full cursor-pointer items-center justify-center gap-2 rounded-full bg-green-first px-6 py-3 text-sm font-semibold text-white shadow-md shadow-green-first/20 transition hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-first/40 disabled:cursor-not-allowed disabled:opacity-50 sm:w-auto"
            >
                <svg v-if="processing" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z" />
                </svg>
                {{ label ?? $t('Сохранить') }}
            </button>
        </div>
    </div>
</template>
