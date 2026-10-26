<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface Region {
    id: number;
    name: string;
    name_en: string;
    subdomain: string;
    url: string;
    depth: number;
    lang: string | null;
    fallback_lang: string | null;
    timezone: string | null;
    is_capital: boolean;
    is_active: boolean;
}

defineProps<{
    regions: Region[];
}>();

const levels = ['Страна', 'Область', 'Город'];

const toggle = (region: Region) => {
    router.patch(route('admin.regions.toggle', region.id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Регионы" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <span>Регионы</span>

                <Link
                    :href="route('admin.regions.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700"
                >
                    Добавить
                </Link>
            </div>
        </template>

        <Content>
            <div class="mb-4 text-sm text-gray-600">Всего: {{ regions.length }}</div>

            <p v-if="regions.length === 0" class="text-gray-500">
                Регионы ещё не добавлены.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Название</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Уровень</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Поддомен</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Язык</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Часовой пояс</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Статус</th>
                            <th class="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="region in regions" :key="region.id" :class="{ 'text-gray-400': !region.is_active }">
                            <td class="px-4 py-3">
                                <span :style="{ paddingLeft: `${region.depth * 1.5}rem` }">
                                    <span v-if="region.depth > 0" class="text-gray-400">└ </span>
                                    <span :class="['mr-1',{'font-bold': region.is_capital}]">
                                        {{ region.name }}
                                    </span>
                                    <span class="text-gray-400">({{ region.name_en }})</span>
                                    <span v-if="region.is_capital" class="ml-1 font-bold text-red-300">столица</span>
                                </span>
                            </td>
                            <td class="px-4 py-3">{{ levels[region.depth] }}</td>
                            <td class="px-4 py-3 font-mono">
                                <a :href="region.url" target="_blank" class="text-indigo-600 hover:text-indigo-900">
                                    {{ region.subdomain }}
                                </a>
                            </td>
                            <td class="px-4 py-3 uppercase">{{ region.lang ?? '' }}<span v-if="region.fallback_lang" class="text-gray-400"> / {{ region.fallback_lang }}</span></td>
                            <td class="px-4 py-3 font-mono">{{ region.timezone ?? '—' }}</td>
                            <td class="px-4 py-3">
                                <button
                                    type="button"
                                    class="rounded-full px-2 py-0.5 text-xs"
                                    :class="region.is_active ? 'bg-green-100 text-green-800 hover:bg-green-200' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                    :title="region.is_active ? 'Деактивировать' : 'Активировать'"
                                    @click="toggle(region)"
                                >
                                    {{ region.is_active ? 'Активен' : 'Отключён' }}
                                </button>
                            </td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <Link
                                    :href="route('admin.regions.edit', region.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Изменить
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </Content>
    </AuthenticatedLayout>
</template>
