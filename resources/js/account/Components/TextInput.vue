<script setup lang="ts">
import { onMounted, ref } from 'vue';

withDefaults(
    defineProps<{
        invalid?: boolean;
    }>(),
    { invalid: false },
);

const model = defineModel<string>({ required: true });

const input = ref<HTMLInputElement | null>(null);

onMounted(() => {
    if (input.value?.hasAttribute('autofocus')) {
        input.value?.focus();
    }
});

defineExpose({ focus: () => input.value?.focus() });
</script>

<template>
    <input
        class="rounded-full border placeholder-gray-300 shadow-xs focus:placeholder-white"
        :class="invalid ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-green-first focus:ring-green-first'"
        v-model="model"
        ref="input"
    />
</template>
