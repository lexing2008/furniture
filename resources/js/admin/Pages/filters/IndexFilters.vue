<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import type { CategoryInfo } from '@/admin/Components/Filters/filterForm';
import { Head, Link, router } from '@inertiajs/vue3';

interface Filter {
    id: number;
    name: string;
    unit: string | null;
    type: string;
    type_label: string;
    digits_after_point: number | null;
    variants_count: number;
    is_active: boolean;
    // Codes of the languages the filter is translated to
    translated: string[];
}

interface Language {
    id: number;
    code: string;
    name: string;
}

defineProps<{
    category: CategoryInfo;
    filters: Filter[];
    languages: Language[];
    hasBaseLanguage: boolean;
}>();

const toggle = (filter: Filter) => {
    router.patch(route('admin.filters.toggle', filter.id), {}, { preserveScroll: true });
};

const move = (filter: Filter, direction: 'up' | 'down') => {
    router.patch(route('admin.filters.move', filter.id), { direction }, { preserveScroll: true });
};

const remove = (filter: Filter) => {
    if (confirm(`Удалить фильтр «${filter.name}»?`)) {
        router.delete(route('admin.filters.destroy', filter.id), { preserveScroll: true });
    }
};

const smallButton = 'rounded-sm border border-gray-300 px-2 py-0.5 text-xs text-gray-600 hover:bg-gray-50 disabled:opacity-30';
</script>

<template>
    <Head :title="`Фильтры: ${category.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <span>Фильтры: {{ category.name }}</span>

                <Link
                    :href="route('admin.categories.filters.create', category.id)"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700"
                >
                    Добавить
                </Link>
            </div>
        </template>

        <Content>
            <div class="mb-4 flex items-center justify-between text-sm text-gray-600">
                <Link :href="route('admin.categories.index')" class="underline hover:text-gray-900">← Все категории</Link>
                <span>Всего: {{ filters.length }}</span>
            </div>

            <div v-if="!hasBaseLanguage" class="mb-4 rounded-md bg-yellow-50 px-4 py-3 text-sm text-yellow-800">
                Фильтры заполняются на русском: сначала
                <Link :href="route('admin.languages.create')" class="font-medium underline">добавьте язык «ru»</Link>.
            </div>

            <p v-if="filters.length === 0" class="text-gray-500">
                У категории пока нет фильтров.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Порядок</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Название</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Тип</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Варианты</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Переводы</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Статус</th>
                            <th class="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="(filter, index) in filters" :key="filter.id" :class="{ 'text-gray-400': !filter.is_active }">
                            <td class="whitespace-nowrap px-4 py-3">
                                <button type="button" :class="smallButton" :disabled="index === 0" title="Выше" @click="move(filter, 'up')">↑</button>
                                <button
                                    type="button"
                                    :class="smallButton"
                                    :disabled="index === filters.length - 1"
                                    title="Ниже"
                                    @click="move(filter, 'down')"
                                >↓</button>
                            </td>
                            <td class="px-4 py-3">
                                {{ filter.name }}
                                <span v-if="filter.unit" class="text-gray-400">, {{ filter.unit }}</span>
                            </td>
                            <td class="px-4 py-3">
                                {{ filter.type_label }}
                                <span v-if="filter.digits_after_point" class="text-gray-400">({{ filter.digits_after_point }} зн.)</span>
                            </td>
                            <td class="px-4 py-3">{{ filter.variants_count || '—' }}</td>
                            <td class="px-4 py-3">
                                <span v-if="languages.length === 0" class="text-gray-400">—</span>
                                <Link
                                    v-for="language in languages"
                                    :key="language.code"
                                    :href="route('admin.filters.translations.edit', [filter.id, language.code])"
                                    class="mr-2 uppercase hover:underline"
                                    :class="filter.translated.includes(language.code) ? 'text-green-700' : 'text-yellow-700'"
                                    :title="filter.translated.includes(language.code) ? `Перевод ${language.name}` : `Нет перевода: ${language.name}`"
                                >
                                    {{ language.code }}
                                </Link>
                            </td>
                            <td class="px-4 py-3">
                                <button
                                    type="button"
                                    class="rounded-full px-2 py-0.5 text-xs"
                                    :class="filter.is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                    :title="filter.is_active ? 'Деактивировать' : 'Активировать'"
                                    @click="toggle(filter)"
                                >
                                    {{ filter.is_active ? 'Активен' : 'Отключён' }}
                                </button>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <Link :href="route('admin.filters.edit', filter.id)" class="text-indigo-600 hover:text-indigo-900">
                                    Изменить
                                </Link>
                                <button type="button" class="ml-3 text-red-600 hover:text-red-800" @click="remove(filter)">
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Content>
    </AuthenticatedLayout>
</template>
