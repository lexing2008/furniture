export interface PhoneData {
    code: string;
    number: string;
    description: string;
}

/** The company itself: the "About company" page of the account */
export interface CompanyInfoFields {
    region_id: number | null;
    legal_name: string;
    tax_id: string;
    display_name: string;
    website: string;
    is_online_store: boolean;
}

export interface CompanyDescriptionFields {
    description: string;
}

/** The "About company" page of the account */
export interface CompanyAboutFields extends CompanyInfoFields, CompanyDescriptionFields {
    categories: number[];
}

/** Fields of a company that the registration and the account have in common */
export interface CompanyFormFields extends CompanyAboutFields {
    phones: PhoneData[];
}

export interface CompanyContactsFields {
    email: string;
    phones: PhoneData[];
}

export interface CompanySocialsFields {
    socials: Record<string, string>;
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
