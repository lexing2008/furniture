<script setup lang="ts">
import type { RegionNode } from '@/account/Components/Company/companyForm';
import { isEnglish } from '@/common/i18n';
import { computed, inject } from 'vue';
import { adRegionTreeKey } from './adRegionTree';

const props = defineProps<{
    node: RegionNode;
}>();

const tree = inject(adRegionTreeKey)!;
const name = computed(() => (isEnglish() ? props.node.name_en : props.node.name));
const button = 'rounded border border-gray-300 px-1.5 py-0.5 text-[11px] text-gray-600 hover:bg-gray-50';
</script>

<template>
    <li>
        <div v-if="node.children.length > 0" class="flex items-center gap-2 rounded px-1 py-1 hover:bg-gray-50">
            <button
                type="button"
                class="flex min-w-0 flex-1 items-center gap-2 text-left text-sm font-medium text-gray-800"
                :aria-expanded="tree.isExpanded(node.id)"
                @click="tree.toggleExpanded(node.id)"
            >
                <span class="inline-block w-4 text-gray-400 transition-transform" :class="{ 'rotate-90': tree.isExpanded(node.id) }">▸</span>
                <span class="truncate">{{ name }}</span>
            </button>

            <button type="button" :class="button" @click.stop="tree.toggleAll(node)">
                {{ tree.isAllSelected(node) ? $t('Снять все') : $t('Выбрать все') }}
            </button>
        </div>

        <label v-else class="flex cursor-pointer items-center gap-2 rounded px-1 py-1 pl-7 text-sm text-gray-700 hover:bg-gray-50">
            <input
                type="checkbox"
                :checked="tree.isSelected(node.id)"
                class="rounded-sm border-gray-300 text-green-first shadow-xs focus:ring-green-first"
                @change="tree.toggleSelected(node.id)"
            />
            {{ name }}
        </label>

        <ul v-if="node.children.length > 0" v-show="tree.isExpanded(node.id)" class="ml-4 space-y-1 border-l border-gray-100 pl-2">
            <AdRegionTreeNode v-for="child in node.children" :key="child.id" :node="child" />
        </ul>
    </li>
</template>
