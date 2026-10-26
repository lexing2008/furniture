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

export interface TimezoneOption {
    value: string;
    offset: number;
    label: string;
}

export interface RegionFormData {
    parent_id: number | null;
    name: string;
    name_en: string;
    name_in: string;
    subdomain: string;
    lang: string;
    timezone: string;
    is_capital: boolean;
    seo_title: string;
    seo_description: string;
    seo_block: string;
    is_active: boolean;
}

export type ExistingRegion = Partial<Record<keyof RegionFormData, string | number | boolean | null>>;

export const makeRegionForm = (region: ExistingRegion = {}): RegionFormData => ({
    parent_id: region.parent_id ? Number(region.parent_id) : null,
    name: String(region.name ?? ''),
    name_en: String(region.name_en ?? ''),
    name_in: String(region.name_in ?? ''),
    subdomain: String(region.subdomain ?? ''),
    lang: String(region.lang ?? ''),
    timezone: String(region.timezone ?? ''),
    is_capital: region.is_capital === undefined ? false : Boolean(region.is_capital),
    seo_title: String(region.seo_title ?? ''),
    seo_description: String(region.seo_description ?? ''),
    seo_block: String(region.seo_block ?? ''),
    is_active: region.is_active === undefined ? true : Boolean(region.is_active),
});
