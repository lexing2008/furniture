<script setup lang="ts">
import AuthenticatedLayout from '@/admin/Layouts/AuthenticatedLayout.vue';
import Content from '@/admin/Components/Common/Content.vue';
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import type {
    CategoryInfo,
    SeoTemplateOption,
    TranslationFormData,
} from '@/admin/Components/Filters/filterForm';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';

interface Language {
    id?: number;
    code: string;
    name: string;
}

const props = defineProps<{
    category: CategoryInfo;
    filter: { id: number; type: string; digits_after_point: number | null };
    language: Language;
    languages: Language[];
    base: { name: string; unit: string };
    form: TranslationFormData;
    seoTemplates: SeoTemplateOption[];
}>();

const form = useForm<TranslationFormData>({
    name: props.form.name,
    unit: props.form.unit,
    is_active: props.form.is_active,
    variants: props.form.variants,
});

// Nested error keys like "variants.0.name_full" are not covered by the form typings
const errors = computed(() => form.errors as Record<string, string | undefined>);

const error = (index: number, field: string) => errors.value[`variants.${index}.${field}`];

const templates = computed(() => props.seoTemplates.filter((template) => template.languages.includes(props.language.code)));

const submit = () => {
    form.put(route('admin.filters.translations.update', [props.filter.id, props.language.code]));
};

// Number, three names, slug, template, active
const rowGrid =
    'lg:grid-cols-[2rem_minmax(9rem,1fr)_minmax(10rem,1.3fr)_minmax(10rem,1.3fr)_minmax(8rem,1fr)_minmax(9rem,1fr)_4.5rem]';

// On a large screen the column headers replace the labels
const cellLabel = 'block text-sm font-medium text-gray-700 lg:sr-only';

const selectClass =
    'mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 lg:mt-0';

</script>

