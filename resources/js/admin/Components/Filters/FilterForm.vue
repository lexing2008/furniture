<script setup lang="ts">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import { Link } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import { emptyVariant } from './filterForm';
import type { FilterFormData, FilterTypeOption, SeoTemplateOption } from './filterForm';

const props = defineProps<{
    form: InertiaForm<FilterFormData>;
    types: FilterTypeOption[];
    seoTemplates: SeoTemplateOption[];
    categoryId: number;
    // The type of a saved filter cannot be changed
    typeLocked: boolean;
}>();

defineEmits<{
    submit: [];
}>();

const currentType = computed(() => props.types.find((type) => type.value === props.form.type));

const hasVariants = computed(() => currentType.value?.has_variants ?? false);
const isFloat = computed(() => props.form.type === 'input_float');

// Nested error keys like "variants.0.name_full" are not covered by the form typings
const errors = computed(() => props.form.errors as Record<string, string | undefined>);

const error = (index: number, field: string) => errors.value[`variants.${index}.${field}`];

// Filters are filled in Russian, so only the templates translated to it can be linked to a translation
const russianTemplates = computed(() => props.seoTemplates.filter((template) => template.languages.includes('ru')));

const addVariant = () => props.form.variants.push(emptyVariant());

const removeVariant = (index: number) => props.form.variants.splice(index, 1);

const moveVariant = (index: number, shift: -1 | 1) => {
    const target = index + shift;

    if (target < 0 || target >= props.form.variants.length) {
        return;
    }

    const [variant] = props.form.variants.splice(index, 1);
    props.form.variants.splice(target, 0, variant);
};

const inputClass =
    'mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500';

// Order, three names, slug, two templates, active, delete
const rowGrid =
    'lg:grid-cols-[5rem_minmax(8rem,1fr)_minmax(9rem,1.3fr)_minmax(9rem,1.3fr)_minmax(7rem,1fr)_minmax(8rem,1fr)_minmax(8rem,1fr)_4.5rem_5.5rem]';

// On a large screen the column headers replace the labels
const cellLabel = 'block text-sm font-medium text-gray-700 lg:sr-only';

const selectClass =
    'mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500 lg:mt-0';

