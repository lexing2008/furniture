<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name'])]
class SeoTemplate extends Model
{
    public function translations(): HasMany
    {
        return $this->hasMany(SeoTemplateTranslation::class);
    }

    /**
     * Templates with the languages they are translated to: forms offer a template only in those languages.
     *
     * @return \Illuminate\Support\Collection<int, array{id: int, name: string, languages: array<int, string>}>
     */
    public static function options()
    {
        return self::query()
            ->with('translations:id,seo_template_id,lang')
            ->orderBy('name')
            ->get()
            ->map(fn (self $template) => [
                'id' => $template->id,
                'name' => $template->name,
                'languages' => $template->translations->pluck('lang')->values(),
            ]);
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function categoryTranslations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }
}