<template>
    <Head :title="`Перевод фильтра: ${base.name}`" />

    <AuthenticatedLayout>
        <template #header>
            Перевод фильтра «{{ base.name }}» — {{ language.name }}
        </template>

        <Content>
            <div class="mb-6 flex flex-wrap items-center gap-2 border-b border-gray-200 pb-4 text-sm">
                <Link :href="route('admin.categories.filters.index', category.id)" class="mr-4 text-gray-600 underline hover:text-gray-900">
                    ← Фильтры: {{ category.name }}
                </Link>

                <Link
                    v-for="item in languages"
                    :key="item.code"
                    :href="route('admin.filters.translations.edit', [filter.id, item.code])"
                    class="rounded px-3 py-1"
                    :class="item.code === language.code ? 'bg-gray-800 text-white' : 'text-gray-700 hover:bg-gray-100'"
                >
                    {{ item.name }} <span class="uppercase opacity-60">({{ item.code }})</span>
                </Link>
            </div>

            <form @submit.prevent="submit" class="space-y-6">
                <div class="max-w-3xl space-y-6">
                <p class="rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-600">
                    Серым показан русский оригинал. Оставьте название пустым, если перевода на этот язык нет.
                </p>

                <div>
                    <InputLabel for="name" value="Название" />
                    <p v-if="base.name" class="text-xs text-gray-500">RU: {{ base.name }}</p>
                    <TextInput id="name" type="text" class="mt-1 block w-full" v-model="form.name" />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div>
                    <InputLabel for="unit" value="Единица измерения" />
                    <p v-if="base.unit" class="text-xs text-gray-500">RU: {{ base.unit }}</p>
                    <TextInput id="unit" type="text" class="mt-1 block w-48" v-model="form.unit" />
                    <InputError class="mt-2" :message="form.errors.unit" />
                </div>

                <label class="flex items-center gap-2">
                    <input
                        type="checkbox"
                        v-model="form.is_active"
                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                    />
                    <span class="text-sm text-gray-700">Перевод активен</span>
                </label>

                </div>

                <div v-if="form.variants.length > 0" class="space-y-3">
                    <h3 class="font-medium text-gray-900">Варианты</h3>

                    <!-- On a large screen one variant is one table row, the table scrolls sideways when it does not fit -->
                    <div class="overflow-x-auto">
                        <div class="lg:min-w-[64rem]">
                            <div
                                :class="rowGrid"
                                class="hidden gap-3 border-b border-gray-200 px-2 pb-2 text-xs font-medium uppercase tracking-wide text-gray-500 lg:grid"
                            >
                                <span>№</span>
                                <span>Название в фильтре</span>
                                <span>Полное название</span>
                                <span>Название для покупки</span>
                                <span>Slug</span>
                                <span>SEO-шаблон</span>
                                <span class="text-center">Активен</span>
                            </div>

                            <div
                                v-for="(variant, index) in form.variants"
                                :key="variant.id"
                                :class="rowGrid"
                                class="mb-3 space-y-3 rounded-md border border-gray-200 p-4 lg:mb-0 lg:grid lg:items-start lg:gap-3 lg:space-y-0 lg:rounded-none lg:border-0 lg:border-b lg:border-gray-100 lg:p-2"
                            >
                                <p class="text-sm font-medium text-gray-700 lg:pt-2">
                                    {{ index + 1 }}<span class="lg:hidden">. {{ variant.base.name_full || variant.base.name_in_filter }}</span>
                                </p>

                                <div>
                                    <label :for="`name_in_filter_${index}`" :class="cellLabel">Название в фильтре</label>
                                    <TextInput :id="`name_in_filter_${index}`" type="text" class="mt-1 block w-full lg:mt-0" v-model="variant.name_in_filter" />
                                    <p v-if="variant.base.name_in_filter" class="mt-1 text-xs text-gray-500">RU: {{ variant.base.name_in_filter }}</p>
                                    <InputError class="mt-1" :message="error(index, 'name_in_filter')" />
                                </div>

                                <div>
                                    <label :for="`name_full_${index}`" :class="cellLabel">Полное название</label>
                                    <TextInput :id="`name_full_${index}`" type="text" class="mt-1 block w-full lg:mt-0" v-model="variant.name_full" />
                                    <p v-if="variant.base.name_full" class="mt-1 text-xs text-gray-500">RU: {{ variant.base.name_full }}</p>
                                    <InputError class="mt-1" :message="error(index, 'name_full')" />
                                </div>

                                <div>
                                    <label :for="`name_buy_${index}`" :class="cellLabel">Название для покупки</label>
                                    <TextInput :id="`name_buy_${index}`" type="text" class="mt-1 block w-full lg:mt-0" v-model="variant.name_buy" />
                                    <p v-if="variant.base.name_buy" class="mt-1 text-xs text-gray-500">RU: {{ variant.base.name_buy }}</p>
                                    <InputError class="mt-1" :message="error(index, 'name_buy')" />
                                </div>

                                <div>
                                    <label :for="`slug_${index}`" :class="cellLabel">Slug</label>
                                    <TextInput
                                        :id="`slug_${index}`"
                                        type="text"
                                        class="mt-1 block w-full font-mono lg:mt-0"
                                        v-model="variant.slug"
                                        placeholder="Из полного названия"
                                    />
                                    <p v-if="variant.base.slug" class="mt-1 font-mono text-xs text-gray-500">RU: {{ variant.base.slug }}</p>
                                    <InputError class="mt-1" :message="error(index, 'slug')" />
                                </div>

                                <div>
                                    <label :for="`seo_template_id_${index}`" :class="cellLabel">SEO-шаблон</label>
                                    <select :id="`seo_template_id_${index}`" v-model="variant.seo_template_id" :class="selectClass">
                                        <option :value="null">— Без шаблона —</option>
                                        <option v-for="template in templates" :key="template.id" :value="template.id">{{ template.name }}</option>
                                    </select>
                                    <InputError class="mt-1" :message="error(index, 'seo_template_id')" />
                                </div>

                                <label class="flex items-center gap-2 lg:justify-center lg:pt-2">
                                    <input
                                        type="checkbox"
                                        v-model="variant.is_active"
                                        class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                    />
                                    <span class="text-sm text-gray-700 lg:hidden">Перевод варианта активен</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Variants are created in Russian: the edit page opens with an empty one -->
                    <Link
                        :href="route('admin.filters.edit', { filter: filter.id, add: 1 })"
                        class="inline-block rounded border border-gray-300 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50"
                    >
                        + Добавить вариант
                    </Link>
                </div>

                <div class="flex items-center gap-4">
                    <PrimaryButton :disabled="form.processing">Сохранить</PrimaryButton>

                    <Link
                        :href="route('admin.categories.filters.index', category.id)"
                        class="text-sm text-gray-600 underline hover:text-gray-900"
                    >
                        Отмена
                    </Link>
                </div>
            </form>
        </Content>
    </AuthenticatedLayout>
</template>
