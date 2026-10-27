<script setup lang="ts">
import { computed, provide, ref } from 'vue';
import RegionTreeNode from './RegionTreeNode.vue';
import type { RegionNode } from './companyForm';
import { regionTreeKey } from './regionTree';
import { isEnglish } from '@/common/i18n';

const props = withDefaults(
    defineProps<{
        nodes: RegionNode[];
        // Name of the radio group
        name?: string;
        invalid?: boolean;
    }>(),
    { name: 'region_id', invalid: false },
);

const selected = defineModel<number | null>({ required: true });

const expanded = ref(new Set<number>());

// Branches (countries and areas) and the ids below them
// Name of the chosen city, shown above the tree
const selectedName = computed(() => {
    if (selected.value === null) {
        return null;
    }

    let name: string | null = null;

    const walk = (nodes: RegionNode[]) => {
        for (const node of nodes) {
            if (node.id === selected.value) {
                name = isEnglish() ? node.name_en : node.name;

                return;
            }

            walk(node.children);
        }
    };

    walk(props.nodes);

    return name;
});

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

// The chosen city is opened at once: the form is edited with a value already selected
const openChosen = () => {
    if (selected.value === null) {
        return;
    }

    const walk = (nodes: RegionNode[]): boolean => {
        let found = false;

        for (const node of nodes) {
            const inside = walk(node.children);

            if (inside) {
                expanded.value.add(node.id);
            }

            found = found || inside || node.id === selected.value;
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

const select = (id: number) => {
    selected.value = id;
};

const expandAll = () => {
    branchIds.value.forEach((id) => expanded.value.add(id));
};

const collapseAll = () => {
    expanded.value.clear();
};

provide(regionTreeKey, {
    name: props.name,
    isExpanded: (id: number) => expanded.value.has(id),
    isSelected: (id: number) => selected.value === id,
    toggleExpanded,
    select,
});

const button = 'cursor-pointer rounded border border-gray-300 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50';
</script>

<template>
    <div>
        <div class="mb-3 flex flex-wrap items-center gap-2">
            <button type="button" :class="button" @click="expandAll">{{ $t('Развернуть все') }}</button>
            <button type="button" :class="button" @click="collapseAll">{{ $t('Свернуть все') }}</button>
            <span v-if="selectedName" class="text-sm text-gray-500">{{ $t('Выбран: :name', { name: selectedName }) }}</span>
        </div>

        <p v-if="nodes.length === 0" class="text-sm text-gray-500">{{ $t('Городов пока нет.') }}</p>

        <ul v-else class="space-y-1 rounded-md border p-3" :class="invalid ? 'border-red-500' : 'border-gray-200'">
            <RegionTreeNode v-for="node in nodes" :key="node.id" :node="node" />
        </ul>
    </div>
</template>
