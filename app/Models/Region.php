<?php

namespace App\Models;

use App\Casts\PurifiedHtml;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Country (parent_id = null) -> area -> city.
 */
#[Fillable([
    'parent_id', 'country_id', 'name', 'name_en', 'name_in', 'subdomain', 'lang', 'fallback_lang', 'timezone', 'is_capital',
    'seo_title', 'seo_description', 'seo_block', 'is_active',
])]
class Region extends Model
{
    public const SEO_FIELDS = ['seo_title', 'seo_description', 'seo_block'];

    /** Depth of the deepest level: 0 country, 1 area, 2 city */
    public const MAX_DEPTH = 2;

    /** Subdomains that cannot be taken by a region */
    public const RESERVED_SUBDOMAINS = ['www'];

    protected function casts(): array
    {
        return [
            'country_id' => 'integer',
            'is_capital' => 'boolean',
            'is_active' => 'boolean',
            'seo_block' => PurifiedHtml::class.':seo',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $region): void {
            if ($region->parent_id === null) {
                if ($region->exists) {
                    $region->country_id = $region->getKey();
                }

                return;
            }

            $parent = self::query()->find($region->parent_id, ['id', 'parent_id', 'country_id']);
            $region->country_id = $parent?->country_id ?? $parent?->root()->id;
        });

