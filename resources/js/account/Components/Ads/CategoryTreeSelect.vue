<script setup lang="ts">
import type { CategoryNode } from '@/account/Components/Company/companyForm';
import { computed, provide, ref } from 'vue';
import CategoryTreeNode from './CategoryTreeNode.vue';
import { categoryTreeSelectKey } from './categoryTreeSelect';

const props = withDefaults(
    defineProps<{
        nodes: CategoryNode[];
        name?: string;
        invalid?: boolean;
    }>(),
    { name: 'category_id', invalid: false },
);

const model = defineModel<number | null>({ required: true });
const emit = defineEmits<{
    change: [id: number | null];
}>();

const query = ref('');
const expanded = ref(new Set<number>());

const normalizedQuery = computed(() => query.value.trim().toLocaleLowerCase());

const matches = (node: CategoryNode): boolean =>
    node.name.toLocaleLowerCase().includes(normalizedQuery.value);

const filterNodes = (nodes: CategoryNode[]): CategoryNode[] => {
    if (normalizedQuery.value === '') {
        return nodes;
    }

    const result: CategoryNode[] = [];

    for (const node of nodes) {
        if (node.selectable) {
            if (matches(node)) {
                result.push(node);
            }

            continue;
        }

        // A matching branch shows all of its children, a matching child shows the branch as context
        const children = filterNodes(node.children);

        if (children.length > 0) {
            result.push({ ...node, children });
        } else if (matches(node)) {
            result.push(node);
        }
    }

    return result;
};

const filteredNodes = computed(() => filterNodes(props.nodes));

const selectedPath = computed(() => {
    if (model.value === null) {
        return null;
    }

    let path: string | null = null;

    const walk = (nodes: CategoryNode[], parents: string[]) => {
        for (const node of nodes) {
            const next = [...parents, node.name];

            if (node.id === model.value) {
                path = next.join(' / ');

                return;
            }

            walk(node.children, next);
        }
    };

    walk(props.nodes, []);

    return path;
});

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

const openChosen = () => {
    if (model.value === null) {
        return;
    }

    const walk = (nodes: CategoryNode[]): boolean => {
        let found = false;

        for (const node of nodes) {
            const inside = walk(node.children);

            if (inside) {
                expanded.value.add(node.id);
            }

            found = found || inside || node.id === model.value;
        }

        return found;
    };

    walk(props.nodes);
};

openChosen();

const isExpanded = (id: number): boolean =>
    normalizedQuery.value !== '' || expanded.value.has(id);

const toggleExpanded = (id: number) => {
    if (expanded.value.has(id)) {
        expanded.value.delete(id);
    } else {
        expanded.value.add(id);
    }
};

const select = (id: number) => {
    model.value = id;
    emit('change', id);
};

const expandAll = () => {
    branchIds.value.forEach((id) => expanded.value.add(id));
};

const collapseAll = () => {
    expanded.value.clear();
};

provide(categoryTreeSelectKey, {
    name: props.name,
    isExpanded,
    isSelected: (id: number) => model.value === id,
    toggleExpanded,
    select,
});

const button = 'cursor-pointer rounded border border-gray-300 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50';
</script>

<template>
    <div class="space-y-3">

        <div class="flex flex-wrap items-center gap-2 pt-2">
            <button type="button" :class="button" @click="expandAll">{{ $t('Развернуть все') }}</button>
            <button type="button" :class="button" @click="collapseAll">{{ $t('Свернуть все') }}</button>
            <span v-if="selectedPath" class="text-sm text-gray-500">{{ $t('Выбран: :name', { name: selectedPath }) }}</span>
        </div>
        <input
            v-model="query"
            type="search"
            class="block w-full rounded-full border px-4 text-sm shadow-xs placeholder-gray-300" :class="invalid ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-green-first focus:ring-green-first'"
            :placeholder="$t('Поиск категории')"
        />

        <p v-if="filteredNodes.length === 0" class="text-sm text-gray-500">{{ $t('Ничего не найдено') }}</p>

        <ul v-else class="max-h-96 space-y-1 overflow-y-auto rounded-xl border p-3" :class="invalid ? 'border-red-500' : 'border-gray-200'">
            <CategoryTreeNode v-for="node in filteredNodes" :key="node.id" :node="node" />
        </ul>
    </div>
</template>
