import type { InjectionKey } from 'vue';

export interface RegionTreeState {
    name: string;
    isExpanded: (id: number) => boolean;
    isSelected: (id: number) => boolean;
    toggleExpanded: (id: number) => void;
    select: (id: number) => void;
}

export const regionTreeKey: InjectionKey<RegionTreeState> = Symbol('regionTree');
