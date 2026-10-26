import en from '../../../lang/en.json';
import ru from '../../../lang/ru.json';

type Locale = 'ru' | 'en';

const dictionaries: Record<Locale, Record<string, string>> = { ru, en };

let currentLocale: Locale =
    typeof document !== 'undefined' && document.documentElement.lang.toLowerCase().startsWith('en')
        ? 'en'
        : 'ru';

export function setLocale(locale: string): void {
    currentLocale = locale === 'en' ? 'en' : 'ru';
}

export function isEnglish(): boolean {
    return currentLocale === 'en';
}

/**
 * Translation from the shared lang/*.json files. The key is the Russian source phrase,
 * so the same files are used by Laravel and by Vue.
 */
export function translate(key: string, replacements: Record<string, string | number> = {}): string {
    let text = dictionaries[currentLocale][key] ?? key;

    for (const [name, value] of Object.entries(replacements)) {
        text = text.split(`:${name}`).join(String(value));
    }

    return text;
}
