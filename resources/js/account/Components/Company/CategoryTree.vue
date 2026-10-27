<script setup lang="ts">
import { computed, provide, ref } from 'vue';
import CategoryTreeNode from './CategoryTreeNode.vue';
import type { CategoryNode } from './companyForm';
import { categoryTreeKey } from './categoryTree';

const props = defineProps<{
    nodes: CategoryNode[];
    invalid?: boolean;
}>();

const selected = defineModel<number[]>({ required: true });

const expanded = ref(new Set<number>());

// Branches (categories with subcategories) and the ids below them
const branchIds = computed(() => {
    const ids: number[] = [];

    const walk = (nodes: CategoryNode[]) => {
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

// A branch with a chosen category is opened at once: nothing chosen stays hidden when the form is edited
const openChosen = () => {
    const walk = (nodes: CategoryNode[]): boolean => {
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

const expandAll = () => {
    branchIds.value.forEach((id) => expanded.value.add(id));
};

const collapseAll = () => {
    expanded.value.clear();
};

provide(categoryTreeKey, {
    isExpanded: (id: number) => expanded.value.has(id),
    isSelected: (id: number) => selected.value.includes(id),
    toggleExpanded,
    toggleSelected,
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

        <p v-if="nodes.length === 0" class="text-sm text-gray-500">{{ $t('Категорий пока нет.') }}</p>

        <ul v-else class="max-h-96 space-y-1 overflow-y-auto rounded-xl border p-3" :class="invalid ? 'border-red-500' : 'border-gray-200'">
            <CategoryTreeNode v-for="node in nodes" :key="node.id" :node="node" />
        </ul>
    </div>
</template>
