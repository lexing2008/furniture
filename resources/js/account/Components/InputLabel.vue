<script setup lang="ts">
withDefaults(
    defineProps<{
        value?: string;
        required?: boolean;
        invalid?: boolean;
    }>(),
    { invalid: false },
);
</script>

<template>
    <label class="block text-sm font-medium" :class="invalid ? 'text-red-600' : 'text-gray-700'">
        <span v-if="value">
            {{ value }}<span v-if="required" class="text-red-600" aria-hidden="true"> *</span>
        </span>
        <span v-else>
            <slot /><span v-if="required" class="text-red-600" aria-hidden="true"> *</span>
        </span>
    </label>
</template>
