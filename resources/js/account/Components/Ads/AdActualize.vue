<script setup lang="ts">
import { translate } from '@/common/i18n';
import { ref } from 'vue';

const props = defineProps<{
    adId: number;
    publishedAt: string | null;
}>();

const date = ref(props.publishedAt);
const saving = ref(false);
const error = ref<string | null>(null);

const csrfToken = (): string => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const actualize = async () => {
    if (saving.value) {
        return;
    }

    saving.value = true;
    error.value = null;

    try {
        const response = await fetch(route('account.ads.actualize', props.adId), {
            method: 'PATCH',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
        });
        const data = (await response.json().catch(() => ({}))) as {
            published_at?: string;
            message?: string;
        };

        if (!response.ok) {
            error.value = data.message ?? translate('Не удалось подтвердить актуальность.');

            return;
        }

        date.value = data.published_at ?? date.value;
    } catch {
        error.value = translate('Не удалось подтвердить актуальность.');
    } finally {
        saving.value = false;
    }
};
</script>

<template>
    <div class="flex min-w-24 flex-col items-start gap-1">
        <span class="whitespace-nowrap text-xs text-gray-600">{{ date || '—' }}</span>
        <button
            type="button"
            class="inline-flex w-24 cursor-pointer items-center justify-center gap-1 whitespace-normal rounded-full border border-green-200 bg-green-50 px-2 py-1 text-center text-[10px] font-semibold leading-3 text-green-800 transition hover:bg-green-100 disabled:opacity-50"
            :title="$t('Поднять')"
            :aria-label="$t('Поднять')"
            :disabled="saving"
            @click="actualize"
        >
            <svg v-if="saving" class="h-3 w-3 animate-spin" viewBox="0 0 24 24" fill="none">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z" />
            </svg>
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="h-3 w-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 19.5V4.5m0 0L5.25 11.25M12 4.5l6.75 6.75" />
            </svg>
            {{ $t('Поднять') }}
        </button>
        <p v-if="error" class="text-[10px] leading-3 text-red-600">{{ error }}</p>
    </div>
</template>
