<script setup lang="ts">
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import InputError from '@/account/Components/InputError.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import WysiwygEditor from '@/common/Components/WysiwygEditor.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps<{
    company: {
        payment: string;
    };
}>();

const form = useForm({
    payment: props.company.payment,
});

const errors = computed(() => form.errors as Record<string, string | undefined>);

const submit = () => {
    form.put(route('account.payment.update'), { preserveScroll: true });
};

const section = 'space-y-4 rounded-lg border border-gray-200 bg-white p-5 shadow-xs';
</script>

<template>
    <Head :title="$t('Оплата')" />

    <AccountLayout>
        <h1 class="text-2xl font-bold text-gray-900">{{ $t('Оплата') }}</h1>

        <form class="space-y-6" @submit.prevent="submit">
            <section :class="section">
                <h2 class="text-lg font-semibold" :class="errors.payment ? 'text-red-600' : 'text-gray-900'">{{ $t('Способы оплаты') }}</h2>
                <p class="text-xs text-gray-500">
                    {{ $t('Опишите способы оплаты, например наличными, картой или переводом. Текст будет виден посетителям.') }}
                </p>

                <WysiwygEditor
                    v-model="form.payment"
                    profile="company"
                    height-key="account-wysiwyg"
                    :invalid="!!errors.payment"
                    resizable
                />
                <InputError :message="errors.payment" />
            </section>

            <div class="flex items-center gap-4">
                <PrimaryButton :disabled="form.processing">{{ $t('Сохранить') }}</PrimaryButton>
                <span v-if="form.recentlySuccessful" class="text-sm text-green-700">{{ $t('Сохранено') }}</span>
            </div>
        </form>
    </AccountLayout>
</template>
