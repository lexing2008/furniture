<?php

namespace App\Http\Requests\Account;

use App\Enums\FilterType;
use App\Models\Ad;
use App\Models\Category;
use App\Models\Company;
use App\Models\Filter;
use App\Models\Region;
use App\Models\TmpFile;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class AdRequest extends FormRequest
{
    private ?Company $resolvedCompany = null;

    private ?Ad $resolvedAd = null;

    private ?Collection $categoryFilters = null;

    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $filters = $this->input('filters', []);

        $this->merge([
            'title' => trim((string) $this->input('title', '')),
            'number' => filled($this->input('number')) ? trim((string) $this->input('number')) : null,
            'article' => filled($this->input('article')) ? trim((string) $this->input('article')) : null,
            'video' => filled($this->input('video')) ? trim((string) $this->input('video')) : null,
            'description' => filled($this->input('description')) ? trim((string) $this->input('description')) : null,
            'price' => trim((string) $this->input('price', '')),
            'warranty_value' => filled($this->input('warranty_value')) ? $this->input('warranty_value') : null,
            'warranty_unit' => (string) ($this->input('warranty_unit') ?: 'months'),
            'regions' => collect($this->input('regions', []))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
            'filters' => is_array($filters) ? $filters : [],
            'existing_images' => collect($this->input('existing_images', []))
                ->map(fn ($image) => [
                    'id' => (int) ($image['id'] ?? 0),
                    'order' => (int) ($image['order'] ?? 0),
                ])
                ->values()
                ->all(),
            'new_images' => collect($this->input('new_images', []))
                ->map(fn ($image) => [
                    'temp_file_id' => (int) ($image['temp_file_id'] ?? 0),
                    'order' => (int) ($image['order'] ?? 0),
                ])
                ->values()
                ->all(),
            'deleted_image_ids' => collect($this->input('deleted_image_ids', []))
                ->map(fn ($id) => (int) $id)
                ->unique()
                ->values()
                ->all(),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $company = $this->company();
        $ad = $this->ad();

        return [
            'title' => ['required', 'string', 'max:255'],
            'number' => [
                'nullable',
                'string',
                'max:255',
                Rule::unique('ads', 'number')
                    ->where(fn ($query) => $query->where('company_id', $company->id))
                    ->ignore($ad?->id),
            ],
            'article' => ['nullable', 'string', 'max:255'],
            'category_id' => ['required', 'integer'],
            'description' => ['nullable', 'string', 'max:15000'],
            'price' => ['required', 'numeric', 'min:0', 'max:99999999999.9999'],
            'warranty_value' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'warranty_unit' => ['required_with:warranty_value', Rule::in(Ad::WARRANTY_UNITS)],
            'video' => ['nullable', 'url:http,https', 'max:2048'],
            'regions' => ['required', 'array', 'min:1'],
            'regions.*' => ['integer'],
            'filters' => ['nullable', 'array'],
            'existing_images' => ['array'],
            'existing_images.*.id' => ['required', 'integer'],
            'existing_images.*.order' => ['required', 'integer', 'min:0'],
            'new_images' => ['array'],
            'new_images.*.temp_file_id' => ['required', 'integer'],
            'new_images.*.order' => ['required', 'integer', 'min:0'],
            'deleted_image_ids' => ['array'],
            'deleted_image_ids.*' => ['integer'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }

    public function after(): array
    {
        return [
            function (Validator $validator): void {
                $company = $this->company();
                $ad = $this->ad();

                $category = Category::query()
                    ->whereKey((int) $this->input('category_id'))
                    ->where('is_active', true)
                    ->whereDoesntHave('children')
                    ->first();

                if ($category === null) {
                    $validator->errors()->add('category_id', __('Выберите категорию из списка.'));
                }

                if (blank($this->currency())) {
                    $validator->errors()->add('currency', __('Для страны текущего региона не настроена валюта.'));
                }

                if (filled($this->input('video')) && ! preg_match('#^https?://([a-z0-9-]+\.)*youtube\.com/|^https?://youtu\.be/#i', (string) $this->input('video'))) {
                    $validator->errors()->add('video', __('Укажите ссылку на YouTube.'));
                }

                $this->validateRegions($validator, $company);
                $this->validateImages($validator, $ad);

                if ($category !== null) {
                    $this->validateFilters($validator, $category);
                }
            },
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function adData(): array
    {
        $data = $this->safe()->only([
            'title', 'number', 'article', 'category_id', 'description', 'price',
            'warranty_value', 'warranty_unit', 'video',
        ]);

        if ($this->has('is_active')) {
            $data['is_active'] = $this->boolean('is_active');
        }

        return $data;
    }

    /**
     * @return array{existing: array<int, array{id: int, order: int}>, new: array<int, array{temp_file_id: int, order: int}>, deleted: array<int, int>}
     */
    public function imageData(): array
    {
        return [
            'existing' => $this->input('existing_images', []),
            'new' => $this->input('new_images', []),
            'deleted' => $this->input('deleted_image_ids', []),
        ];
    }

    /**
     * @return array{ints: array<int, int>, floats: array<int, float|int|string>, variants: array<int, array<int, int>>}
     */
    public function filterValues(): array
    {
        $result = ['ints' => [], 'floats' => [], 'variants' => []];
        $input = (array) $this->input('filters', []);

        foreach ($this->filtersForCategory() as $filter) {
            $value = $input[$filter->id] ?? null;

            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            if ($filter->type === FilterType::CHECKBOX || $filter->type === FilterType::RADIO) {
                $variantIds = collect(is_array($value) ? $value : [$value])
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                $result['variants'][$filter->id] = $variantIds;
            } elseif ($filter->type === FilterType::INPUT_INT) {
                $result['ints'][$filter->id] = (int) $value;
            } elseif ($filter->type === FilterType::INPUT_FLOAT) {
                $result['floats'][$filter->id] = (float) $value;
            }
        }

        return $result;
    }

    /**
     * The currency of the country of the current region.
     */
    public function currency(): ?string
    {
        return $this->country()?->countryData?->currency;
    }

    public function currencyDecimals(): int
    {
        return (int) ($this->country()?->countryData?->currency_decimals ?? 2);
    }

    /**
     * Slug of the selected category in the current language, used as an image file prefix.
     */
    public function categorySlug(): string
    {
        $category = Category::query()
            ->with('translations')
            ->find((int) $this->input('category_id'));

        $slug = Str::slug($category?->slugIn(app()->getLocale()) ?? '');

        return $slug !== '' ? $slug : 'category-'.($category?->id ?? 'unknown');
    }

    public function messages(): array
    {
        return [
            'required' => __('Заполните поле «:attribute».'),
            'string' => __('Поле «:attribute» должно быть текстом.'),
            'max.string' => __('Поле «:attribute» не должно быть длиннее :max символов.'),
            'integer' => __('Поле «:attribute» должно быть целым числом.'),
            'numeric' => __('Поле «:attribute» должно быть числом.'),
            'url' => __('Укажите корректный адрес, например https://example.com.'),
            'unique' => __('Значение поля «:attribute» уже занято.'),
            'min.array' => __('Добавьте хотя бы один элемент: «:attribute».'),
            'max.array' => __('В поле «:attribute» не больше :max элементов.'),
            'regions.required' => __('Выберите хотя бы один регион.'),
            'regions.min' => __('Выберите хотя бы один регион.'),
            'number.unique' => __('Этот номер уже используется в вашей компании.'),
            'warranty_unit.in' => __('Выберите единицу измерения гарантии.'),
            'warranty_unit.required_with' => __('Выберите единицу измерения гарантии.'),
        ];
    }

    public function attributes(): array
    {
        return [
            'title' => __('название'),
            'number' => __('номер в собственной системе'),
            'article' => __('артикул'),
            'category_id' => __('категория'),
            'description' => __('описание'),
            'price' => __('цена'),
            'warranty_value' => __('срок гарантии'),
            'warranty_unit' => __('единица гарантии'),
            'video' => __('видео'),
            'regions' => __('регионы'),
            'is_active' => __('отображение на сайте'),
        ];
    }

    private function company(): Company
    {
        if ($this->resolvedCompany === null) {
            $this->resolvedCompany = $this->user()->company()->firstOrFail();
        }

        return $this->resolvedCompany;
    }

    private function ad(): ?Ad
    {
        if ($this->resolvedAd !== null) {
            return $this->resolvedAd;
        }

        $ad = $this->route('ad');

        if (is_string($ad)) {
            $ad = Ad::query()->find($ad);
        }

        if ($ad instanceof Ad) {
            abort_unless($ad->company_id === $this->company()->id, 404);

            $this->resolvedAd = $ad;
        }

        return $this->resolvedAd;
    }

    private function country(): ?Region
    {
        $company = $this->company();
        $region = Region::fromHost($this->getHost()) ?? $company->region;

        return $region?->root() ?? $company->country;
    }

    private function validateRegions(Validator $validator, Company $company): void
    {
        $ids = collect($this->input('regions', []))->map(fn ($id) => (int) $id);
        $countryId = $company->country_id ?? $company->region?->root()->id;

        if ($ids->isEmpty()) {
            return;
        }

        $regions = Region::query()->whereIn('id', $ids)->get();

        $valid = $regions->count() === $ids->unique()->count()
            && $regions->every(fn (Region $region) => $region->isCity() && $region->root()->id === $countryId);

        if (! $valid) {
            $validator->errors()->add('regions', __('Выберите регионы из страны вашей компании.'));
        }
    }

    /**
     * @param  array<int, array{id: int, order: int}>  $existing
     * @param  array<int, array{temp_file_id: int, order: int}>  $new
     */
    private function validateImages(Validator $validator, ?Ad $ad): void
    {
        $existing = $this->input('existing_images', []);
        $new = $this->input('new_images', []);
        $deleted = $this->input('deleted_image_ids', []);

        if (count($existing) + count($new) > 15) {
            $validator->errors()->add('images', __('Можно загрузить не больше 15 изображений.'));
        }

        $orders = collect($existing)->pluck('order')->merge(collect($new)->pluck('order'));

        if ($orders->count() !== $orders->unique()->count()) {
            $validator->errors()->add('images', __('Порядок изображений указан неверно.'));
        }

        $existingIds = collect($existing)->pluck('id')->map(fn ($id) => (int) $id);
        $deletedIds = collect($deleted)->map(fn ($id) => (int) $id);

        if ($existingIds->count() !== $existingIds->unique()->count()) {
            $validator->errors()->add('images', __('Одно изображение указано дважды.'));
        }

        if ($existingIds->intersect($deletedIds)->isNotEmpty()) {
            $validator->errors()->add('images', __('Нельзя одновременно сохранить и удалить одно изображение.'));
        }

        if ($ad === null) {
            if ($existingIds->isNotEmpty() || $deletedIds->isNotEmpty()) {
                $validator->errors()->add('images', __('Изображения не найдены.'));
            }
        } else {
            $ownImageIds = $ad->images()->pluck('id')->map(fn ($id) => (int) $id);

            if ($existingIds->diff($ownImageIds)->isNotEmpty() || $deletedIds->diff($ownImageIds)->isNotEmpty()) {
                $validator->errors()->add('images', __('Изображения не найдены.'));
            }
        }

        $tempIds = collect($new)->pluck('temp_file_id')->map(fn ($id) => (int) $id);

        if ($tempIds->count() !== $tempIds->unique()->count()) {
            $validator->errors()->add('images', __('Одно изображение указано дважды.'));

            return;
        }

        if ($tempIds->isEmpty()) {
            return;
        }

        $owned = TmpFile::query()
            ->whereIn('id', $tempIds)
            ->where('user_id', $this->user()->id)
            ->where('type', TmpFile::TYPE_AD_IMAGE)
            ->count();

        if ($owned !== $tempIds->unique()->count()) {
            $validator->errors()->add('images', __('Изображение принадлежит другому пользователю или не найдено.'));
        }
    }

    private function validateFilters(Validator $validator, Category $category): void
    {
        $filters = $this->filtersForCategory();
        $input = (array) $this->input('filters', []);

        foreach ($input as $filterId => $value) {
            $filter = $filters->get((int) $filterId);

            if ($filter === null) {
                $validator->errors()->add('filters', __('Выбран неизвестный фильтр.'));

                continue;
            }

            $error = $this->filterError($filter, $value);

            if ($error !== null) {
                $validator->errors()->add("filters.{$filter->id}", $error);
            }
        }
    }

    private function filterError(Filter $filter, mixed $value): ?string
    {
        if ($filter->type === FilterType::CHECKBOX) {
            if (! is_array($value)) {
                return __('Выберите значения фильтра.');
            }

            foreach ($value as $variantId) {
                if (! $filter->variants->contains('id', (int) $variantId)) {
                    return __('Выбран некорректный вариант фильтра.');
                }
            }

            return null;
        }

        if ($filter->type === FilterType::RADIO) {
            if ($value === '' || $value === null) {
                return null;
            }

            if (is_array($value) || ! $filter->variants->contains('id', (int) $value)) {
                return __('Выбран некорректный вариант фильтра.');
            }

            return null;
        }

        if ($value === '' || $value === null) {
            return null;
        }

        if (is_array($value)) {
            return $filter->type === FilterType::INPUT_INT
                ? __('Введите целое число.')
                : __('Введите число.');
        }

        if ($filter->type === FilterType::INPUT_INT && filter_var($value, FILTER_VALIDATE_INT) === false) {
            return __('Введите целое число.');
        }

        if ($filter->type === FilterType::INPUT_FLOAT && ! is_numeric($value)) {
            return __('Введите число.');
        }

        return null;
    }

    /**
     * @return Collection<int, Filter>
     */
    private function filtersForCategory(): Collection
    {
        if ($this->categoryFilters === null) {
            $this->categoryFilters = Filter::query()
                ->where('category_id', (int) $this->input('category_id'))
                ->where('is_active', true)
                ->with('variants')
                ->get()
                ->keyBy('id');
        }

        return $this->categoryFilters;
    }
}
