<script setup lang="ts">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import PrimaryButton from '@/account/Components/PrimaryButton.vue';
import TextInput from '@/account/Components/TextInput.vue';
import { Link } from '@inertiajs/vue3';
import type { InertiaForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import type { Language, ParentOption, RegionFormData } from './regionForm';

const props = defineProps<{
    form: InertiaForm<RegionFormData>;
    languages: Language[];
    parents: ParentOption[];
    domain: string;
}>();

defineEmits<{
    submit: [];
}>();

// Only a country has its own language and obligatory SEO texts
const isCountry = computed(() => props.form.parent_id === null);

const indent = (depth: number) => '  '.repeat(depth * 2) + (depth > 0 ? '└ ' : '');

const inputClass =
    'mt-1 block w-full rounded-md border-gray-300 shadow-xs focus:border-indigo-500 focus:ring-indigo-500';
</script>

<template>
    <form @submit.prevent="$emit('submit')" class="max-w-3xl space-y-6">
        <div>
            <InputLabel for="parent_id" value="Родитель" />

            <select id="parent_id" v-model="props.form.parent_id" :class="inputClass">
                <option :value="null">— Нет (это страна) —</option>
                <option v-for="parent in parents" :key="parent.id" :value="parent.id">
                    {{ indent(parent.depth) }}{{ parent.name }}
                </option>
            </select>

            <p class="mt-1 text-xs text-gray-500">
                Страна состоит из областей, область — из городов. Родитель области — страна, родитель города — область.
            </p>

            <InputError class="mt-2" :message="props.form.errors.parent_id" />
        </div>

        <div>
            <InputLabel for="name" value="Название" />
            <TextInput id="name" type="text" class="mt-1 block w-full" v-model="props.form.name" />
            <InputError class="mt-2" :message="props.form.errors.name" />
        </div>

        <div>
            <InputLabel for="name_en" value="Название на английском" />
            <TextInput id="name_en" type="text" class="mt-1 block w-full" v-model="props.form.name_en" />
            <InputError class="mt-2" :message="props.form.errors.name_en" />
        </div>

        <div>
            <InputLabel for="name_in" value="Название в предложном падеже" />
            <TextInput
                id="name_in"
                type="text"
                class="mt-1 block w-full"
                v-model="props.form.name_in"
                placeholder="Например: в Берлине"
            />
            <InputError class="mt-2" :message="props.form.errors.name_in" />
        </div>

        <div>
            <InputLabel for="subdomain" value="Поддомен" />

            <div class="mt-1 flex items-center gap-2">
                <TextInput id="subdomain" type="text" class="block w-48 font-mono" v-model="props.form.subdomain" />
                <span class="font-mono text-sm text-gray-500">.{{ domain }}</span>
            </div>

            <InputError class="mt-2" :message="props.form.errors.subdomain" />
        </div>

        <div v-if="isCountry">
            <InputLabel for="lang" value="Язык" />

            <select id="lang" v-model="props.form.lang" :class="inputClass">
                <option value="">— Выберите язык —</option>
                <option v-for="language in languages" :key="language.code" :value="language.code">
                    {{ language.name }} ({{ language.code }})
                </option>
            </select>

            <p class="mt-1 text-xs text-gray-500">Языком страны будут пользоваться все её области и города.</p>

            <InputError class="mt-2" :message="props.form.errors.lang" />
        </div>

        <p class="text-xs text-gray-500">
            <template v-if="isCountry">SEO-поля страны обязательны: из неё их наследуют области и города.</template>
            <template v-else>Пустые SEO-поля берутся из страны.</template>
        </p>

        <div>
            <InputLabel for="seo_title" value="SEO page title" />
            <TextInput id="seo_title" type="text" class="mt-1 block w-full" v-model="props.form.seo_title" />
            <InputError class="mt-2" :message="props.form.errors.seo_title" />
        </div>

        <div>
            <InputLabel for="seo_description" value="SEO description" />
            <textarea
                id="seo_description"
                v-model="props.form.seo_description"
                rows="3"
                maxlength="500"
                :class="inputClass"
            />
            <p class="mt-1 text-xs text-gray-500">{{ props.form.seo_description.length }} / 500</p>
            <InputError class="mt-2" :message="props.form.errors.seo_description" />
        </div>

        <div>
            <InputLabel for="seo_block" value="SEO block" />
            <textarea id="seo_block" v-model="props.form.seo_block" rows="8" :class="inputClass" />
            <InputError class="mt-2" :message="props.form.errors.seo_block" />
        </div>

        <label class="flex items-center gap-2">
            <input
                type="checkbox"
                v-model="props.form.is_capital"
                class="rounded-sm border-gray-300 text-indigo-600 shadow-xs focus:ring-indigo-500"
            />
            <span class="text-sm text-gray-700">Столица области</span>
        </label>

        <label class="flex items-center gap-2">
            <input
                type="checkbox"
                v-model="props.form.is_active"
                class="rounded-sm border-gray-300 text-indigo-600 shadow-xs focus:ring-indigo-500"
            />
            <span class="text-sm text-gray-700">Активен (страница доступна на сайте)</span>
        </label>

        <div class="flex items-center gap-4">
            <PrimaryButton :disabled="props.form.processing">Сохранить</PrimaryButton>

            <Link
                :href="route('admin.regions.index')"
                class="text-sm text-gray-600 underline hover:text-gray-900"
            >
                Отмена
            </Link>
        </div>
    </form>
</template>
