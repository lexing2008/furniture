<script setup lang="ts">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import type { Language } from '@/admin/Components/Categories/categoryForm';
import WysiwygEditor from '@/common/Components/WysiwygEditor.vue';
import { Link } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type { SeoTemplateFormData, SeoTemplateTranslationData } from './seoTemplateForm';

const props = defineProps<{
    form: InertiaForm<SeoTemplateFormData>;
    languages: Language[];
}>();

defineEmits<{
    submit: [];
}>();

const activeTab = ref(0);

// Nested error keys like "translations.0.name" are not covered by the form typings
const errors = computed(() => props.form.errors as Record<string, string | undefined>);

const error = (index: number, field: keyof SeoTemplateTranslationData) => errors.value[`translations.${index}.${field}`];

const tabHasErrors = (index: number) =>
    Object.keys(errors.value).some((key) => key.startsWith(`translations.${index}.`));

const inputClass =
    'mt-1 block w-full rounded-md border-gray-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500';
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="max-w-3xl space-y-6">
        <div
            v-if="languages.length === 0"
            class="rounded-md bg-yellow-50 px-4 py-3 text-sm text-yellow-800"
        >
            Сначала
            <Link :href="route('admin.languages.create')" class="font-medium underline">добавьте язык</Link>,
            чтобы заполнить переводы шаблона.
        </div>

        <div>
            <InputLabel for="name" value="Название шаблона" />
            <TextInput id="name" type="text" class="mt-1 block w-full" v-model="props.form.name" />
            <p class="mt-1 text-xs text-gray-500">Видно только в админке.</p>
            <InputError class="mt-2" :message="props.form.errors.name" />
        </div>

        <div v-if="languages.length > 0">
            <div class="flex flex-wrap gap-1 border-b border-gray-200">
                <button
                    v-for="(language, index) in languages"
                    :key="language.code"
                    type="button"
                    class="-mb-px rounded-t-md border px-4 py-2 text-sm"
                    :class="activeTab === index
                        ? 'border-gray-200 border-b-white bg-white font-semibold text-gray-900'
                        : 'border-transparent text-gray-500 hover:text-gray-700'"
                    @click="activeTab = index"
                >
                    {{ language.name }}
                    <span class="uppercase text-gray-400">({{ language.code }})</span>
                    <span v-if="tabHasErrors(index)" class="ml-1 inline-block h-2 w-2 rounded-full bg-red-500" />
                </button>
            </div>

            <InputError class="mt-2" :message="errors.translations" />

            <template v-for="(translation, index) in props.form.translations" :key="translation.lang">
                <div v-show="activeTab === index" class="space-y-6 pt-6">
                    <p class="text-xs text-gray-500">
                        Оставьте название пустым, если шаблон на этом языке не нужен.
                        В SEO-полях можно использовать <code class="rounded-sm bg-gray-100 px-1">{name}</code> —
                        подставится название категории.
                    </p>

                    <div>
                        <InputLabel :for="`name_${index}`" value="Название" />
                        <TextInput :id="`name_${index}`" type="text" class="mt-1 block w-full" v-model="translation.name" />
                        <InputError class="mt-2" :message="error(index, 'name')" />
                    </div>

                    <div>
                        <InputLabel :for="`seo_title_${index}`" value="SEO page title" />
                        <TextInput
                            :id="`seo_title_${index}`"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="translation.seo_title"
                            placeholder="Купить {name} недорого"
                        />
                        <InputError class="mt-2" :message="error(index, 'seo_title')" />
                    </div>

                    <div>
                        <InputLabel :for="`seo_description_${index}`" value="SEO description" />
                        <textarea
                            :id="`seo_description_${index}`"
                            v-model="translation.seo_description"
                            rows="3"
                            maxlength="500"
                            :class="inputClass"
                        />
                        <p class="mt-1 text-xs text-gray-500">{{ translation.seo_description.length }} / 500</p>
                        <InputError class="mt-2" :message="error(index, 'seo_description')" />
                    </div>

                    <div>
                        <InputLabel :for="`seo_block_${index}`" value="SEO block" />
                        <WysiwygEditor
                            v-model="translation.seo_block"
                            :invalid="!!error(index, 'seo_block')"
                        />
                        <InputError class="mt-2" :message="error(index, 'seo_block')" />
                    </div>
                </div>
            </template>
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="props.form.processing || languages.length === 0">Сохранить</PrimaryButton>

            <Link
                :href="route('admin.seo-templates.index')"
                class="text-sm text-gray-600 underline hover:text-gray-900"
            >
                Отмена
            </Link>
        </div>
    </form>
</template>
