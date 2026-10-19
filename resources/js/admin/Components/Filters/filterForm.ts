import type { SeoTemplateOption } from '@/admin/Components/Categories/categoryForm';

export type { SeoTemplateOption };

export type FilterTypeValue = 'checkbox' | 'radio' | 'input_int' | 'input_float';

export interface FilterTypeOption {
    value: FilterTypeValue;
    label: string;
    has_variants: boolean;
}

export interface CategoryInfo {
    id: number;
    name: string;
}

export interface VariantData {
    // null for a variant that is not saved yet
    id: number | null;
    name_in_filter: string;
    name_full: string;
    name_buy: string;
    slug: string;
    // Template for all languages of the variant
    seo_template_id: number | null;
    // Template of the Russian translation, it wins over the one above
    translation_seo_template_id: number | null;
    is_active: boolean;
}

export interface FilterFormData {
    type: FilterTypeValue;
    digits_after_point: number | null;
    is_active: boolean;
    name: string;
    unit: string;
    variants: VariantData[];
}

export const emptyVariant = (): VariantData => ({
    id: null,
    name_in_filter: '',
    name_full: '',
    name_buy: '',
    slug: '',
    seo_template_id: null,
    translation_seo_template_id: null,
    is_active: true,
});

export interface TranslationVariantData {
    id: number;
    // Base language (ru) values, shown as a hint
    base: {
        name_in_filter: string;
        name_full: string;
        name_buy: string;
        slug: string;
    };
    name_in_filter: string;
    name_full: string;
    name_buy: string;
    slug: string;
    seo_template_id: number | null;
    is_active: boolean;
}

export interface TranslationFormData {
    name: string;
    unit: string;
    is_active: boolean;
    variants: TranslationVariantData[];
}
