<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps<{
    show: boolean;
    type: 'success' | 'error';
    message: string;
}>();

const emit = defineEmits<{
    close: [];
}>();

const title = computed(() => (props.type === 'success' ? 'Готово' : 'Ошибка'));
</script>

<template>
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
                v-if="show"
                :key="type + message"
                class="fixed bottom-4 left-4 right-4 z-[120] overflow-hidden rounded-2xl border bg-white/95 ring-1 ring-black/5 backdrop-blur sm:bottom-6 sm:left-auto sm:right-6 sm:w-96"
                :class="type === 'success'
                    ? 'border-green-first/20 shadow-[0_26px_65px_-22px_rgba(103,181,42,0.65),0_10px_30px_-15px_rgba(15,23,42,0.18)]'
                    : 'border-red-100 shadow-[0_26px_65px_-22px_rgba(239,68,68,0.5),0_10px_30px_-15px_rgba(15,23,42,0.18)]'"
                :role="type === 'error' ? 'alert' : 'status'"
                :aria-live="type === 'error' ? 'assertive' : 'polite'"
            >
                <span
                    class="absolute inset-y-0 left-0 w-1.5 bg-gradient-to-b"
                    :class="type === 'success' ? 'from-green-first to-green-first/70' : 'from-red-400 to-rose-600'"
                ></span>

                <div class="flex items-start gap-3 p-4 pl-5">
                    <span
                        class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gradient-to-br text-white shadow-lg"
                        :class="type === 'success' ? 'from-green-first to-green-first/70 shadow-green-first/20' : 'from-red-400 to-rose-600 shadow-red-200/60'"
                    >
                        <svg v-if="type === 'success'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.6" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.6" stroke="currentColor" class="h-5 w-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                        </svg>
                    </span>

                    <div class="min-w-0 flex-1 pt-0.5">
                        <p class="text-sm font-bold text-gray-900">{{ $t(title) }}</p>
                        <p class="mt-1 text-sm leading-5 text-gray-600">{{ message }}</p>
                    </div>

                    <button
                        type="button"
                        class="flex h-7 w-7 shrink-0 cursor-pointer items-center justify-center rounded-full text-gray-400 transition hover:bg-gray-100 hover:text-gray-700"
                        :aria-label="$t('Закрыть')"
                        :title="$t('Закрыть')"
                        @click="emit('close')"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>

                <div class="h-1 bg-gray-100">
                    <div class="toast-progress h-full origin-left" :class="type === 'success' ? 'bg-green-first' : 'bg-red-500'"></div>
                </div>
            </div>
        </Transition>
    </Teleport>
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
