export interface Language {
    id: number;
    code: string;
    name: string;
}

export interface ParentOption {
    id: number;
    name: string;
    depth: number;
}

export interface SeoTemplateOption {
    id: number;
    name: string;
    // Language codes the template is translated to
    languages: string[];
}

export interface TranslationData {
    lang: string;
    name: string;
    slug: string;
    seo_template_id: number | null;
    seo_title: string;
    seo_description: string;
    seo_block: string;
}

export interface CategoryFormData {
    parent_id: number | null;
    seo_template_id: number | null;
    is_active: boolean;
    translations: TranslationData[];
}

export type ExistingTranslation = Partial<Record<keyof TranslationData, string | number | null>>;

/**
 * One translation block per language, prefilled from existing data.
 */
export const makeTranslations = (
    languages: Language[],
    existing: Record<string, ExistingTranslation> = {},
): TranslationData[] =>
    languages.map((language) => ({
        lang: language.code,
        name: String(existing[language.code]?.name ?? ''),
        slug: String(existing[language.code]?.slug ?? ''),
        seo_template_id: existing[language.code]?.seo_template_id ? Number(existing[language.code]?.seo_template_id) : null,
        seo_title: String(existing[language.code]?.seo_title ?? ''),
        seo_description: String(existing[language.code]?.seo_description ?? ''),
        seo_block: String(existing[language.code]?.seo_block ?? ''),
    }));
