export interface PhoneData {
    code: string;
    number: string;
    description: string;
}

/** Fields of a company that the registration and the account have in common */
export interface CompanyFormFields {
    region_id: number | null;
    legal_name: string;
    tax_id: string;
    display_name: string;
    description: string;
    is_online_store: boolean;
    website: string;
    phones: PhoneData[];
    categories: number[];
}

export interface CategoryNode {
    id: number;
    name: string;
    // Only a category without subcategories can be chosen
    selectable: boolean;
    children: CategoryNode[];
}

export interface RegionGroup {
    label: string;
    cities: { id: number; name: string }[];
}

export const emptyPhone = (): PhoneData => ({ code: '', number: '', description: '' });
