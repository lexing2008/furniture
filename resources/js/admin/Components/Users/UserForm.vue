<script setup lang="ts">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import { Link } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';

export interface UserFormData {
    name: string;
    email: string;
    role: string;
    password: string;
    password_confirmation: string;
}

export interface RoleOption {
    value: string;
    label: string;
}

const props = defineProps<{
    form: InertiaForm<UserFormData>;
    roles: RoleOption[];
    // On edit the password is optional: empty means "keep current"
    passwordRequired: boolean;
    roleLocked?: boolean;
}>();

defineEmits<{
    submit: [];
}>();
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="max-w-xl space-y-6">
        <div>
            <InputLabel for="name" value="Имя" />

            <TextInput
                id="name"
                type="text"
                class="mt-1 block w-full"
                v-model="props.form.name"
                required
                autofocus
                autocomplete="off"
            />

            <InputError class="mt-2" :message="props.form.errors.name" />
        </div>

        <div>
            <InputLabel for="email" value="Email" />

            <TextInput
                id="email"
                type="email"
                class="mt-1 block w-full"
                v-model="props.form.email"
                required
                autocomplete="off"
            />

            <InputError class="mt-2" :message="props.form.errors.email" />
        </div>

        <div>
            <InputLabel for="role" value="Роль" />

            <select
                id="role"
                v-model="props.form.role"
                class="mt-1 block w-full rounded-md border-gray-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500 disabled:bg-gray-100"
                :disabled="roleLocked"
                required
            >
                <option value="" disabled>Выберите роль</option>
                <option v-for="role in roles" :key="role.value" :value="role.value">
                    {{ role.label }}
                </option>
            </select>

            <p v-if="roleLocked" class="mt-1 text-xs text-gray-500">
                Собственную роль изменить нельзя.
            </p>

            <InputError class="mt-2" :message="props.form.errors.role" />
        </div>

        <div>
            <InputLabel for="password" :value="passwordRequired ? 'Пароль' : 'Новый пароль'" />

            <TextInput
                id="password"
                type="password"
                class="mt-1 block w-full"
                v-model="props.form.password"
                :required="passwordRequired"
                autocomplete="new-password"
            />

            <p v-if="!passwordRequired" class="mt-1 text-xs text-gray-500">
                Оставьте пустым, чтобы не менять пароль.
            </p>

            <InputError class="mt-2" :message="props.form.errors.password" />
        </div>

        <div>
            <InputLabel for="password_confirmation" value="Повторите пароль" />

            <TextInput
                id="password_confirmation"
                type="password"
                class="mt-1 block w-full"
                v-model="props.form.password_confirmation"
                :required="passwordRequired"
                autocomplete="new-password"
            />

            <InputError class="mt-2" :message="props.form.errors.password_confirmation" />
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="props.form.processing">Сохранить</PrimaryButton>

            <Link
                :href="route('admin.users.index')"
                class="text-sm text-gray-600 underline hover:text-gray-900"
            >
                Отмена
            </Link>
        </div>
    </form>
</template>