const smallButton = 'rounded border border-gray-300 px-2 py-1 text-xs text-gray-600 hover:bg-gray-50 disabled:opacity-30';
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="space-y-6">
        <div class="max-w-3xl space-y-6">
        <p class="rounded-md bg-gray-50 px-4 py-3 text-sm text-gray-600">
            Фильтр заполняется на русском языке. Остальные языки — на отдельной странице перевода.
        </p>

        <div>
            <InputLabel for="type" value="Тип" />

            <select id="type" v-model="props.form.type" :disabled="typeLocked" :class="inputClass">
                <option v-for="type in types" :key="type.value" :value="type.value">{{ type.label }}</option>
            </select>

            <p v-if="typeLocked" class="mt-1 text-xs text-gray-500">Тип сохранённого фильтра изменить нельзя.</p>

            <InputError class="mt-2" :message="props.form.errors.type" />
        </div>

        <div>
            <InputLabel for="name" value="Название" />
            <TextInput id="name" type="text" class="mt-1 block w-full" v-model="props.form.name" />
            <InputError class="mt-2" :message="props.form.errors.name" />
        </div>

        <div>
            <InputLabel for="unit" value="Единица измерения" />
            <TextInput id="unit" type="text" class="mt-1 block w-48" v-model="props.form.unit" placeholder="см, кг, л" />
            <InputError class="mt-2" :message="props.form.errors.unit" />
        </div>

        <div v-if="isFloat">
            <InputLabel for="digits_after_point" value="Знаков после запятой" />
            <input
                id="digits_after_point"
                type="number"
                min="1"
                max="6"
                class="mt-1 block w-32 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                v-model.number="props.form.digits_after_point"
            />
            <InputError class="mt-2" :message="props.form.errors.digits_after_point" />
        </div>

        <label class="flex items-center gap-2">
            <input
                type="checkbox"
                v-model="props.form.is_active"
                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
            />
            <span class="text-sm text-gray-700">Активен (показывается на сайте)</span>
        </label>

        </div>

        <div v-if="hasVariants" class="space-y-3">
            <div class="flex items-center justify-between">
                <h3 class="font-medium text-gray-900">Варианты</h3>
                <button type="button" :class="smallButton" @click="addVariant">+ Добавить вариант</button>
            </div>

            <InputError :message="errors.variants" />

            <p v-if="props.form.variants.length === 0" class="text-sm text-gray-500">Вариантов пока нет.</p>

            <!-- On a large screen one variant is one table row, the table scrolls sideways when it does not fit -->
            <div v-else class="overflow-x-auto">
                <div class="lg:min-w-[68rem]">
                    <div
                        :class="rowGrid"
                        class="hidden gap-3 border-b border-gray-200 px-2 pb-2 text-xs font-medium uppercase tracking-wide text-gray-500 lg:grid"
                    >
                        <span>Порядок</span>
                        <span>Название в фильтре</span>
                        <span>Полное название</span>
                        <span>Название для покупки</span>
                        <span>Slug</span>
                        <span>SEO-шаблон (все языки)</span>
                        <span>SEO-шаблон (ru)</span>
                        <span class="text-center">Активен</span>
                        <span />
                    </div>

                    <div
                        v-for="(variant, index) in props.form.variants"
                        :key="variant.id ?? `new-${index}`"
                        :class="rowGrid"
                        class="mb-3 space-y-3 rounded-md border border-gray-200 p-4 lg:mb-0 lg:grid lg:items-start lg:gap-3 lg:space-y-0 lg:rounded-none lg:border-0 lg:border-b lg:border-gray-100 lg:p-2"
                    >
                        <div class="flex items-center justify-between lg:justify-start lg:gap-1 lg:pt-1">
                            <span class="text-sm font-medium text-gray-700 lg:hidden">Вариант {{ index + 1 }}</span>

                            <div class="flex gap-1">
                                <button type="button" :class="smallButton" :disabled="index === 0" title="Выше" @click="moveVariant(index, -1)">↑</button>
                                <button
                                    type="button"
                                    :class="smallButton"
                                    :disabled="index === props.form.variants.length - 1"
                                    title="Ниже"
                                    @click="moveVariant(index, 1)"
                                >↓</button>
                            </div>
                        </div>

                        <div>
                            <label :for="`name_in_filter_${index}`" :class="cellLabel">Название в фильтре</label>
                            <TextInput
                                :id="`name_in_filter_${index}`"
                                type="text"
                                class="mt-1 block w-full lg:mt-0"
                                v-model="variant.name_in_filter"
                                placeholder="Угловой"
                            />
                            <InputError class="mt-1" :message="error(index, 'name_in_filter')" />
                        </div>

                        <div>
                            <label :for="`name_full_${index}`" :class="cellLabel">Полное название</label>
                            <TextInput
                                :id="`name_full_${index}`"
                                type="text"
                                class="mt-1 block w-full lg:mt-0"
                                v-model="variant.name_full"
                                placeholder="Угловой диван"
                            />
                            <InputError class="mt-1" :message="error(index, 'name_full')" />
                        </div>

                        <div>
                            <label :for="`name_buy_${index}`" :class="cellLabel">Название для покупки</label>
                            <TextInput
                                :id="`name_buy_${index}`"
                                type="text"
                                class="mt-1 block w-full lg:mt-0"
                                v-model="variant.name_buy"
                                placeholder="Купить угловой диван"
                            />
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
                            <InputError class="mt-1" :message="error(index, 'slug')" />
                        </div>

                        <div>
                            <label :for="`seo_template_id_${index}`" :class="cellLabel">SEO-шаблон варианта (все языки)</label>
                            <select
                                :id="`seo_template_id_${index}`"
                                v-model="variant.seo_template_id"
                                :class="selectClass"
                            >
                                <option :value="null">— Без шаблона —</option>
                                <option v-for="template in seoTemplates" :key="template.id" :value="template.id">{{ template.name }}</option>
                            </select>
                            <InputError class="mt-1" :message="error(index, 'seo_template_id')" />
                        </div>

                        <div>
                            <label :for="`translation_seo_template_id_${index}`" :class="cellLabel">
                                SEO-шаблон русского перевода (приоритетнее шаблона варианта)
                            </label>
                            <select
                                :id="`translation_seo_template_id_${index}`"
                                v-model="variant.translation_seo_template_id"
                                :class="selectClass"
                            >
                                <option :value="null">— Без шаблона —</option>
                                <option v-for="template in russianTemplates" :key="template.id" :value="template.id">{{ template.name }}</option>
                            </select>
                            <InputError class="mt-1" :message="error(index, 'translation_seo_template_id')" />
                        </div>

                        <label class="flex items-center gap-2 lg:justify-center lg:pt-2">
                            <input
                                type="checkbox"
                                v-model="variant.is_active"
                                class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                            />
                            <span class="text-sm text-gray-700 lg:hidden">Вариант активен</span>
                        </label>

                        <div class="lg:pt-1 lg:text-right">
                            <button type="button" :class="[smallButton, 'text-red-600']" @click="removeVariant(index)">Удалить</button>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="props.form.variants.length > 0">
                <button type="button" :class="smallButton" @click="addVariant">+ Добавить вариант</button>
            </div>
        </div>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="props.form.processing">Сохранить</PrimaryButton>

            <Link
                :href="route('admin.categories.filters.index', categoryId)"
                class="text-sm text-gray-600 underline hover:text-gray-900"
            >
                Отмена
            </Link>
        </div>
    </form>
</template>
