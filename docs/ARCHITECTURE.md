# Архитектура проекта

> Дополнение к `AGENTS.md`. Здесь описано, как устроен проект и где искать код.
> При изменении архитектуры обновляй этот файл и `docs/WORKLOG.md`.

## 1. Общая схема

```text
Браузер
  │
  ├── {region}.furniture.lex/            публичный сайт региона
  ├── {region}.furniture.lex/account     кабинет компании
  └── /admin                             админка сотрудников

Laravel
  ├── routes/web.php         домены регионов, сайт, кабинет
  ├── routes/admin.php       админка
  ├── Middleware             локаль, роли, Inertia
  ├── Controllers            Account / Admin / RegionPage
  ├── Models                 регионы, компании, товары, фильтры
  └── Inertia + Vue          account и admin приложения
```

## 2. Жизненный цикл запроса

1. Запрос приходит на `{region}.furniture.lex`.
2. Web-middleware `ExpireStaleSessionCookies` и `SetLocaleFromRegion`.
3. `SetLocaleFromRegion`:
   - пропускает `/admin`;
   - находит регион по хосту;
   - берёт `Region::root()->lang`;
   - ставит `ru`/`en`; для неподдерживаемого языка — `fallback_lang` страны, затем `en`.
4. Route middleware:
   - `guest` — регистрация/вход;
   - `auth` + `role:customer` — кабинет;
   - `auth` + employee-роль — админка.
5. Inertia middleware:
   - `HandleInertiaAccountRequests` — root view `account`, shared props;
   - `HandleInertiaAdminRequests` — root view `admin`;
   - для гостевых страниц кабинета используется root view `guest`.
6. Контроллер возвращает `Inertia::render(...)`.
7. Vue-страница рендерится в соответствующем Blade-каркасе.

### Shared props кабинета

`HandleInertiaAccountRequests` передаёт:

- `app_name`;
- `locale`;
- `company_region` (url и локализованное имя);
- `auth.user`;
- `flash.success/error/status`;
- `ziggy`.

## 3. Регионы и домены

Иерархия: **страна → область → город**, максимум 3 уровня.

- Страна: `parent_id = null`.
- Область: `parent_id` страны.
- Город: `parent_id` области.
- `Region::MAX_DEPTH = 2`.

Ключевые методы `Region`:

- `fromHost($host)` — регион по поддомену;
- `root()` — страна;
- `language()` — язык страны;
- `fallbackLanguage()` — резервный язык страны;
- `cityTree(?int $countryId)` — дерево городов;
- `cityOptions()` — плоский список городов;
- `isCity()`, `isAvailable()`, `url()`, `seo()`;
- `countryData()` — `RegionCountry` (телефон, валюта, decimals).

`AppServiceProvider`:

- настраивает route binding `{region}` по `subdomain`;
- добавляет view composer для `layouts.site`, `region.*`, `guest`;
- передаёт `region`, `country`, `areas`, `language`.

Публичные страницы:

- `RegionPageController@show`;
- для страны — `region.country.show`;
- для области — `region.region.show`;
- для города — `region.show`.

## 4. Роли

`App\Enums\Roles`:

- `admin`, `manager`, `seo` — сотрудники;
- `customer` — клиент/владелец компании.

`User::homePath()`:

- сотрудник → `/admin`;
- клиент → `/account`.

## 5. Backend: кабинет

Контроллеры — `app/Http/Controllers/Account`:

- `CompanyController` — главная;
- `CompanyGeneralController` — данные компании;
- `CompanyContactsController` — email и телефоны;
- `CompanySocialsController` — соцсети;
- `CompanyScheduleController` — график и исключения;
- `CompanyDeliveryController` — доставка;
- `CompanyPaymentController` — оплата;
- `PasswordController` — смена пароля;
- `AdController` — товары/услуги;
- `AdImageController` — загрузка/удаление изображений;
- `Auth/*` — вход, регистрация, сброс пароля;
- `Concerns/ResolvesCompany` — получение компании текущего пользователя.

Маршруты кабинета находятся в `routes/web.php` (файл `routes/account.php` пустой).

## 6. Backend: админка

Контроллеры — `app/Http/Controllers/Admin`:

- языки;
- категории;
- фильтры и переводы фильтров;
- регионы;
- страны (`region_countries`);
- SEO-шаблоны;
- пользователи;
- профиль;
- auth.

Админка использует отдельный Inertia-entrypoint и отдельные Vue-страницы.
Админка не локализуется и остаётся русской.

## 7. Frontend

