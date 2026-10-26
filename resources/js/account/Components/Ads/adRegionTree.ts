import type { InjectionKey } from 'vue';
import type { RegionNode } from '@/account/Components/Company/companyForm';

export interface AdRegionTreeState {
    isExpanded: (id: number) => boolean;
    isSelected: (id: number) => boolean;
    isAllSelected: (node: RegionNode) => boolean;
    toggleExpanded: (id: number) => void;
    toggleSelected: (id: number) => void;
    toggleAll: (node: RegionNode) => void;
}

export const adRegionTreeKey: InjectionKey<AdRegionTreeState> = Symbol('adRegionTree');
