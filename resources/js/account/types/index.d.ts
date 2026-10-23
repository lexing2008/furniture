import { Config } from 'ziggy-js';

export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
    role?: 'admin' | 'manager' | 'seo' | 'customer' | null;
}

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    app_name: string;
    auth: {
        user: User;
    };
    ziggy: Config & { location: string };
    flash?: {
        success?: string | null;
        error?: string | null;
        // "verification-link-sent" after the letter was sent again
        status?: string | null;
    };
};