### Entrypoints

- `resources/js/account/app.ts` — клиент кабинета;
- `resources/js/account/ssr.ts` — SSR кабинета;
- `resources/js/admin/app.ts` — клиент админки;
- `resources/js/admin/ssr.ts` — SSR админки.

Фактический Vite-конфиг — `vite.config.js`.
Алиасы: `@account`, `@admin`, `ziggy-js`.

### Кабинет

- `Layouts/AccountLayout.vue` — каркас и flash-сообщения;
- `Components/Sidebar/*` — зелёный sidebar;
- `Pages/Company/*` — разделы компании;
- `Pages/Ads/*` — товары/услуги;
- `Pages/Password/Edit.vue`;
- `Pages/Auth/*` — гостевые страницы.

### Общие компоненты

- `resources/js/common/Components/WysiwygEditor.vue`;
- `resources/js/common/i18n.ts`;
- `resources/js/account/Components/Ads/*`.

### i18n

- `lang/ru.json`, `lang/en.json` — единый словарь backend + frontend;
- `$t` регистрируется глобально в `account` и `admin`;
- `setLocale()` берёт локаль из Inertia props;
- в Blade используется `__()`.

## 8. Локализация

- Источник языка — страна текущего региона (`regions.lang`).
- Если язык не задан или не поддержан, используется `regions.fallback_lang`, затем `en`; без обоих — `APP_LOCALE=ru`.
- `SetLocaleFromRegion` ставит локаль до контроллера.
- `layouts/site.blade.php` получает `$language` из view composer.
- `account.blade.php`, `guest.blade.php`, `admin.blade.php` используют `app()->getLocale()`.
- Админка не локализуется.
- Стандартные ошибки Laravel — `lang/ru/validation.php`.
- Новые фразы добавляются в оба JSON.

## 9. Данные

### Основные таблицы

- `users` — пользователи и роль;
- `regions` — страна/область/город;
- `region_countries` — телефонный код, валюта, decimals;
- `regions.fallback_lang` — необязательный резервный язык страны;
- `languages` — коды языков;
- `companies` — компания, регион, страна, контент;
- `company_phones`;
- `category_company`;
- `company_schedules`;
- `company_schedule_exceptions`;
- `categories`, `category_translations`;
- `filters`, `filter_trans`;
- `filter_variants`, `filter_variant_trans`;
- `seo_templates`, `seo_template_trans`;
- `ads`, `ad_images`, `ad_region`;
- `tmp_files`;
- `adv_filter_ints`, `adv_filter_floats`, `adv_filter_variants`.

### Компания

`Company`:

- `user_id`;
- `region_id` — город компании;
- `country_id` — страна компании;
- `legal_name`, `tax_id`, `display_name`;
- `description`, `delivery`, `payment` — PurifiedHtml;
- `is_online_store`, `website`, `email`, `socials`;
- связи: телефоны, категории, график, исключения, товары.

### Категории и фильтры

`Category`:

- дерево `parent_id`;
- переводы `category_translations` (name, menu_name, slug);
- `selectableTree($lang)` — только листья;
- `slugIn($lang)` — slug для имён файлов.

`Filter`:

- `category_id`;
- `type`: `checkbox`, `radio`, `input_int`, `input_float`;
- `digits_after_point` — для float;
- `order`, `is_active`;
- переводы и варианты.

## 10. Товары/услуги

### Модели

- `Ad`;
- `AdImage`;
- `TmpFile`;
- `AdvFilterInt`, `AdvFilterFloat`, `AdvFilterVariant`.

### Создание/редактирование

- `AdController@create/store/edit/update`;
- `AdRequest` — валидация;
- `AdFilterService` — фильтры категории и значения товара;
- `AdImageService` — загрузка, WebP, миниатюры, переименование;
- `AdImageController` — upload/delete temp.

### Поля `ads`

- `company_id`, `category_id`;
- `title`;
- `number` — nullable, unique в рамках компании;
- `article` — nullable;
- `video`;
- `description` — PurifiedHtml;
- `price` decimal, `currency`;
- `warranty_value`, `warranty_unit`;
- `is_moderated` (default `false`);
- `is_active` (default `true`);
- `is_banned` (default `false`);
- timestamps.

### Фильтры

- при смене категории `GET /account/ads/filters?category_id=...`;
- `AdFilterService` возвращает:
  - `id`, `name`, `unit`, `type`, `digits_after_point`;
  - `variants` для checkbox/radio;
  - `value` для редактирования.
