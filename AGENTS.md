# AGENTS.md — контекст проекта

> Это главный файл для нового чата/агента. Сначала прочитай его, затем `docs/ARCHITECTURE.md`.
> Если меняется архитектура, схема БД, правила домена или порядок проверки — обнови этот файл.
> Последнее обновление: 2026-10-26.

## 1. Что это за проект

Мебельный интернет-торговый центр: компании размещают мебель и услуги, посетители выбирают регион и смотрят предложения.

Основные части:

- публичный сайт на региональных поддоменах;
- кабинет компании `/account`;
- админка `/admin` для сотрудников;
- товары/услуги компаний (`ads`) с изображениями, фильтрами и регионами показа.

## 2. Стек и окружение

- PHP **8.4** — только внутри Docker-контейнера `lamp-apachephp-1`.
- Laravel **13**.
- MySQL, база `furnituredb` (см. `.env`, секреты в документацию не переносить).
- Inertia.js 2 + Vue 3 + TypeScript.
- Tailwind CSS 4.
- Vite 8.
- Тесты — PHPUnit; тестовая БД SQLite `:memory:`.

Важно:

- host PHP 8.3 не подходит для запуска Laravel 13;
- artisan, composer, pint и тесты запускай через Docker;
- Node/npm/vite запускаются на хосте.

Рабочая директория проекта:

```text
/home/alexey/work/furniture/lamp/www
```

## 3. Быстрые команды

### Backend

```bash
docker exec -w /var/www/html lamp-apachephp-1 php artisan test
docker exec -w /var/www/html lamp-apachephp-1 php artisan migrate
docker exec -w /var/www/html lamp-apachephp-1 php artisan view:clear
docker exec -w /var/www/html lamp-apachephp-1 ./vendor/bin/pint <files>
docker exec -w /var/www/html lamp-apachephp-1 ./vendor/bin/pint --test <files>
docker exec -w /var/www/html lamp-apachephp-1 php artisan schedule:list
```

### Frontend

```bash
npx vue-tsc --noEmit
npm run build
```

`npm run build` запускает `vue-tsc`, клиентскую и SSR-сборку. Фактический конфиг — `vite.config.js`; entrypoint’ы: `resources/js/account/app.ts`, `resources/js/admin/app.ts` и их `ssr.ts`.

### Разово / после новых файлов

```bash
git ls-files --others --exclude-standard -z | xargs -0 chmod 664
docker exec -w /var/www/html lamp-apachephp-1 php artisan storage:link
```

`storage:link` уже выполнен в текущем окружении; на новом сервере его нужно выполнить.

## 4. Карта репозитория

```text
routes/web.php          региональные домены + сайт + аккаунт
routes/admin.php        админка
routes/auth.php         общие auth-маршруты
routes/console.php      schedule
routes/account.php      сейчас пустой; account-маршруты в web.php

resources/views/
  app.blade.php         базовый HTML-каркас
  layouts/site.blade.php  каркас сайта
  layouts/partials/     шапка и футер сайта
  account.blade.php     HTML-каркас кабинета
  guest.blade.php       каркас гостевых страниц кабинета
  admin.blade.php       каркас админки
  region/               страницы регионов
  emails/               письма

resources/js/
  account/              Vue-приложение кабинета
  admin/                Vue-приложение админки
  common/               общие компоненты и i18n
  web.js                скрипты сайта

app/Http/Controllers/Account/   кабинет
app/Http/Controllers/Admin/     админка
app/Http/Controllers/RegionPageController.php
app/Http/Middleware/            локаль, Inertia, роли
app/Models/                     Eloquent-модели
app/Services/                   AdImageService, AdFilterService
app/Support/                    AdImagePath, Timezones, Html, Slug
lang/ru.json, lang/en.json      общие переводы backend + frontend
```

## 5. Домены и регионы

Иерархия: **страна → область → город**.

- `regions.parent_id = null` — страна;
- у области родитель — страна;
- у города родитель — область;
- глубина максимум 2 (`Region::MAX_DEPTH`).

Публичные страницы и кабинет открываются на поддомене:

```text
{region}.furniture.lex
```

- `Region::fromHost($host)` находит регион по поддомену.
- `Region::root()` возвращает страну.
- `Region::language()` возвращает `regions.lang` страны.
- `Region::fallbackLanguage()` возвращает необязательный `regions.fallback_lang` страны.
- `Region::cityTree(?int $countryId)` — дерево городов; используется в регистрации, общих данных и товарах.
- `Region::url()` строит URL региона.

## 6. Роли и доступ

Роли (`App\Enums\Roles`):

- `admin`, `manager`, `seo` — сотрудники, доступ в `/admin`;
- `customer` — владелец компании, доступ в `/account`.

Маршруты кабинета защищены `auth` + `role:customer`.
Админка — `auth` + одна из employee-ролей.

`User::homePath()` ведёт сотрудника в админку, клиента — в кабинет.

## 7. Локализация

Язык публичного сайта берётся из страны текущего региона. Язык кабинета: сохранённый `users.lang`, если он поддерживается, иначе — язык региона.

