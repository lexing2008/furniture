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

</script>

<template>
    <Head :title="$t('Доставка')" />

    <AccountLayout>
        <AccountPageHeader
            icon="delivery"
            :title="$t('Доставка')"
            :description="$t('Опишите условия доставки для посетителей.')"
        />

        <form class="space-y-6" @submit.prevent="submit">
            <section :class="formCard">
                <h2 :class="[formTitle, errors.delivery ? 'text-red-600' : 'text-gray-900']">{{ $t('Условия доставки') }}</h2>
                <p class="text-sm leading-6 text-gray-500">
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

            <AccountFormActions :processing="form.processing" :success="form.recentlySuccessful" :hint="''" />
        </form>
    </AccountLayout>
</template>
