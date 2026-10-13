<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    code: '',
    name: '',
    name_en: '',
});

const submit = () => {
    form.post(route('admin.languages.store'));
};
</script>

<template>
    <Head title="Добавить язык" />

    <AuthenticatedLayout>
        <template #header>
            Добавить язык
        </template>

        <Content>
            <form @submit.prevent="submit" class="max-w-xl space-y-6">
                <div>
                    <InputLabel for="code" value="Код (ISO 639-1)" />

                    <TextInput
                        id="code"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.code"
                        maxlength="5"
                        placeholder="en"
                        required
                        autofocus
                    />

                    <InputError class="mt-2" :message="form.errors.code" />
                </div>

                <div>
                    <InputLabel for="name" value="Название (на самом языке)" />

                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        placeholder="Русский"
                        required
                    />

                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="name_en" value="Название на английском" />

                    <TextInput
                        id="name_en"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name_en"
                        placeholder="Russian"
                        required
                    />

                    <InputError class="mt-2" :message="form.errors.name_en" />
                </div>

                <div class="flex items-center gap-4">
                    <PrimaryButton :disabled="form.processing">Сохранить</PrimaryButton>

                    <Link
                        :href="route('admin.languages.index')"
                        class="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        Отмена
                    </Link>
                </div>
            </form>
        </Content>
    </AuthenticatedLayout>
</template>
