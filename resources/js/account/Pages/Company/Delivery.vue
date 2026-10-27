<script setup lang="ts">
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import InputError from '@/account/Components/InputError.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import WysiwygEditor from '@/common/Components/WysiwygEditor.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    company: {
        delivery: string;
    };
}>();

const form = useForm({
    delivery: props.company.delivery,
});

const errors = computed(() => form.errors as Record<string, string | undefined>);

const submit = () => {
    form.put(route('account.delivery.update'), { preserveScroll: true });
};

const section = 'space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs';
</script>

<template>
    <Head :title="$t('Доставка')" />

    <AccountLayout>
        <h1 class="text-2xl font-bold text-gray-900">{{ $t('Доставка') }}</h1>

        <form class="space-y-6" @submit.prevent="submit">
            <section :class="section">
                <h2 class="text-lg font-semibold" :class="errors.delivery ? 'text-red-600' : 'text-gray-900'">{{ $t('Условия доставки') }}</h2>
                <p class="text-xs text-gray-500">
                    {{ $t('Опишите способы, сроки и стоимость доставки. Текст будет виден посетителям.') }}
                </p>

                <WysiwygEditor
                    v-model="form.delivery"
                    profile="company"
                    height-key="account-wysiwyg"
                    :invalid="!!errors.delivery"
                    resizable
                />
                <InputError :message="errors.delivery" />
            </section>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">{{ $t('Сохранить') }}</PrimaryButton>
                <span v-if="form.recentlySuccessful" class="text-sm text-green-700">{{ $t('Сохранено') }}</span>
            </div>
        </form>
    </AccountLayout>
</template>