        static::created(function (self $region): void {
            if ($region->parent_id === null && $region->country_id !== $region->getKey()) {
                $region->forceFill(['country_id' => $region->getKey()])->saveQuietly();
            }
        });
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function country(): BelongsTo
    {
        return $this->belongsTo(self::class, 'country_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    /**
     * Phone code, currency and other data of a country region.
     */
    public function countryData(): HasOne
    {
        return $this->hasOne(RegionCountry::class);
    }

    /**
     * Whether the region is a city: the third level, country -> area -> city.
     */
    public function isCity(): bool
    {
        return $this->parent_id !== null && $this->parent?->parent_id !== null;
    }

    /**
     * Cities a company can choose, grouped by area: [{label: "Беларусь — Минская область", cities: [{id, name}]}].
     * Only the cities that are shown on the site: the city, its area and its country are active.
     *
     * @return array<int, array{label: string, cities: array<int, array{id: int, name: string}>}>
     */
    public static function cityOptions(): array
    {
        $tree = self::tree();
        $byId = $tree->keyBy('id');

        $available = function (self $region) use ($byId): bool {
            for ($current = $region; $current !== null; $current = $byId[$current->parent_id] ?? null) {
                if (! $current->is_active) {
                    return false;
                }
            }

            return true;
        };

        $groups = [];

        foreach ($tree as $region) {
            if ($region->depth !== self::MAX_DEPTH || ! $available($region)) {
                continue;
            }

            $area = $byId[$region->parent_id];
            $country = $byId[$area->parent_id];

            $groups[$area->id] ??= ['label' => "{$country->name} — {$area->name}", 'cities' => []];
            $groups[$area->id]['cities'][] = ['id' => $region->id, 'name' => $region->name];
        }

        return array_values($groups);
    }

    /**
     * Cities a company can choose as a nested tree: country -> area -> city.
     * Only the branches that contain an available city are kept; cities are selectable.
     *
     * @return array<int, array{id: int, name: string, selectable: bool, children: array<int, mixed>}>
     */
    public static function cityTree(?int $countryId = null): array
    {
        $tree = self::tree();
        $byId = $tree->keyBy('id');

        $available = function (self $region) use ($byId): bool {
            for ($current = $region; $current !== null; $current = $byId[$current->parent_id] ?? null) {
                if (! $current->is_active) {
                    return false;
                }
            }

            return true;
        };

        $byParent = $tree->groupBy(fn (self $region) => $region->parent_id ?? 0);

        $build = function (int $parentId) use (&$build, $byParent, $available, $countryId): array {
            $nodes = [];

            foreach ($byParent[$parentId] ?? [] as $region) {
                if ($parentId === 0 && $countryId !== null && $region->id !== $countryId) {
                    continue;
                }

                if ($region->depth === self::MAX_DEPTH) {
                    if ($available($region)) {
                        $nodes[] = [
                            'id' => $region->id,
                            'name' => $region->name,
                            'name_en' => $region->name_en,
                            'selectable' => true,
                            'children' => [],
                        ];
                    }

                    continue;
                }

                $children = $build($region->id);

                if ($children !== []) {
                    $nodes[] = [
                        'id' => $region->id,
                        'name' => $region->name,
                        'name_en' => $region->name_en,
                        'selectable' => false,
                        'children' => $children,
                    ];
                }
            }

            return $nodes;
        };

        return $build(0);
    }

    /**
     * The country of the region (the region itself for a country).
     */
    public function root(): self
    {
        $region = $this;

        while ($region->parent_id !== null) {
            $region = $region->parent;
        }

        return $region;
    }

    public function getTimezone(): ?string
    {
        $region = $this;

        if ($this->timezone) {
            return $this->timezone;
        }

        while ($region !== null) {
            $region = $region->parent;
            if ($region?->timezone) {
                return $region->timezone;
            }
        }

        return null;
    }

    /**
     * Page language: the language of the country.
     */
    public function language(): ?string
    {
        return $this->root()->lang;
    }

    /**
     * Optional fallback language of the country.
     */
    public function fallbackLanguage(): ?string
    {
        return $this->root()->fallback_lang;
    }

    /**
     * A region is shown on the site only when it and all its parents are active.
     */
    public function isAvailable(): bool
    {
        for ($region = $this; $region !== null; $region = $region->parent) {
            if (! $region->is_active) {
                return false;
            }
        }

        return true;
    }

    /**
     * SEO texts of the page: empty values are taken from the country.
     *
     * @return array{seo_title: ?string, seo_description: ?string, seo_block: ?string}
     */
    public function seo(): array
    {
        $root = $this->root(); // country

        $result = [];

        foreach (self::SEO_FIELDS as $field) {
            $value = filled($this->{$field}) ? $this->{$field} : $root->{$field};
            $value = str_replace('{region}', $this->name, $value);
            $value = str_replace('{region_in}', $this->name_in, $value);
            $result[$field] = $value;
        }

        return $result;
    }

    /**
     * Address of the region page: "https://berlin.furniture.com".
     */
    public function url(): string
    {
        return request()->getScheme().'://'.$this->subdomain.'.'.config('app.domain');
    }

    /**
     * All regions ordered as a tree (country, its areas, their cities), each with a "depth" attribute.
     *
     * @return Collection<int, Region>
     */
    public static function tree(): Collection
    {
        $all = self::query()->orderBy('id')->get();

        $byParent = $all->groupBy(fn (Region $region) => $region->parent_id ?? 0);

        $result = new Collection;

        $walk = function (int $parentId, int $depth) use (&$walk, $byParent, $result): void {
            $children = ($byParent[$parentId] ?? collect())
                ->sortBy([
                    // 1. Сначала столицы (true идет выше false)
                    fn (Region $a, Region $b) => $b->is_capital <=> $a->is_capital,
                    // 2. Затем по алфавиту без учета регистра
                    fn (Region $a, Region $b) => mb_strtolower($a->name) <=> mb_strtolower($b->name),
                ]);

            foreach ($children as $region) {
                $region->setAttribute('depth', $depth);
                $result->push($region);
                $walk($region->id, $depth + 1);
            }
        };

        $walk(0, 0);

        return $result;
    }

    /**
     * Regions that can become the parent of a region without breaking the country -> area -> city levels:
     * not the region itself, not its descendants, and the whole branch must still fit into three levels.
     *
     * @return Collection<int, Region>
     */
    public static function allowedParents(?self $region = null): Collection
    {
        $tree = self::tree();

        $children = $tree->groupBy(fn (Region $r) => $r->parent_id ?? 0);

        $excluded = [];
        $height = 0;

        if ($region !== null) {
            $excluded = [$region->id];
            $height = 0;

            // Level by level down the branch of the region
            $level = [$region->id];

            while ($level) {
                $next = [];

                foreach ($level as $id) {
                    foreach ($children[$id] ?? [] as $child) {
                        $next[] = $child->id;
                    }
                }

                if ($next) {
                    $height++;
                    $excluded = [...$excluded, ...$next];
                }

                $level = $next;
            }
        }

        return $tree
            ->reject(fn (Region $r) => in_array($r->id, $excluded, true))
            ->filter(fn (Region $r) => $r->depth + 1 + $height <= self::MAX_DEPTH)
            ->values();
    }
}