- middleware `SetLocaleFromRegion`;
- админка не локализуется и остаётся русской;
- поддерживаются `ru` и `en`;
- для неподдерживаемого языка используется `fallback_lang` страны;
- если fallback не задан или тоже не поддерживается — fallback `en`;
- если не заданы ни язык страны, ни fallback — остаётся `APP_LOCALE=ru`;
- в кабинете пользователь может выбрать `ru`/`en`, выбор сохраняется в `users.lang`.

Переводы:

- **общие** файлы `lang/ru.json` и `lang/en.json`;
- backend: `__('русская фраза')`, `trans_choice(...)`;
- frontend: `$t('русская фраза')` или `translate(...)`;
- стандартная валидация Laravel: `lang/ru/validation.php`;
- ключи в JSON — русские исходные фразы.

Правило: новая пользовательская фраза должна быть добавлена **и в `ru.json`, и в `en.json`**.

## 8. Frontend-архитектура

- Два независимых Inertia-приложения: `account` и `admin`.
- Корневые Blade-шаблоны задаются middleware:
  - `account` — кабинет;
  - `guest` — гостевые страницы кабинета;
  - `admin` — админка.
- В `account/app.ts` и `account/ssr.ts` зарегистрирован глобальный `$t`.
- `locale` приходит из Inertia shared props.
- Алиасы: `@account`, `@admin`; также `ziggy-js`.
- В кабинете используется зелёный sidebar `bg-green-first`.
- Формы кабинета используют общие `AccountPageHeader`, `AccountFormActions` и `FormStyles`.
- Для длинного HTML — общий `WysiwygEditor` с `resizable` и `height-key`.
- Категория товара выбирается через дерево `CategoryTreeSelect` с поиском (как выбор региона при регистрации).

## 9. Кабинет

Основные разделы:

- главная: статистика товаров, заполненность профиля, быстрые действия и последние товары;
- о компании;
- товары и услуги;
- график работы;
- доставка;
- оплата;
- контакты;
- соцсети;
- смена пароля.

Маршруты товаров:

```text
GET    /account/ads
GET    /account/ads/create
POST   /account/ads
GET    /account/ads/{ad}/edit
PUT    /account/ads/{ad}
PATCH  /account/ads/{ad}/price
PATCH  /account/ads/{ad}/actualize
GET    /account/ads/filters?category_id=...
POST   /account/ads/images
POST   /account/ads/images/url
DELETE /account/ads/images/{tmpFile}
PUT    /account/website
```

## 10. Товары/услуги (`ads`)

Таблицы:

- `ads`;
- `ad_images`;
- `ad_region`;
- `tmp_files`;
- `adv_filter_ints`;
- `adv_filter_floats`;
- `adv_filter_variants`.

Ключевые поля `ads`:

- `company_id`, `category_id`;
- `title`, `number` (nullable, unique в рамках компании);
- `article` (артикул, nullable);
- `is_active` (показывать на сайте, default `true`);
- `is_banned` (default `false`, без поля в форме кабинета);
- `video`, `description`, `price`, `currency`;
- `warranty_value`, `warranty_unit` (`days|months|years`);
- `is_moderated` (default `false`);
- `published_at` — дата актуальности; обновляется при создании, редактировании, изменении цены и подтверждении актуальности; хранится в UTC, отображается в таймзоне региона компании (город → область → страна).

Фильтры:

- типы `checkbox`, `radio`, `input_int`, `input_float`;
- фильтры грузятся по категории через `AdFilterService`;
- значения пишутся в `adv_filter_*`;
- UI подгружает фильтры при смене категории.

Регионы:

- many-to-many через `ad_region`;
- в форме — дерево с чекбоксами;
- у страны и области есть «Выбрать все» / «Снять все».

Модерация:

- при создании `is_moderated = false`;
- при редактировании сбрасывается в `false`, если изменены название, описание, добавлены новые изображения или добавлено новое видео;
- изменение только цены/регионов/фильтров модерацию не сбрасывает.

Копирование:

- на странице редактирования есть «Добавить похожее»;
- открывает `/account/ads/create?copy={id}`;
- копируются категория, гарантия, описание, регионы и фильтры;
- заголовок, номер, видео и изображения не копируются.

## 11. Изображения товаров

Публичный диск: `storage/app/public`.

Основная папка:

```text
storage/app/public/furniture/manual/{category_slug}
storage/app/public/furniture/manual/{category_slug}/630
storage/app/public/furniture/manual/{category_slug}/100
```

Правила:

