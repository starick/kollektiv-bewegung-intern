import { App } from 'vue';
import { usePage } from '@inertiajs/vue3';
import { Dictionary } from '@/Types/dictionary';

type Replacements = Dictionary<string | number>;

// Translates an English source string via the JSON translations shared by Laravel.
// Placeholders follow Laravel's convention: ':name', ':Name' and ':NAME'.
export function trans(key: string, replacements: Replacements = {}): string {
  const translations = (usePage()?.props.translations ?? {}) as Dictionary<string>;
  let text = translations[key] ?? key;

  Object.entries(replacements)
    .sort(([a], [b]) => b.length - a.length)
    .forEach(([name, value]) => {
      const v = String(value);
      text = text
        .replaceAll(`:${name.toUpperCase()}`, v.toUpperCase())
        .replaceAll(
          `:${name.charAt(0).toUpperCase()}${name.slice(1)}`,
          v.charAt(0).toUpperCase() + v.slice(1)
        )
        .replaceAll(`:${name}`, v);
    });

  return text;
}

export function currentLocale(): string {
  return (usePage()?.props.locale as string | undefined) ?? 'de';
}

export const TranslationPlugin = {
  install(app: App) {
    app.config.globalProperties.$t = trans;
  }
};

declare module 'vue' {
  interface ComponentCustomProperties {
    $t: typeof trans;
  }
}
