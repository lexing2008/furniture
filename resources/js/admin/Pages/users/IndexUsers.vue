<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import { Head, Link, router } from '@inertiajs/vue3';

interface User {
    id: number;
    name: string;
    email: string;
    role: string;
    email_verified_at: string | null;
    created_at: string;
    is_current: boolean;
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

defineProps<{
    users: {
        data: User[];
        links: PaginationLink[];
        total: number;
    };
}>();

const formatDate = (value: string) => new Date(value).toLocaleDateString('ru-RU');

const destroy = (user: User) => {
    if (!confirm(`Удалить пользователя ${user.email}?`)) {
        return;
    }

    router.delete(route('admin.users.destroy', user.id), { preserveScroll: true });
};

const sendVerification = (user: User) => {
    router.post(route('admin.users.verification.send', user.id), {}, { preserveScroll: true });
};
</script>

<template>
    <Head title="Пользователи" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <span>Пользователи</span>

                <Link
                    :href="route('admin.users.create')"
                    class="inline-flex items-center rounded-md border border-transparent bg-gray-800 px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out hover:bg-gray-700"
                >
                    Добавить
                </Link>
            </div>
        </template>

        <Content>
            <p v-if="users.data.length === 0" class="text-gray-500">
                Пользователи ещё не добавлены.
            </p>

            <div v-else class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Имя</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Email</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Роль</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Email подтверждён</th>
                            <th class="px-4 py-3 text-left font-medium text-gray-500">Добавлен</th>
                            <th class="px-4 py-3" />
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="user in users.data" :key="user.id">
                            <td class="px-4 py-3">
                                {{ user.name }}
                                <span v-if="user.is_current" class="ml-1 text-xs text-gray-400">(вы)</span>
                            </td>
                            <td class="px-4 py-3">{{ user.email }}</td>
                            <td class="px-4 py-3">{{ user.role }}</td>
                            <td class="px-4 py-3">
                                <span v-if="user.email_verified_at" class="text-green-700">
                                    {{ formatDate(user.email_verified_at) }}
                                </span>
                                <span v-else class="text-yellow-700">
                                    Нет ·
                                    <button
                                        type="button"
                                        class="underline hover:text-yellow-900"
                                        @click="sendVerification(user)"
                                    >
                                        отправить письмо
                                    </button>
                                </span>
                            </td>
                            <td class="px-4 py-3 text-gray-500">{{ formatDate(user.created_at) }}</td>
                            <td class="whitespace-nowrap px-4 py-3 text-right">
                                <Link
                                    :href="route('admin.users.edit', user.id)"
                                    class="text-indigo-600 hover:text-indigo-900"
                                >
                                    Изменить
                                </Link>

                                <button
                                    v-if="!user.is_current"
                                    type="button"
                                    class="ml-4 text-red-600 hover:text-red-800"
                                    @click="destroy(user)"
                                >
                                    Удалить
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <nav
                v-if="users.links.length > 3"
                class="mt-6 flex flex-wrap gap-1"
            >
                <template v-for="link in users.links" :key="link.label">
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
