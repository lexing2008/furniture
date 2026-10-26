import type { InjectionKey } from 'vue';

export interface CategoryTreeSelectState {
    name: string;
    isExpanded: (id: number) => boolean;
    isSelected: (id: number) => boolean;
    toggleExpanded: (id: number) => void;
    select: (id: number) => void;
}

export const categoryTreeSelectKey: InjectionKey<CategoryTreeSelectState> = Symbol('categoryTreeSelect');
