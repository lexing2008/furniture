<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import { Head, Link } from '@inertiajs/vue3';

interface Country {
    id: number;
    name: string;
    name_en: string;
    subdomain: string;
    timezone: string | null;
    phone_code: string | null;
    currency: string | null;
    currency_decimals: number | null;
}

defineProps<{
    countries: Country[];
}>();
</script>

<template>
    <Head title="Все страны" />

    <AuthenticatedLayout>
        <template #header>
            Все страны
        </template>

        <Content>
            <div class="mb-4 text-sm text-gray-600">Всего: {{ countries.length }}</div>

            <p v-if="countries.length === 0" class="text-gray-500">
                Страны ещё не добавлены.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Название</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Поддомен</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Часовой пояс</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Код телефона</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Валюта</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Знаков после запятой</th>
                            <th class="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="country in countries" :key="country.id">
                            <td class="px-4 py-3">
                                {{ country.name }}
                                <span class="text-gray-400">({{ country.name_en }})</span>
                            </td>
                            <td class="px-4 py-3 font-mono">{{ country.subdomain }}</td>
                            <td class="px-4 py-3 font-mono">{{ country.timezone ?? '—' }}</td>
                            <td class="px-4 py-3 font-mono">
                                {{ country.phone_code ? `+${country.phone_code}` : '—' }}
                            </td>
                            <td class="px-4 py-3 font-mono">{{ country.currency ?? '—' }}</td>
                            <td class="px-4 py-3">{{ country.currency_decimals ?? '—' }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <Link
                                    :href="route('admin.countries.edit', country.id)"
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
