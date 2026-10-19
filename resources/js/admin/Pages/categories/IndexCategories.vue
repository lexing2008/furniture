<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface Category {
    id: number;
    name: string;
    depth: number;
    is_active: boolean;
    filters_count: number;
    translations: { code: string; slug: string }[];
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

const props = defineProps<{
    categories: {
        data: Category[];
        links: PaginationLink[];
        total: number;
    };
    showAll: boolean;
    languagesCount: number;
}>();

const toggleShowAll = () => {
    router.get(
        route('admin.categories.index'),
        props.showAll ? {} : { all: 1 },
        { preserveScroll: true },
    );
};
</script>

<template>
    <Head title="Категории мебели" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <span>Категории мебели</span>

                <Link
                    :href="route('admin.categories.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700"
                >
                    Добавить
                </Link>
            </div>
        </template>

        <Content>
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2 text-sm text-gray-600">
                <span>Всего: {{ categories.total }}</span>

                <label class="flex cursor-pointer items-center gap-2">
                    <input
                        type="checkbox"
                        :checked="showAll"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                        @change="toggleShowAll"
                    />
                    Показать все без постраничной навигации
                </label>
            </div>

            <p v-if="categories.data.length === 0" class="text-gray-500">
                Категории ещё не добавлены.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Название</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Slug</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Языки</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Фильтры</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Статус</th>
                            <th class="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="category in categories.data" :key="category.id">
                            <td class="px-4 py-3">
                                <span :style="{ paddingLeft: `${category.depth * 1.5}rem` }">
                                    <span v-if="category.depth > 0" class="text-gray-400">└ </span>
                                    {{ category.name }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-mono text-gray-600">
                                <div v-for="t in category.translations" :key="t.code">
                                    <span class="uppercase text-gray-400">{{ t.code }}:</span> {{ t.slug }}
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    :class="category.translations.length < languagesCount ? 'text-yellow-700' : 'text-gray-600'"
                                    :title="category.translations.length < languagesCount ? 'Заполнены не все языки' : ''"
                                >
                                    {{ category.translations.length }} / {{ languagesCount }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <Link
                                    :href="route('admin.categories.filters.index', category.id)"
                                    :class="category.filters_count > 0 ? 'text-indigo-600 hover:text-indigo-900' : 'text-gray-400'"
                                >
                                    {{ category.filters_count }}
                                </Link>
                            </td>
                            <td class="px-4 py-3">
                                <span
                                    class="rounded-full px-2 py-0.5 text-xs"
                                    :class="category.is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-600'"
                                >
                                    {{ category.is_active ? 'Активна' : 'Отключена' }}
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <Link
                                    :href="route('admin.categories.filters.index', category.id)"
                                    class="mr-3 text-indigo-600 hover:text-indigo-900"
                                >
                                    Фильтры
                                </Link>
                                <Link
                                    :href="route('admin.categories.edit', category.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Изменить
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav
                v-if="!showAll && categories.links.length > 3"
                class="mt-6 flex flex-wrap gap-1"
            >
                <template v-for="link in categories.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        class="rounded px-3 py-1 text-sm"
                        :class="link.active ? 'bg-gray-800 text-white' : 'text-gray-700 hover:bg-gray-100'"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="rounded px-3 py-1 text-sm text-gray-400"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </Content>
    </AuthenticatedLayout>
</template>
