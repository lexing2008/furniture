<script setup lang="ts">
import AccountLayout from '@/account/Layouts/AccountLayout.vue';
import InputError from '@/account/Components/InputError.vue';
import AccountFormActions from '@/account/Components/Forms/AccountFormActions.vue';
import AccountPageHeader from '@/account/Components/Forms/AccountPageHeader.vue';
import { formCard, formTitle } from '@/account/Components/Forms/formStyles';
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

</script>

<template>
    <Head :title="$t('Оплата')" />

    <AccountLayout>
        <AccountPageHeader
            icon="payment"
            :title="$t('Оплата')"
            :description="$t('Опишите способы оплаты для посетителей.')"
        />

        <form class="space-y-6" @submit.prevent="submit">
            <section :class="formCard">
                <h2 :class="[formTitle, errors.payment ? 'text-red-600' : 'text-gray-900']">{{ $t('Способы оплаты') }}</h2>
                <p class="text-sm leading-6 text-gray-500">
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

            <AccountFormActions :processing="form.processing" :success="form.recentlySuccessful" />
        </form>
    </AccountLayout>
</template>
