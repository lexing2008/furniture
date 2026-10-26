<script setup lang="ts">
import { inject } from 'vue';
import { categoryTreeKey } from './categoryTree';
import type { CategoryNode } from './companyForm';

defineProps<{
    node: CategoryNode;
}>();

const tree = inject(categoryTreeKey)!;
</script>

<template>
    <li>
        <!-- A category with subcategories only opens and closes -->
        <button
            v-if="node.children.length > 0"
            type="button"
            class="flex w-full cursor-pointer items-center gap-2 rounded px-1 py-1 text-left text-sm font-medium text-gray-800 hover:bg-gray-50"
            :aria-expanded="tree.isExpanded(node.id)"
            @click="tree.toggleExpanded(node.id)"
        >
            <span class="inline-block w-4 text-gray-400 transition-transform" :class="{ 'rotate-90': tree.isExpanded(node.id) }">▸</span>
            {{ node.name }}
        </button>

        <label v-else class="flex cursor-pointer items-center gap-2 rounded px-1 py-1 pl-7 text-sm text-gray-700 hover:bg-gray-50">
            <input
                type="checkbox"
                :checked="tree.isSelected(node.id)"
                class="rounded-sm border-gray-300 text-green-first shadow-xs focus:ring-green-first"
                @change="tree.toggleSelected(node.id)"
            />
            {{ node.name }}
        </label>

        <ul v-if="node.children.length > 0" v-show="tree.isExpanded(node.id)" class="ml-4 space-y-1 border-l border-gray-100 pl-2">
            <CategoryTreeNode v-for="child in node.children" :key="child.id" :node="child" />
        </ul>
    </li>
</template>