- backend сохраняет значения в:
  - `adv_filter_ints`;
  - `adv_filter_floats`;
  - `adv_filter_variants`.

### Регионы

- many-to-many `ad_region`;
- в форме — `AdRegionTree`, множественный выбор;
- у страны и области — «Выбрать все» / «Снять все»;
- backend проверяет, что города принадлежат стране компании.

### Изображения

Загрузка:

1. Фронт отправляет файл на `POST /account/ads/images`.
2. `AdImageService` сохраняет:
   - main WebP до 1200×1200, quality 92;
   - 630×467, quality 80;
   - 100×100, quality 75.
3. Создаётся `tmp_files` с `type=ad_image`, `uuid`, `path`.
4. Фронт получает `id`, `url`, `thumb_630_url`, `thumb_100_url`.

Сохранение товара:

1. `AdRequest` проверяет `existing_images`, `new_images`, `deleted_image_ids`.
2. Проверяется владелец temp-файлов.
3. `AdImageService::sync`:
   - удаляет помеченные изображения;
   - переименовывает existing/new в `{category-slug}-{uuid}.webp`;
   - переносит main + 630 + 100;
   - создаёт/обновляет `ad_images`;
   - удаляет использованные `tmp_files`.
4. Физическое удаление старых изображений — после успешной транзакции.

Удаление temp:

- `DELETE /account/ads/images/{tmpFile}`;
- проверка пользователя и `type`;
- удаляются main + 630 + 100;
- запись `tmp_files` удаляется.

Очистка:

- `php artisan tmp-files:clean`;
- старше 10 дней;
- удаляет `tmp_files` и связанные файлы;
- schedule в `routes/console.php` — 03:28 Europe/Minsk.

### Модерация

- при создании `is_moderated = false`, `is_banned = false`;
- при редактировании `false`, если:
  - изменён title;
  - изменён description;
  - есть новые изображения;
  - добавлено новое видео.
- изменение только цены/регионов/фильтров/артикула/видимости не сбрасывает.

### Копирование

- `«Добавить похожее»` на edit-странице;
- `/account/ads/create?copy={id}`;
- копируются category, price, warranty, description, regions, filters;
- title, number, video, images не копируются.

## 11. Валидация и HTML

- FormRequest’ы: `app/Http/Requests/Account`;
- сообщения переводятся через `__()`;
- `App\Casts\PurifiedHtml`:
  - `seo` — SEO-блоки;
  - `company` — описания компаний и товаров;
- Purifier удаляет опасные теги/атрибуты.

## 12. Тесты

- PHPUnit + `RefreshDatabase`;
- тестовая БД SQLite in-memory;
- `withoutVite()`;
- Inertia-assertions;
- `Storage::fake('public')` для изображений;
- тесты используют домен `minsk.furniture.test`.

Файлы:

- `tests/Feature/AccountPagesTest.php`;
- `tests/Feature/AdminCountryTest.php`;
- `tests/Feature/AdminRegionTimezoneTest.php`;
- `tests/Feature/CompanyScheduleTest.php`;
- `tests/Feature/AdTest.php`;
- `tests/Unit/TimezonesTest.php`.

Актуальный baseline — в `AGENTS.md`.

## 13. Куда добавлять новую функциональность

### Новый раздел кабинета

1. Route в `routes/web.php` внутри account-группы.
2. Controller в `app/Http/Controllers/Account`.
3. FormRequest при необходимости.
4. Vue-страница в `resources/js/account/Pages`.
5. Пункт sidebar в `Components/Sidebar/Sidebar.vue`.
6. Переводы в оба JSON.
7. Тест.

### Новая сущность

1. Migration.
2. Model + fillable/casts/relations.
3. Controller/Request.
4. Vue-страница.
5. Переводы.
6. Feature-тест.

### Новое поле товара

1. Migration.
2. `Ad` fillable/casts.
3. `AdRequest` rules/messages/attributes.
4. `AdController` mapping.
5. `AdForm`/`adForm.ts`.
6. Тест create/update.
7. Переводы.

## 14. Осторожные места

- `storage/app/public` и `public/storage` — проверять `storage:link`.
- Права файлов после создания: `chmod 664`.
- `APP_URL` может не совпадать с текущим доменом; URL изображений строить от запроса (`url('/storage/...')`).
- Не запускать artisan host-PHP 8.3.
- После правок Blade — `view:clear`.
- Не изменять реальные данные пользователя/расписание/файлы без необходимости.
- Не откатывать незакоммиченные изменения.
- Админка русская; не добавлять туда локализацию без отдельной задачи.
