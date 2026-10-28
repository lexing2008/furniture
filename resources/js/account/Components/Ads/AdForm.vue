<script setup lang="ts">
import InputError from '@/account/Components/InputError.vue';
import InputLabel from '@/account/Components/InputLabel.vue';
import AccountFormActions from '@/account/Components/Forms/AccountFormActions.vue';
import { formCard, formHint, formTitle } from '@/account/Components/Forms/formStyles';
import TextInput from '@/account/Components/TextInput.vue';
import AdRegionTree from '@/account/Components/Ads/AdRegionTree.vue';
import CategoryTreeSelect from '@/account/Components/Ads/CategoryTreeSelect.vue';
import type { CategoryNode, RegionNode } from '@/account/Components/Company/companyForm';
import WysiwygEditor from '@/common/Components/WysiwygEditor.vue';
import { translate } from '@/common/i18n';
import { Link, useForm } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import type {
    AdCopySource,
    AdData,
    AdFilter,
    AdFormData,
    AdImageItem,
    FilterValue,
    WarrantyUnit,
} from './adForm';
import { emptyAdForm } from './adForm';

const props = withDefaults(
    defineProps<{
        categories: CategoryNode[];
        regions: RegionNode[];
        currency: string | null;
        currencyDecimals: number;
        defaultRegionId?: number | null;
        ad?: AdData | null;
        source?: AdCopySource | null;
        copyUrl?: string | null;
        initialFilters?: AdFilter[];
    }>(),
    {
        defaultRegionId: null,
        ad: null,
        source: null,
        copyUrl: null,
        initialFilters: () => [],
    },
);

const isEdit = computed(() => props.ad !== null);

const initialForm = (): AdFormData => {
    if (props.ad !== null) {
        return {
            title: props.ad.title,
            number: props.ad.number,
            article: props.ad.article,
            category_id: props.ad.category_id,
            description: props.ad.description,
            price: props.ad.price,
            warranty_value: props.ad.warranty_value === null ? '' : String(props.ad.warranty_value),
            warranty_unit: props.ad.warranty_unit,
            video: props.ad.video,
            is_active: props.ad.is_active,
            regions: [...props.ad.regions],
            filters: filterState(props.initialFilters),
            existing_images: [],
            new_images: [],
            deleted_image_ids: [],
        };
    }

    if (props.source !== null) {
        return {
            ...emptyAdForm(props.defaultRegionId),
            category_id: props.source.category_id,
            warranty_value: props.source.warranty_value === null ? '' : String(props.source.warranty_value),
            warranty_unit: props.source.warranty_unit,
            description: props.source.description,
            regions: [...props.source.regions],
            filters: filterState(props.initialFilters),
        };
    }

    return emptyAdForm(props.defaultRegionId);
};

const form = useForm<AdFormData>(initialForm());

const images = ref<AdImageItem[]>(
    (props.ad?.images ?? []).map((image) => ({
        key: `existing-${image.id}`,
        id: image.id,
        url: image.url,
        thumb630Url: image.thumb_630_url,
        thumb100Url: image.thumb_100_url,
        progress: 100,
        uploading: false,
    })),
);
const deletedIds = ref<number[]>([]);
const fileInput = ref<HTMLInputElement | null>(null);
const dragIndex = ref<number | null>(null);
const dropTargetIndex = ref<number | null>(null);
const uploads = new Map<string, XMLHttpRequest>();
const uploadError = ref<string | null>(null);
const imageUrl = ref('');
const urlUploading = ref(false);
const urlError = ref<string | null>(null);
const uploadingPlaceholder =
    'data:image/svg+xml,%3Csvg xmlns=%22http://www.w3.org/2000/svg%22 width=%22800%22 height=%22600%22%3E%3Crect width=%22100%25%22 height=%22100%25%22 fill=%22%23f3f4f6%22/%3E%3C/svg%3E';
const filters = ref<AdFilter[]>([...props.initialFilters]);
const filtersLoading = ref(false);
const filtersError = ref<string | null>(null);

