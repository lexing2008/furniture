<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import UserForm from '@/admin/Components/Users/UserForm.vue';
import type { RoleOption, UserFormData } from '@/admin/Components/Users/UserForm.vue';
import { Head, useForm } from '@inertiajs/vue3';

defineProps<{
    roles: RoleOption[];
}>();

const form = useForm<UserFormData>({
    name: '',
    email: '',
    role: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('admin.users.store'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Добавить пользователя" />

    <AuthenticatedLayout>
        <template #header>
            Добавить пользователя
        </template>

        <Content>
            <p class="mb-6 text-sm text-gray-600">
                После сохранения на указанный email будет отправлено письмо для подтверждения адреса.
            </p>

            <UserForm
                :form="form"
                :roles="roles"
                :password-required="true"
                @submit="submit"
            />
        </Content>
    </AuthenticatedLayout>
</template>
