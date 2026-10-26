# CLAUDE.md

> **Единый источник контекста проекта — `AGENTS.md`.**
> Перед любой задачей прочитай:
>
> 1. `AGENTS.md`;
> 2. `docs/ARCHITECTURE.md`;
> 3. последние записи в `docs/WORKLOG.md`.

## Кратко о проекте

Мебельный интернет-торговый центр:

- публичный сайт на `{region}.furniture.lex`;
- кабинет компании `/account`;
- админка `/admin`;
- товары/услуги, изображения, фильтры, регионы.

Стек: Laravel 13, PHP 8.4, Inertia 2, Vue 3 + TypeScript, Tailwind 4, Vite 8, MySQL.

## Критично для запуска

PHP 8.4 находится **только внутри Docker-контейнера** `lamp-apachephp-1`.
Host PHP 8.3 для artisan не использовать.

```bash
docker exec -w /var/www/html lamp-apachephp-1 php artisan test
docker exec -w /var/www/html lamp-apachephp-1 php artisan migrate
docker exec -w /var/www/html lamp-apachephp-1 php artisan view:clear
docker exec -w /var/www/html lamp-apachephp-1 ./vendor/bin/pint <files>

npx vue-tsc --noEmit
npm run build
```

После новых файлов:

```bash
git ls-files --others --exclude-standard -z | xargs -0 chmod 664
```

## Главные правила

- Не откатывать незакоммиченные изменения.
- Не изменять реальные данные пользователя без необходимости.
- Новые пользовательские фразы добавлять в `lang/ru.json` **и** `lang/en.json`.
- Язык сайта/кабинета — из страны текущего региона; админка остаётся русской.
- HTML очищается через `PurifiedHtml`; WYSIWYG не является границей безопасности.
- После Blade-правок — `php artisan view:clear`.
- Тесты, `vue-tsc` и `npm run build` должны проходить.

## Где смотреть детали

- `AGENTS.md` — правила, команды, домены, текущий baseline;
- `docs/ARCHITECTURE.md` — устройство backend/frontend и потоков данных;
- `docs/WORKLOG.md` — что уже сделано и чем проверялось.
