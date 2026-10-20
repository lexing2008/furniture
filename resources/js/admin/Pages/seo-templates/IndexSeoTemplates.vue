<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import { Head, Link } from '@inertiajs/vue3';

interface SeoTemplate {
    id: number;
    name: string;
    languages: string[];
    categories_count: number;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{
    templates: {
        data: SeoTemplate[];
        links: PaginationLink[];
        total: number;
    };
    languagesCount: number;
}>();
</script>

<template>
    <Head title="SEO-шаблоны" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <span>SEO-шаблоны</span>

                <Link
                    :href="route('admin.seo-templates.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700"
                >
                    Добавить
                </Link>
            </div>
        </template>

        <Content>
            <div class="mb-4 text-sm text-gray-600">Всего: {{ templates.total }}</div>

            <p v-if="templates.data.length === 0" class="text-gray-500">
                SEO-шаблоны ещё не добавлены.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Название</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Языки</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Используется в категориях</th>
                            <th class="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="template in templates.data" :key="template.id">
                            <td class="px-4 py-3">{{ template.name }}</td>
                            <td class="px-4 py-3">
                                <span
                                    :class="template.languages.length < languagesCount ? 'text-yellow-700' : 'text-gray-600'"
                                    :title="template.languages.length < languagesCount ? 'Заполнены не все языки' : ''"
                                >
                                    <span class="uppercase">{{ template.languages.join(', ') }}</span>
                                    ({{ template.languages.length }} / {{ languagesCount }})
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-600">{{ template.categories_count }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <Link
                                    :href="route('admin.seo-templates.edit', template.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Изменить
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav v-if="templates.links.length > 3" class="mt-6 flex flex-wrap gap-1">
                <template v-for="link in templates.links" :key="link.label">
                    <Link
                        v-if="link.url"
                        :href="link.url"
                        preserve-scroll
                        class="rounded-sm px-3 py-1 text-sm"
                        :class="link.active ? 'bg-gray-800 text-white' : 'text-gray-700 hover:bg-gray-100'"
                        v-html="link.label"
                    />
                    <span
                        v-else
                        class="rounded-sm px-3 py-1 text-sm text-gray-400"
                        v-html="link.label"
                    />
                </template>
            </nav>
        </Content>
    </AuthenticatedLayout>
</template>
