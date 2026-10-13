<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import UserForm from '@/admin/Components/Users/UserForm.vue';
import type { RoleOption, UserFormData } from '@/admin/Components/Users/UserForm.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    user: {
        id: number;
        name: string;
        email: string;
        role: string;
        email_verified_at: string | null;
        is_current: boolean;
    };
    roles: RoleOption[];
}>();

const form = useForm<UserFormData>({
    name: props.user.name,
    email: props.user.email,
    role: props.user.role,
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.put(route('admin.users.update', props.user.id), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head :title="`Редактирование: ${user.name}`" />

    <AuthenticatedLayout>
        <template #header>
            Редактирование пользователя
        </template>

        <Content>
            <div
                v-if="!user.email_verified_at"
                class="mb-6 flex flex-wrap items-center justify-between gap-2 rounded-md bg-yellow-50 px-4 py-3 text-sm text-yellow-800"
            >
                <span>Email не подтверждён.</span>

                <Link
                    :href="route('admin.users.verification.send', user.id)"
                    method="post"
                    as="button"
                    preserve-scroll
                    class="font-medium underline hover:text-yellow-900"
                >
                    Отправить письмо повторно
                </Link>
            </div>

            <p class="mb-6 text-sm text-gray-600">
                При смене email подтверждение сбросится, и на новый адрес придёт письмо.
            </p>

            <UserForm
                :form="form"
                :roles="roles"
                :password-required="false"
                :role-locked="user.is_current"
                @submit="submit"
            />
        </Content>
    </AuthenticatedLayout>
</template>