const isUploading = computed(() => urlUploading.value || images.value.some((image) => image.uploading));
const errors = computed(() => form.errors as Record<string, string | undefined>);

const onUrlPaste = (event: ClipboardEvent) => {
    const value = event.clipboardData?.getData('text')?.trim();

    if (!value) {
        return;
    }

    imageUrl.value = value;
    event.preventDefault();

    if (/^https?:\/\//i.test(value)) {
        void uploadUrl();
    }
};

const uploadUrl = async () => {
    const url = imageUrl.value.trim();
    urlError.value = null;

    if (url === '' || urlUploading.value) {
        return;
    }

    if (images.value.length >= 15) {
        urlError.value = translate('Максимум 15 изображений.');

        return;
    }

    const item: AdImageItem = {
        key: `url-${Date.now()}-${Math.random()}`,
        url: uploadingPlaceholder,
        progress: 0,
        uploading: true,
    };

    images.value.push(item);
    urlUploading.value = true;

    try {
        const response = await fetch(route('account.ads.images.url'), {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ url }),
        });
        const data = (await response.json().catch(() => ({}))) as {
            id?: number;
            url?: string;
            thumb_630_url?: string;
            thumb_100_url?: string;
            message?: string;
            errors?: Record<string, string[]>;
        };

        if (!response.ok) {
            images.value = images.value.filter((image) => image.key !== item.key);
            urlError.value =
                data.errors?.url?.[0] ??
                data.errors?.image?.[0] ??
                data.message ??
                translate('Не удалось загрузить изображение по ссылке.');

            return;
        }

        item.tempFileId = data.id;
        item.url = data.url ?? item.url;
        item.thumb630Url = data.thumb_630_url;
        item.thumb100Url = data.thumb_100_url;
        item.progress = 100;
        item.uploading = false;
        imageUrl.value = '';
    } catch {
        images.value = images.value.filter((image) => image.key !== item.key);
        urlError.value = translate('Не удалось загрузить изображение по ссылке.');
    } finally {
        urlUploading.value = false;
    }
};

const descriptionLength = computed(() => {
    const html = form.description ?? '';

    if (typeof document === 'undefined') {
        return [...html.replace(/<[^>]*>/g, '')].length;
    }

    const container = document.createElement('div');
    container.innerHTML = html;

    return [...(container.textContent ?? '')].length;
});

const priceStep = computed(() => {
    const decimals = Math.max(0, Math.min(4, props.currencyDecimals));

    return String(Number((1 / 10 ** decimals).toFixed(decimals)));
});

const warrantyUnits = computed<Array<{ value: WarrantyUnit; label: string }>>(() => [
    { value: 'days', label: translate('Дни') },
    { value: 'months', label: translate('Месяцы') },
    { value: 'years', label: translate('Годы') },
]);

