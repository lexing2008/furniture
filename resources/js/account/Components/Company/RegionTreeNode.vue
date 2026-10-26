<script setup lang="ts">
import { computed, inject } from 'vue';
import type { RegionNode } from './companyForm';
import { regionTreeKey } from './regionTree';
import { isEnglish } from '@/common/i18n';

const props = defineProps<{
    node: RegionNode;
}>();

const tree = inject(regionTreeKey)!;
const name = computed(() => (isEnglish() ? props.node.name_en : props.node.name));
</script>

<template>
    <li>
        <!-- A country or an area only opens and closes -->
        <button
            v-if="node.children.length > 0"
            type="button"
            class="flex w-full items-center gap-2 rounded px-1 py-1 text-left text-sm font-medium text-gray-800 hover:bg-gray-50"
            :aria-expanded="tree.isExpanded(node.id)"
            @click="tree.toggleExpanded(node.id)"
        >
            <span class="inline-block w-4 text-gray-400 transition-transform" :class="{ 'rotate-90': tree.isExpanded(node.id) }">▸</span>
            {{ name }}
        </button>

        <label v-else class="flex cursor-pointer items-center gap-2 rounded px-1 py-1 pl-7 text-sm text-gray-700 hover:bg-gray-50">
            <input
                type="radio"
                :name="tree.name"
                :value="node.id"
                :checked="tree.isSelected(node.id)"
                class="h-4 w-4 border-gray-300 text-green-first shadow-xs focus:ring-green-first"
                @change="tree.select(node.id)"
            />
            {{ name }}
        </label>

        <ul v-if="node.children.length > 0" v-show="tree.isExpanded(node.id)" class="ml-4 space-y-1 border-l border-gray-100 pl-2">
            <RegionTreeNode v-for="child in node.children" :key="child.id" :node="child" />
        </ul>
    </li>
</template>