- загрузка сразу при выборе файла, с progressbar;
- загрузка по ссылке: с любого сайта, обязательна проверка содержимого как изображения;
- до 15 изображений;
- основное изображение — WebP, вписано в `1200×1200`, quality 92;
- превью 630 — вписано в `630×467`, quality 80;
- превью 100 — вписано в `100×100`, quality 75;
- пропорции сохраняются, не увеличиваются;
- временная запись — `tmp_files` с `type=ad_image`;
- после сохранения товара `tmp_files` удаляется;
- при удалении временного изображения на фронте вызывается `DELETE /account/ads/images/{tmpFile}` и удаляются все три файла;
- при удалении изображения товара физическое удаление происходит после успешного сохранения;
- имя файла: `{name_slug}.{uuid}.webp`, где `name_slug` — транслитерированный slug названия до 150 символов;
- путь: `furniture/manual/{category_slug}/{name_slug}.{uuid}.webp`;
- превью: `furniture/manual/{category_slug}/630/...` и `.../{category_slug}/100/...`;
- UUID хранится отдельно: `ad_images.uuid`, `tmp_files.uuid`;
- превью лежат в `630/` и `100/` с тем же базовым именем.

Очистка:

```bash
php artisan tmp-files:clean
```

Удаляет `tmp_files` старше 10 дней и связанные файлы. Запланировано ежедневно в **03:28 Europe/Minsk** в `routes/console.php`.

На сервере нужен cron:

```cron
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

## 12. Валидация и HTML

- Формы кабинета используют FormRequest’ы.
- Сообщения валидации переводятся.
- HTML пользователей очищается через `App\Casts\PurifiedHtml`.
- Профили Purifier:
  - `seo`;
  - `company` (используется для описаний компаний и товаров).
- WYSIWYG не является границей безопасности; сервер очищает HTML.

## 13. Полезные особенности

- `Company` связана с `User`, `Region`, `country_id`, телефонами, категориями, графиком, товарами.
- `region_countries` содержит телефонный код, валюту и число знаков после запятой.
- Валюта товара берётся из страны текущего региона.
- `number` и `article` у товара необязательны.
- `warranty_value` по умолчанию пустой; единица по умолчанию — месяцы.
- `is_active` и `is_banned` — флаги видимости/блокировки; в форме кабинета редактируется только `is_active`.
- `ads.description` очищается профилем `company` и редактируется через WYSIWYG.
- Ссылки в соцсетях принимаются только с доменов соответствующей соцсети (включая поддомены).
- В форме товара регионы можно выбирать массово по стране/области.
- В шапке и подвале сайта выводятся активные категории с заполненным `menu_name` для языка страны региона; в подвале название категории выводится как «{name} в {region_name_in}».
- В блоке «Другие города» выводятся города с `is_capital = true` из `$areas` со ссылками «Мебель в {region_name_in}», кроме текущего города.
- Меню категорий кэшируется на час и сбрасывается при изменении категорий/переводов.
- Для категорий есть страница `GET /{categorySlug}` на поддомене: меню категорий, фильтры, список активных товаров/услуг, включая находящиеся на модерации, сортировка по `published_at` или цене, компания с раскрываемыми реквизитами и телефонами.
- На мобильных шапка сворачивается до логотипа и бургера; меню полноэкранное, поиск и регион внутри, подкатегории подгружаются по кнопке из `GET /categories/{category}/children`.
- В списке товаров есть поиск по названию/артикулу/номеру, фильтры (категория, область/город, цена, фото, параметры, видео), миниатюра 100, статусы, число заполненных фильтров, редактирование цены и подтверждение актуальности.
- Админка:
  - языки;
  - категории;
  - фильтры и переводы фильтров;
  - регионы;
  - страны (`region_countries`);
  - SEO-шаблоны;
  - пользователи;
  - профиль.

## 14. Обязательный workflow изменений

1. Прочитать `AGENTS.md` и `docs/ARCHITECTURE.md`.
2. Найти существующий паттерн в коде и следовать ему.
3. Если меняется БД — миграция + модель + тесты.
4. Если меняется UI — Vue-компонент + обе локали.
5. Запустить:
   - `php artisan test`;
   - `npx vue-tsc --noEmit`;
   - `npm run build`;
   - `pint` для PHP-файлов.
6. После новых файлов — `chmod 664`.
7. После Blade-правок — `php artisan view:clear`.
8. Если изменилась архитектура/правила — обновить `AGENTS.md` и `docs/WORKLOG.md`.

## 15. Осторожно

- Не откатывать незакоммиченные изменения без явной просьбы.
- Не изменять реальные данные пользователя, график работы, заказы и загруженные файлы без необходимости.
- Для визуальных проверок создавать временные данные и удалять их после проверки.
- Не коммитить `.env`, пароли, токены и ключи.
- Не запускать artisan host-PHP 8.3.
- После создания файлов через инструменты проверять права: иначе Apache получает 500.
- `APP_URL` может быть `http://localhost:8000`, но URL изображений строятся от текущего хоста через `url('/storage/...')`.
- `package.json` содержит старые скрипты `dev:account`/`build:client` и т.п. с несуществующими конфигами; используй `npm run dev` и `npm run build` с `vite.config.js`.

## 16. Текущий baseline

- Тесты: **55 passed, 805 assertions**.
- `vue-tsc` и `npm run build` проходят.
- Последние крупные изменения: локализация сайта/кабинета, разделы компании, часовые пояса, расписание, доставка/оплата, страны и валюты, товары/услуги, изображения и миниатюры, копирование товара, WYSIWYG-описание, выбор всех регионов, модерация.
