<script setup lang="ts">
import type { CompanySocialsFields } from '@/account/Components/Company/companyForm';
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import AccountFormActions from '@/account/Components/Forms/AccountFormActions.vue';
import AccountPageHeader from '@/account/Components/Forms/AccountPageHeader.vue';
import { formCard, formGrid, formTitle } from '@/account/Components/Forms/formStyles';
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

</script>

<template>
    <Head :title="$t('Соцсети')" />

    <AccountLayout>
        <AccountPageHeader
            icon="socials"
            :title="$t('Соцсети')"
            :description="$t('Добавьте ссылки на страницы компании в соцсетях.')"
        />

        <form class="space-y-6" @submit.prevent="submit">
            <section :class="formCard">
                <h2 :class="[formTitle, Object.keys(errors).some((key) => key.startsWith('socials.')) ? 'text-red-600' : 'text-gray-900']">{{ $t('Ссылки на соцсети') }}</h2>
                <p class="text-sm leading-6 text-gray-500">{{ $t('Укажите полные адреса страниц компании. Пустые поля не сохраняются.') }}</p>

                <div :class="formGrid">
                    <div v-for="(title, key) in socials" :key="key">
                        <InputLabel :for="`social_${key}`" :value="title" :invalid="!!errors[`socials.${key}`]" />
                        <TextInput
                            :id="`social_${key}`"
                            :invalid="!!errors[`socials.${key}`]"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.socials[key]"
                            placeholder="https://"
                        />
                        <InputError class="mt-2" :message="errors[`socials.${key}`]" />
                    </div>
                </div>
            </section>

            <AccountFormActions :processing="form.processing" :success="form.recentlySuccessful" />
        </form>
    </AccountLayout>
</template>
