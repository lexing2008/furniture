import type { InjectionKey } from 'vue';

export interface CategoryTreeState {
    isExpanded: (id: number) => boolean;
    isSelected: (id: number) => boolean;
    toggleExpanded: (id: number) => void;
    toggleSelected: (id: number) => void;
}

export const categoryTreeKey: InjectionKey<CategoryTreeState> = Symbol('categoryTree');