const youtubeEmbedUrl = computed(() => {
    const raw = form.video.trim();

    if (raw === '') {
        return null;
    }

    let url: URL;

    try {
        url = new URL(/^https?:\/\//i.test(raw) ? raw : `https://${raw}`);
    } catch {
        return null;
    }

    const host = url.hostname.toLowerCase().replace(/^www\./i, '');
    let id: string | null = null;

    if (host === 'youtu.be') {
        id = url.pathname.split('/').filter(Boolean)[0] ?? null;
    } else if (host === 'youtube.com' || host.endsWith('.youtube.com')) {
        if (url.pathname === '/watch') {
            id = url.searchParams.get('v');
        } else {
            const parts = url.pathname.split('/').filter(Boolean);

            if (['embed', 'shorts', 'live', 'v'].includes(parts[0] ?? '')) {
                id = parts[1] ?? null;
            }
        }
    }

    return id !== null && /^[A-Za-z0-9_-]{11}$/.test(id)
        ? `https://www.youtube-nocookie.com/embed/${id}`
        : null;
});

function filterState(source: AdFilter[]): Record<string, FilterValue> {
    const values: Record<string, FilterValue> = {};

    for (const filter of source) {
        if (filter.type === 'checkbox') {
            values[String(filter.id)] = Array.isArray(filter.value)
                ? filter.value.map(Number)
                : [];
        } else {
            values[String(filter.id)] = filter.value ?? '';
        }
    }

    return values;
}

const loadFilters = async (categoryId: number | null = form.category_id) => {
    filtersError.value = null;

    if (!categoryId) {
        filters.value = [];
        form.filters = {};

        return;
    }

    filters.value = [];
    form.filters = {};
    filtersLoading.value = true;

    try {
        const response = await fetch(`${route('account.ads.filters')}?category_id=${categoryId}`, {
            headers: { Accept: 'application/json' },
        });

        if (!response.ok) {
            filtersError.value = translate('Не удалось загрузить фильтры.');

            return;
        }

        const data = (await response.json()) as { filters: AdFilter[] };
        filters.value = data.filters;
        form.filters = filterState(data.filters);
    } catch {
        filtersError.value = translate('Не удалось загрузить фильтры.');
    } finally {
        filtersLoading.value = false;
    }
};

const selectedVariants = (filterId: number): number[] => {
    const value = form.filters[String(filterId)];

    return Array.isArray(value) ? value.map(Number) : [];
};

const isVariantChecked = (filterId: number, variantId: number): boolean =>
    selectedVariants(filterId).includes(variantId);

const toggleVariant = (filterId: number, variantId: number, event: Event) => {
    const checked = (event.target as HTMLInputElement).checked;
    const values = selectedVariants(filterId);

    form.filters[String(filterId)] = checked
        ? [...values, variantId]
        : values.filter((id) => id !== variantId);
};

const setRadio = (filterId: number, variantId: number) => {
    form.filters[String(filterId)] = variantId;
};

const setFilterValue = (filterId: number, event: Event) => {
    form.filters[String(filterId)] = (event.target as HTMLInputElement).value;
};

const filterError = (filterId: number): string | undefined => errors.value[`filters.${filterId}`];

const csrfToken = (): string => document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const openFileDialog = () => fileInput.value?.click();

const onFileInput = (event: Event) => {
    const input = event.target as HTMLInputElement;

    if (input.files) {
        uploadFiles(input.files);
    }

    input.value = '';
};

const onDropzone = (event: DragEvent) => {
    if (event.dataTransfer?.files.length) {
        uploadFiles(event.dataTransfer.files);
    }
};

const uploadFiles = (files: FileList | File[]) => {
    uploadError.value = null;

    const imagesToUpload = Array.from(files).filter((file) => file.type.startsWith('image/'));
    const freeSlots = 15 - images.value.length;

    if (freeSlots <= 0) {
        uploadError.value = translate('Максимум 15 изображений.');

        return;
    }

    if (imagesToUpload.length > freeSlots) {
        uploadError.value = translate('Максимум 15 изображений.');
    }

    imagesToUpload.slice(0, freeSlots).forEach(uploadFile);
};

const uploadFile = (file: File) => {
    const item: AdImageItem = {
        key: `new-${Date.now()}-${Math.random()}`,
        url: URL.createObjectURL(file),
        progress: 0,
        uploading: true,
    };

    images.value.push(item);
    const uploaded = images.value[images.value.length - 1];

    const request = new XMLHttpRequest();
    request.open('POST', route('account.ads.images.store'));

    const csrf = csrfToken();

    if (csrf) {
        request.setRequestHeader('X-CSRF-TOKEN', csrf);
    }

    request.setRequestHeader('Accept', 'application/json');
    uploads.set(item.key, request);

    request.upload.onprogress = (event) => {
        if (event.lengthComputable) {
            uploaded.progress = Math.round((event.loaded / event.total) * 100);
        }
    };

    request.onload = () => {
        uploaded.uploading = false;

        uploads.delete(item.key);

        if (request.status === 201) {
            const response = JSON.parse(request.responseText) as {
                id: number;
                url: string;
                thumb_630_url: string;
                thumb_100_url: string;
            };
            uploaded.tempFileId = response.id;
            uploaded.url = response.url;
            uploaded.thumb630Url = response.thumb_630_url;
            uploaded.thumb100Url = response.thumb_100_url;
            uploaded.progress = 100;

            return;
        }

        uploaded.progress = 0;
        uploaded.error = translate('Не удалось загрузить изображение.');
    };

    request.onerror = () => {
        uploads.delete(item.key);
        uploaded.uploading = false;
        uploaded.progress = 0;
        uploaded.error = translate('Не удалось загрузить изображение.');
    };

    const data = new FormData();
    data.append('image', file);
    request.send(data);
};

const removeImage = async (index: number) => {
    const image = images.value[index];
    uploadError.value = null;

    if (image.uploading) {
        uploads.get(image.key)?.abort();
        uploads.delete(image.key);
    }

    if (image.tempFileId) {
        try {
            const response = await fetch(route('account.ads.images.destroy', image.tempFileId), {
                method: 'DELETE',
                headers: {
                    Accept: 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
            });

            if (!response.ok && response.status !== 404) {
                uploadError.value = translate('Не удалось удалить изображение.');

                return;
            }
        } catch {
            uploadError.value = translate('Не удалось удалить изображение.');

            return;
        }
    }

    if (image.id) {
        deletedIds.value.push(image.id);
    }

    if (image.url.startsWith('blob:')) {
        URL.revokeObjectURL(image.url);
    }

    images.value.splice(index, 1);
};

const moveImage = (from: number, to: number) => {
    if (to < 0 || to >= images.value.length || from === to) {
        return;
    }

    const [image] = images.value.splice(from, 1);
    images.value.splice(to, 0, image);
};

const startDrag = (index: number, event: DragEvent) => {
    dragIndex.value = index;
    dropTargetIndex.value = null;
    event.dataTransfer?.setData('text/plain', String(index));

    if (event.dataTransfer) {
        event.dataTransfer.effectAllowed = 'move';
    }
};

const dragEnter = (index: number) => {
    if (dragIndex.value !== null && dragIndex.value !== index) {
        dropTargetIndex.value = index;
    }
};

const dragEnd = () => {
    dragIndex.value = null;
    dropTargetIndex.value = null;
};

const dropAt = (index: number) => {
    if (dragIndex.value !== null) {
        moveImage(dragIndex.value, index);
    }

    dragIndex.value = null;
    dropTargetIndex.value = null;
};

const submit = () => {
    form.existing_images = images.value
        .filter((image) => image.id !== undefined)
        .map((image, order) => ({ id: image.id as number, order }));
    form.new_images = images.value
        .filter((image) => image.tempFileId !== undefined)
        .map((image, order) => ({ temp_file_id: image.tempFileId as number, order }));
    form.deleted_image_ids = deletedIds.value;

    if (isEdit.value && props.ad) {
        form.put(route('account.ads.update', props.ad.id), { preserveScroll: true });

        return;
    }

    form.post(route('account.ads.store'), { preserveScroll: true });
};

const hint = formHint;
const smallButton =
    'cursor-pointer rounded-full border border-gray-300 bg-white/90 px-2.5 py-1 text-xs font-medium text-gray-600 shadow-xs transition hover:border-green-first hover:text-green-800';
const dropzone =
    'flex min-h-32 cursor-pointer flex-col items-center justify-center rounded-xl border-2 border-dashed border-gray-300 bg-gray-50/50 px-4 py-8 text-center text-sm text-gray-500 transition hover:border-green-first hover:bg-green-50/30 hover:text-green-800';
</script>

<template>
    <div>
        <form class="space-y-6" @submit.prevent="submit">
        <section :class="formCard">
            <h2 :class="[formTitle, 'text-gray-900']">{{ $t('Основная информация') }}</h2>

            <div>
                <InputLabel for="title" :value="$t('Название')" required :invalid="!!form.errors.title" />
                <TextInput id="title" v-model="form.title" type="text" class="mt-1 block w-full" :placeholder="$t('Например, диван «Осло»')" :invalid="!!form.errors.title" />
                <InputError class="mt-2" :message="form.errors.title" />
            </div>

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <InputLabel for="article" :value="$t('Артикул')" :invalid="!!form.errors.article" />
                    <TextInput id="article" v-model="form.article" type="text" class="mt-1 block w-full" :placeholder="$t('Например, ART-12345')" :invalid="!!form.errors.article" />
                    <InputError class="mt-2" :message="form.errors.article" />
                </div>

                <div>
                    <InputLabel for="number" :value="$t('Номер в собственной системе (необязательно)')" :invalid="!!form.errors.number" />
                    <TextInput id="number" v-model="form.number" type="text" class="mt-1 block w-full" :placeholder="$t('Например, 12345')" :invalid="!!form.errors.number" />
                    <p :class="hint">{{ $t('Уникальный номер товара или услуги в вашей системе.') }}</p>
                    <InputError class="mt-2" :message="form.errors.number" />
                </div>
            </div>

            <div>
                <InputLabel :value="$t('Категория')" required :invalid="!!form.errors.category_id" />
                <CategoryTreeSelect v-model="form.category_id" :nodes="categories" :invalid="!!form.errors.category_id" @change="loadFilters" />
                <InputError class="mt-2" :message="form.errors.category_id" />
            </div>

            <div v-if="!currency" class="rounded-md bg-yellow-50 px-3 py-2 text-sm text-yellow-800">
                {{ $t('Для страны текущего региона не настроена валюта.') }}
            </div>
            <InputError :message="errors.currency" />

            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <InputLabel for="price" :value="$t('Цена')" required :invalid="!!form.errors.price" />
                    <div class="mt-1 flex items-center gap-2">
                        <TextInput id="price" v-model="form.price" type="number" min="0" :step="priceStep" class="block w-full" :placeholder="$t('Например, 12')" :invalid="!!form.errors.price" />
                        <span class="shrink-0 text-sm font-medium text-gray-700">{{ currency ?? '—' }}</span>
                    </div>
                    <p :class="hint">{{ $t('Цена отображается в валюте текущего региона.') }}</p>
                    <InputError class="mt-2" :message="form.errors.price" />
                </div>

                <div>
                    <InputLabel for="warranty_value" :value="$t('Срок гарантии')" :invalid="!!(form.errors.warranty_value || form.errors.warranty_unit)" />
                    <div class="mt-1 flex gap-2">
                        <TextInput id="warranty_value" v-model="form.warranty_value" type="number" min="1" max="1000" class="block w-full" :placeholder="$t('Например, 12')" :invalid="!!(form.errors.warranty_value || form.errors.warranty_unit)" />
                        <select
                            v-model="form.warranty_unit"
                            class="w-40 rounded-full border shadow-xs"
                            :class="(form.errors.warranty_value || form.errors.warranty_unit) ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-green-first focus:ring-green-first'"
                        >
                            <option v-for="unit in warrantyUnits" :key="unit.value" :value="unit.value">{{ unit.label }}</option>
                        </select>
                    </div>
                    <InputError class="mt-2" :message="form.errors.warranty_value ?? form.errors.warranty_unit" />
                </div>
            </div>



            <div>
                <InputLabel for="description" :value="$t('Описание')" :invalid="!!form.errors.description" />
                <WysiwygEditor
                    v-model="form.description"
                    profile="company"
                    height-key="account-wysiwyg"
                    :invalid="!!errors.description"
                    resizable
                />
                <p :class="hint">{{ descriptionLength }} / 15000</p>
                <InputError class="mt-2" :message="form.errors.description" />
            </div>
        </section>

        <section :class="formCard">
            <div>
                <h2 :class="[formTitle, errors.images ? 'text-red-600' : 'text-gray-900']">{{ $t('Изображения') }}</h2>
                <p :class="hint">{{ $t('До 15 изображений. Перетащите, чтобы изменить порядок.') }}</p>
            </div>

            <div class="rounded-xl border border-gray-200 bg-gray-50/60 p-4">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end">
                    <div class="min-w-0 flex-1">
                        <InputLabel for="image_url" :value="$t('Ссылка на изображение')" />
                        <TextInput
                            id="image_url"
                            v-model="imageUrl"
                            type="url"
                            class="mt-1 block w-full"
                            placeholder="https://example.com/image.jpg"
                            autocomplete="off"
                            @keydown.enter.prevent="uploadUrl"
                            @paste="onUrlPaste"
                        />
                    </div>
                    <button
                        type="button"
                        class="inline-flex h-10 shrink-0 items-center justify-center gap-2 rounded-full bg-green-first px-4 text-sm font-semibold text-white shadow-sm transition hover:bg-green-700 disabled:cursor-not-allowed disabled:opacity-50"
                        :disabled="urlUploading || imageUrl.trim() === ''"
                        @click="uploadUrl"
                    >
                        <svg v-if="urlUploading" class="h-4 w-4 animate-spin" viewBox="0 0 24 24" fill="none">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4" />
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 0 1 8-8V0C5.373 0 0 5.373 0 12h4Z" />
                        </svg>
                        {{ urlUploading ? $t('Загрузка...') : $t('Загрузить по ссылке') }}
                    </button>
                </div>

                <p v-if="urlError" class="mt-2 text-sm text-red-600">{{ urlError }}</p>
            </div>

            <p class="text-xs font-medium text-amber-700">{{ $t('Не воруйте изображения, это грех.') }}</p>

            <input ref="fileInput" type="file" accept="image/*" multiple class="hidden" @change="onFileInput" />

            <div :class="[dropzone, errors.images ? 'border-red-500' : '']" @click="openFileDialog" @dragover.prevent @drop.prevent="onDropzone">
                <span class="mb-2 flex h-10 w-10 items-center justify-center rounded-full bg-white text-green-700 shadow-xs ring-1 ring-gray-200">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.8" stroke="currentColor" class="h-5 w-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5m-13.5-9L12 3m0 0 4.5 4.5M12 3v13.5" />
                    </svg>
                </span>
                <p class="font-medium text-gray-700">{{ $t('Перетащите изображения сюда или нажмите, чтобы выбрать') }}</p>
                <p class="mt-1 text-xs text-gray-500">{{ $t('До 15 изображений. Перетащите, чтобы изменить порядок.') }}</p>
            </div>

            <p v-if="uploadError" class="text-sm text-red-600">{{ uploadError }}</p>
            <InputError :message="errors.images" />

            <p v-if="images.length === 0" :class="hint">{{ $t('Нет изображений.') }}</p>

            <div v-else class="grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="(image, index) in images"
                    :key="image.key"
                    class="group relative"
                    @dragenter.prevent="dragEnter(index)"
                    @dragover.prevent
                    @drop.prevent="dropAt(index)"
                >
                    <div
                        v-if="dropTargetIndex === index && dragIndex !== null && dragIndex !== index"
                        class="pointer-events-none absolute inset-0 z-0 rounded-lg border-2 border-dashed border-green-first bg-green-50/60"
                    ></div>

                    <div
                        draggable="true"
                        class="relative z-10 overflow-hidden rounded-lg border border-gray-200 bg-gray-50 transition-transform"
                        :class="[
                            dragIndex === index ? 'cursor-grabbing opacity-40' : 'cursor-grab',
                            dropTargetIndex === index && dragIndex !== index ? 'translate-x-2 translate-y-2' : '',
                        ]"
                        @dragstart="startDrag(index, $event)"
                        @dragend="dragEnd"
                    >
                        <img :src="image.thumb630Url ?? image.url" :alt="`${$t('Изображения')} ${index + 1}`" class="aspect-[630/467] h-auto w-full object-cover" />

                        <div v-if="image.uploading" class="absolute inset-0 flex flex-col items-center justify-center bg-black/50 px-4 text-white">
                            <span class="text-sm">{{ $t('Загрузка...') }} {{ image.progress }}%</span>
                            <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-white/30">
                                <div class="h-full bg-white transition-all" :style="{ width: `${image.progress}%` }"></div>
                            </div>
                        </div>

                        <p v-if="image.error" class="absolute inset-x-0 bottom-0 bg-red-600/90 px-2 py-1 text-xs text-white">{{ image.error }}</p>

                        <div class="absolute left-2 top-2 rounded bg-black/60 px-1.5 py-0.5 text-xs text-white">{{ index + 1 }}</div>

                        <div v-if="!image.uploading" class="absolute right-2 top-2 flex gap-1">
                            <button type="button" :class="[smallButton, 'cursor-pointer bg-white/90']" :title="$t('Переместить влево')" @click="moveImage(index, index - 1)">←</button>
                            <button type="button" :class="[smallButton, 'cursor-pointer bg-white/90']" :title="$t('Переместить вправо')" @click="moveImage(index, index + 1)">→</button>
                            <button type="button" :class="[smallButton, 'cursor-pointer bg-white/90 text-red-600']" :title="$t('Удалить')" @click="removeImage(index)">×</button>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <section :class="formCard">
            <div>
                <h2 :class="[formTitle, 'text-gray-900']">{{ $t('Видео') }}</h2>
                <p :class="hint">{{ $t('Ссылка на YouTube') }}</p>
            </div>

            <div>
                <InputLabel for="video" :value="$t('Ссылка на YouTube')" :invalid="!!form.errors.video" />
                <TextInput id="video" v-model="form.video" type="url" class="mt-1 block w-full" :placeholder="$t('Например, https://youtube.com/watch?v=...')" :invalid="!!form.errors.video" />
                <InputError class="mt-2" :message="form.errors.video" />

                <div v-if="youtubeEmbedUrl" class="mt-4 overflow-hidden rounded-xl border border-gray-200 bg-black shadow-sm">
                    <div class="aspect-video">
                        <iframe
                            :src="youtubeEmbedUrl"
                            class="h-full w-full"
                            :title="$t('Предпросмотр видео')"
                            loading="lazy"
                            allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin"
                            allowfullscreen
                        ></iframe>
                    </div>
                </div>
            </div>
        </section>

        <section :class="formCard">
            <div>
                <h2 :class="[formTitle, form.errors.regions ? 'text-red-600' : 'text-gray-900']">{{ $t('Регионы') }} <span class="text-red-600">*</span></h2>
                <p :class="hint">{{ $t('Выберите регионы, в которых показывать товар/услугу.') }}</p>
            </div>

            <AdRegionTree v-model="form.regions" :nodes="regions" :invalid="!!form.errors.regions" />
            <InputError :message="form.errors.regions" />
        </section>

        <section :class="formCard">
            <div>
                <h2 :class="[formTitle, form.errors.filters ? 'text-red-600' : 'text-gray-900']">{{ $t('Параметры фильтров') }}</h2>
            </div>

            <p v-if="!form.category_id" :class="hint">{{ $t('Выберите категорию, чтобы загрузить фильтры.') }}</p>
            <p v-else-if="filtersLoading" :class="hint">{{ $t('Загрузка фильтров...') }}</p>
            <p v-else-if="filtersError" class="text-sm text-red-600">{{ filtersError }}</p>
            <p v-else-if="filters.length === 0" :class="hint">{{ $t('Нет доступных фильтров.') }}</p>

            <div v-else class="space-y-4">
                <div v-for="filter in filters" :key="filter.id">
                    <div class="mb-2 text-sm font-medium" :class="filterError(filter.id) ? 'text-red-600' : 'text-gray-800'">
                        {{ filter.name }}
                        <span v-if="filter.unit" class="text-gray-500">({{ filter.unit }})</span>
                    </div>

                    <div v-if="filter.type === 'checkbox'" class="flex flex-wrap gap-4">
                        <label v-for="variant in filter.variants" :key="variant.id" class="flex items-center gap-2 text-sm text-gray-700">
                            <input
                                type="checkbox"
                                :checked="isVariantChecked(filter.id, variant.id)"
                                class="rounded-sm text-green-first shadow-xs focus:ring-green-first"
                                :class="filterError(filter.id) ? 'border-red-500' : 'border-gray-300'"
                                @change="toggleVariant(filter.id, variant.id, $event)"
                            />
                            {{ variant.name }}
                        </label>
                    </div>

                    <div v-else-if="filter.type === 'radio'" class="flex flex-wrap gap-4">
                        <label v-for="variant in filter.variants" :key="variant.id" class="flex items-center gap-2 text-sm text-gray-700">
                            <input
                                type="radio"
                                :name="`filter_${filter.id}`"
                                :checked="Number(form.filters[String(filter.id)]) === variant.id"
                                class="text-green-first shadow-xs focus:ring-green-first"
                                :class="filterError(filter.id) ? 'border-red-500' : 'border-gray-300'"
                                @change="setRadio(filter.id, variant.id)"
                            />
                            {{ variant.name }}
                        </label>
                    </div>

                    <input
                        v-else-if="filter.type === 'input_int'"
                        :value="String(form.filters[String(filter.id)] ?? '')"
                        type="number"
                        step="1"
                        class="block w-full rounded-full border shadow-xs md:w-64"
                        :class="filterError(filter.id) ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-green-first focus:ring-green-first'"
                        @input="setFilterValue(filter.id, $event)"
                    />

                    <input
                        v-else
                        :value="String(form.filters[String(filter.id)] ?? '')"
                        type="number"
                        :step="String(1 / 10 ** (filter.digits_after_point ?? 2))"
                        class="block w-full rounded-full border shadow-xs md:w-64"
                        :class="filterError(filter.id) ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-green-first focus:ring-green-first'"
                        @input="setFilterValue(filter.id, $event)"
                    />

                    <InputError class="mt-1" :message="filterError(filter.id)" />
                </div>
            </div>

            <InputError :message="form.errors.filters" />
        </section>

        <section :class="formCard">
            <div>
                <h2 :class="[formTitle, 'text-gray-900']">{{ $t('Публикация') }}</h2>
                <InputLabel for="is_active" :value="$t('Отображать на сайте')" required :invalid="!!form.errors.is_active" class="mt-4" />
                <select
                    id="is_active"
                    v-model="form.is_active"
                    class="mt-1 block w-full rounded-full border shadow-xs md:w-96"
                    :class="form.errors.is_active ? 'border-red-500 focus:border-red-500 focus:ring-red-500' : 'border-gray-300 focus:border-green-first focus:ring-green-first'"
                >
                    <option :value="true">{{ $t('Отображать на сайте') }}</option>
                    <option :value="false">{{ $t('Не отображать на сайте') }}</option>
                </select>
                <InputError class="mt-2" :message="form.errors.is_active" />
            </div>
        </section>

        <AccountFormActions
            :processing="form.processing"
            :disabled="isUploading || filtersLoading"
            :success="form.recentlySuccessful"
        >
            <template #actions>
                <Link
                    v-if="copyUrl"
                    :href="copyUrl"
                    class="inline-flex items-center justify-center rounded-full border border-green-200 bg-green-50 px-4 py-2.5 text-sm font-medium text-green-800 transition hover:bg-green-100"
                >
                    {{ $t('Добавить похожее') }}
                </Link>
                <Link
                    :href="route('account.ads.index')"
                    class="inline-flex items-center justify-center rounded-full border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium text-gray-600 transition hover:border-gray-300 hover:text-gray-900"
                >
                    {{ $t('К списку') }}
                </Link>
            </template>
        </AccountFormActions>
        </form>

    </div>
</template>
