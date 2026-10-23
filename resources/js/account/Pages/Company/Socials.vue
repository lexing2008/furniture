<script setup lang="ts">
import type { CompanySocialsFields } from '@/account/Components/Company/companyForm';
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    company: CompanySocialsFields;
    // key => title of a social network
    socials: Record<string, string>;
}>();

const form = useForm<CompanySocialsFields>({
    // Every network has a field, even when it is empty
    socials: Object.fromEntries(Object.keys(props.socials).map((key) => [key, props.company.socials[key] ?? ''])),
});

const errors = computed(() => form.errors as Record<string, string | undefined>);

const submit = () => {
    form.put(route('account.socials.update'), { preserveScroll: true });
};

const section = 'space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs';
</script>

<template>
    <Head title="Соцсети" />

    <AccountLayout>
        <h1 class="text-2xl font-bold text-gray-900">Соцсети</h1>

        <form class="space-y-6" @submit.prevent="submit">
            <section :class="section">
                <h2 class="text-lg font-semibold text-gray-900">Ссылки на соцсети</h2>
                <p class="text-xs text-gray-500">Укажите полные адреса страниц компании. Пустые поля не сохраняются.</p>

                <div class="grid gap-4 md:grid-cols-2">
                    <div v-for="(title, key) in socials" :key="key">
                        <InputLabel :for="`social_${key}`" :value="title" />
                        <TextInput
                            :id="`social_${key}`"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.socials[key]"
                            placeholder="https://"
                        />
                        <InputError class="mt-2" :message="errors[`socials.${key}`]" />
                    </div>
                </div>
            </section>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">Сохранить</PrimaryButton>
                <span v-if="form.recentlySuccessful" class="text-sm text-green-700">Сохранено</span>
            </div>
        </form>
    </AccountLayout>
</template>
