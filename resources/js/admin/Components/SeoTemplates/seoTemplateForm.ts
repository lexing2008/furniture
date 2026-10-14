import type { Language } from '@/admin/Components/Categories/categoryForm';

export interface SeoTemplateTranslationData {
    lang: string;
    name: string;
    seo_title: string;
    seo_description: string;
    seo_block: string;
}

export interface SeoTemplateFormData {
    name: string;
    translations: SeoTemplateTranslationData[];
}

export type ExistingSeoTemplateTranslation = Partial<Record<keyof SeoTemplateTranslationData, string | number | null>>;

/**
 * One translation block per language, prefilled from existing data.
 */
export const makeTranslations = (
    languages: Language[],
    existing: Record<string, ExistingSeoTemplateTranslation> = {},
): SeoTemplateTranslationData[] =>
    languages.map((language) => ({
        lang: language.code,
        name: String(existing[language.code]?.name ?? ''),
        seo_title: String(existing[language.code]?.seo_title ?? ''),
        seo_description: String(existing[language.code]?.seo_description ?? ''),
        seo_block: String(existing[language.code]?.seo_block ?? ''),
    }));
