import type { CategoryNode, RegionNode } from '@/account/Components/Company/companyForm';

export type WarrantyUnit = 'days' | 'months' | 'years';

export type FilterType = 'checkbox' | 'radio' | 'input_int' | 'input_float';

export interface AdFilterVariant {
    id: number;
    name: string;
}

export interface AdFilter {
    id: number;
    name: string;
    unit: string | null;
    type: FilterType;
    digits_after_point: number | null;
    variants: AdFilterVariant[];
    value: string | number | number[] | null;
}

export interface AdImageData {
    id: number;
    url: string;
    thumb_630_url: string;
    thumb_100_url: string;
    order: number;
}

export interface AdData {
    id: number;
    title: string;
    number: string;
    article: string;
    category_id: number;
    description: string;
    price: string;
    warranty_value: number | null;
    warranty_unit: WarrantyUnit;
    video: string;
    is_active: boolean;
    regions: number[];
    images: AdImageData[];
}

export interface AdCopySource {
    category_id: number;
    price: string;
    warranty_value: number | null;
    warranty_unit: WarrantyUnit;
    description: string;
    regions: number[];
}

export interface AdImageItem {
    key: string;
    id?: number;
    tempFileId?: number;
    url: string;
    thumb630Url?: string;
    thumb100Url?: string;
    progress: number;
    uploading: boolean;
    error?: string;
}

export type FilterValue = string | number | number[] | null;

export interface AdFormData {
    title: string;
    number: string;
    article: string;
    category_id: number | null;
    description: string;
    price: string;
    warranty_value: string;
    warranty_unit: WarrantyUnit;
    video: string;
    is_active: boolean;
    regions: number[];
    filters: Record<string, FilterValue>;
    existing_images: Array<{ id: number; order: number }>;
    new_images: Array<{ temp_file_id: number; order: number }>;
    deleted_image_ids: number[];
}

export const emptyAdForm = (defaultRegionId: number | null = null): AdFormData => ({
    title: '',
    number: '',
    article: '',
    category_id: null,
    description: '',
    price: '',
    warranty_value: '',
    warranty_unit: 'months',
    video: '',
    is_active: true,
    regions: defaultRegionId === null ? [] : [defaultRegionId],
    filters: {},
    existing_images: [],
    new_images: [],
    deleted_image_ids: [],
});

export const toCategoryNodes = (nodes: RegionNode[]): CategoryNode[] =>
    nodes.map((node) => ({
        id: node.id,
        name: node.name,
        selectable: node.selectable,
        children: toCategoryNodes(node.children),
    }));
