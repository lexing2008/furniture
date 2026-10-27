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
    locale: string;
    // Region of the company: the site the "site name" link in the sidebar points to
    company_region?: {
        url: string;
        name: string;
    } | null;
    auth: {
        user: User;
    };
    ad_counts: {
        all: number;
        active: number;
        disabled: number;
        moderation: number;
        banned: number;
    };
    ziggy: Config & { location: string };
    flash?: {
        success?: string | null;
        error?: string | null;
        // "verification-link-sent" after the letter was sent again
        status?: string | null;
    };
};
