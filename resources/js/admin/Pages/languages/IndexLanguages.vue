<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Language {
    id: number;
    code: string;
    name: string;
    name_en: string;
    created_at: string;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{
    languages: {
        data: Language[];
        links: PaginationLink[];
        total: number;
    };
}>();

const formatDate = (value: string) => new Date(value).toLocaleDateString('ru-RU');
</script>

<template>
    <Head title="Языки" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <span>Языки</span>

                <Link
                    :href="route('admin.languages.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700"
                >
                    Добавить
                </Link>
            </div>
        </template>

        <Content>
            <p v-if="languages.data.length === 0" class="text-gray-500">
                Языки ещё не добавлены.
            </p>

            <table v-else class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Код</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Название</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Название (EN)</th>
                        <th class="px-4 py-3 text-left font-medium text-gray-500">Добавлен</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="language in languages.data" :key="language.id">
                        <td class="px-4 py-3 font-mono uppercase">{{ language.code }}</td>
                        <td class="px-4 py-3">{{ language.name }}</td>
                        <td class="px-4 py-3">{{ language.name_en }}</td>
                        <td class="px-4 py-3 text-gray-500">{{ formatDate(language.created_at) }}</td>
                    </tr>
                </tbody>
            </table>

            <nav
                v-if="languages.links.length > 3"
                class="mt-6 flex flex-wrap gap-1"
            >
                <template v-for="link in languages.links" :key="link.label">
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
