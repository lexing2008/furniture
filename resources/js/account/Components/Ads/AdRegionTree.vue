<script setup lang="ts">
import type { RegionNode } from '@/account/Components/Company/companyForm';
import { computed, provide, ref } from 'vue';
import AdRegionTreeNode from './AdRegionTreeNode.vue';
import { adRegionTreeKey } from './adRegionTree';

const props = defineProps<{
    nodes: RegionNode[];
}>();

const selected = defineModel<number[]>({ required: true });

const expanded = ref(new Set<number>());

const leafIds = (node: RegionNode): number[] => {
    if (node.selectable) {
        return [node.id];
    }

    return node.children.flatMap(leafIds);
};

const branchIds = computed(() => {
    const ids: number[] = [];

    const walk = (nodes: RegionNode[]) => {
        for (const node of nodes) {
            if (node.children.length > 0) {
                ids.push(node.id);
                walk(node.children);
            }
        }
    };

    walk(props.nodes);

    return ids;
});

const openChosen = () => {
    const walk = (nodes: RegionNode[]): boolean => {
        let found = false;

        for (const node of nodes) {
            const inside = walk(node.children);

            if (inside) {
                expanded.value.add(node.id);
            }

            found = found || inside || selected.value.includes(node.id);
        }

        return found;
    };

    walk(props.nodes);
};

openChosen();

const toggleExpanded = (id: number) => {
    if (expanded.value.has(id)) {
        expanded.value.delete(id);
    } else {
        expanded.value.add(id);
    }
};

const toggleSelected = (id: number) => {
    selected.value = selected.value.includes(id)
        ? selected.value.filter((value) => value !== id)
        : [...selected.value, id];
};

const isAllSelected = (node: RegionNode): boolean => {
    const ids = leafIds(node);

    return ids.length > 0 && ids.every((id) => selected.value.includes(id));
};

const toggleAll = (node: RegionNode) => {
    const ids = leafIds(node);

    if (isAllSelected(node)) {
        selected.value = selected.value.filter((id) => !ids.includes(id));
    } else {
        selected.value = [...new Set([...selected.value, ...ids])];
    }
};

const expandAll = () => {
    branchIds.value.forEach((id) => expanded.value.add(id));
};

const collapseAll = () => {
    expanded.value.clear();
};

provide(adRegionTreeKey, {
    isExpanded: (id: number) => expanded.value.has(id),
    isSelected: (id: number) => selected.value.includes(id),
    isAllSelected,
    toggleExpanded,
    toggleSelected,
    toggleAll,
});

const button = 'cursor-pointer rounded border border-gray-300 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50';
</script>

<template>
    <div>
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <button type="button" :class="button" @click="expandAll">{{ $t('Развернуть все') }}</button>
            <button type="button" :class="button" @click="collapseAll">{{ $t('Свернуть все') }}</button>
            <span class="text-sm text-gray-500">{{ $t('Выбрано: :count', { count: selected.length }) }}</span>
        </div>

        <p v-if="nodes.length === 0" class="text-sm text-gray-500">{{ $t('Городов пока нет.') }}</p>

        <ul v-else class="space-y-1 rounded-md border border-gray-200 p-3">
            <AdRegionTreeNode v-for="node in nodes" :key="node.id" :node="node" />
        </ul>
    </div>
</template>
