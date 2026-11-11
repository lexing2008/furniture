<?php

namespace App\Models;

use App\Services\MenuCategoryService;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['parent_id', 'seo_template_id', 'is_active', 'order'])]
class Category extends Model
{
    protected static function booted(): void
    {
        static::saved(fn () => MenuCategoryService::clear());
        static::deleted(fn () => MenuCategoryService::clear());
    }

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function filters(): HasMany
    {
        return $this->hasMany(Filter::class)->orderBy('order')->orderBy('id');
    }

    public function seoTemplate(): BelongsTo
    {
        return $this->belongsTo(SeoTemplate::class);
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    /**
     * Name in the app locale, or in the first available language.
     */
    public function displayName(): string
    {
        $translation = $this->translations->first(fn (CategoryTranslation $t) => $t->lang === app()->getLocale())
            ?? $this->translations->first();

        return $translation?->name ?? "#{$this->id}";
    }

    public function menuName(): string
    {
        $translation = $this->translations->first(fn (CategoryTranslation $t) => $t->lang === app()->getLocale())
            ?? $this->translations->first();

        return $translation?->menu_name ?? '';
    }

    /**
     * Name in the language, or in the app locale / the first available one when there is no such translation.
     */
    public function nameIn(?string $lang): string
    {
        return $this->translations->firstWhere('lang', $lang)?->name ?? $this->displayName();
    }

    /**
     * Slug in the language, falling back to the base language.
     */
    public function slugIn(?string $lang): string
    {
        $translation = $this->translations->firstWhere('lang', $lang)
            ?? $this->translations->firstWhere('lang', 'ru')
            ?? $this->translations->first();

        return $translation?->slug ?? '';
    }

    /**
     * Active categories as a nested tree for choosing: [{id, name, selectable, children}].
     * Only categories without subcategories are selectable; a branch without such categories is left out.
     *
     * @return array<int, array{id: int, name: string, selectable: bool, children: array<int, mixed>}>
     */
    public static function selectableTree(?string $lang): array
    {
        $byParent = self::query()->with('translations')->get()
            ->groupBy(fn (Category $category) => $category->parent_id ?? 0);

        $build = function (int $parentId) use (&$build, $byParent, $lang): array {
            $nodes = [];

            foreach (self::sortForDisplay($byParent[$parentId] ?? new Collection) as $category) {
                if (! $category->is_active) {
                    continue;
                }

                $selectable = ! isset($byParent[$category->id]);
                $children = $build($category->id);

                if ($selectable || $children !== []) {
                    $nodes[] = [
                        'id' => $category->id,
                        'name' => $category->nameIn($lang),
                        'selectable' => $selectable,
                        'children' => $children,
                    ];
                }
            }

            return $nodes;
        };

        return $build(0);
    }

    /**
     * Display order of categories with one parent: by the "order" field, equal ones alphabetically.
     *
     * @param  Collection<int, Category>  $categories
     * @return Collection<int, Category>
     */
    public static function sortForDisplay(Collection $categories): Collection
    {
        return $categories
            ->sortBy([
                fn (Category $a, Category $b) => $a->order <=> $b->order,
                fn (Category $a, Category $b) => mb_strtolower($a->displayName()) <=> mb_strtolower($b->displayName()),
                fn (Category $a, Category $b) => $a->id <=> $b->id,
            ])
            ->values();
    }

    /**
     * Categories with the given parent in the display order.
     *
     * @return Collection<int, Category>
     */
    public static function orderedSiblings(?int $parentId): Collection
    {
        return self::sortForDisplay(
            self::query()->with('translations')->where('parent_id', $parentId)->get(),
        );
    }

    /**
     * The "order" value that puts a category to the end of its siblings.
     */
    public static function nextOrder(?int $parentId): int
    {
        $max = self::query()->where('parent_id', $parentId)->max('order');

        return $max === null ? 0 : (int) $max + 1;
    }

    /**
     * All categories ordered as a tree (parent, then its children), each with a "depth" attribute
     * and the "is_first" / "is_last" flags among the siblings.
     *
     * @return Collection<int, Category>
     */
    public static function tree(): Collection
    {
        $all = self::query()->with('translations')->withCount('filters')->orderBy('id')->get();

        $byParent = $all->groupBy(fn (Category $category) => $category->parent_id ?? 0);

        $result = new Collection;

        $walk = function (int $parentId, int $depth) use (&$walk, $byParent, $result): void {
            $children = self::sortForDisplay($byParent[$parentId] ?? new Collection);

            foreach ($children as $index => $category) {
                $category->setAttribute('depth', $depth);
                $category->setAttribute('is_first', $index === 0);
                $category->setAttribute('is_last', $index === $children->count() - 1);
                $result->push($category);
                $walk($category->id, $depth + 1);
            }
        };

        $walk(0, 0);

        return $result;
    }

    /**
     * IDs of all nested children (used to forbid cycles when choosing a parent).
     *
     * @return array<int, int>
     */
    public function descendantIds(): array
    {
        $byParent = self::query()->pluck('parent_id', 'id')
            ->filter()
            ->groupBy(fn ($parentId) => $parentId, preserveKeys: true)
            ->map(fn ($group) => $group->keys()->all());

        $ids = [];
        $queue = [$this->id];

        while ($queue) {
            $current = array_shift($queue);

            foreach ($byParent[$current] ?? [] as $childId) {
                $ids[] = $childId;
                $queue[] = $childId;
            }
        }

        return $ids;
    }
}
