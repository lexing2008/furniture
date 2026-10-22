<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lang', 'name', 'menu_name', 'slug', 'seo_template_id',
    'seo_title', 'seo_description', 'seo_block'])]
class CategoryTranslation extends Model
{
    public const SEO_FIELDS = ['seo_title', 'seo_description', 'seo_block'];

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang', 'code');
    }

    public function seoTemplate(): BelongsTo
    {
        return $this->belongsTo(SeoTemplate::class);
    }

    /**
     * SEO texts for the page: own fields win, empty ones are taken from the template translation
     * in the same language. The template of the translation wins over the template of the category.
     *
     * @return array{seo_title: ?string, seo_description: ?string, seo_block: ?string}
     */
    public function seo(): array
    {
        $template = ($this->seoTemplate ?? $this->category?->seoTemplate)
            ?->translations->firstWhere('lang', $this->lang);

        $result = [];

        foreach (self::SEO_FIELDS as $field) {
            $own = $this->{$field};

            $result[$field] = $own !== null && $own !== ''
                ? $own
                : $template?->render($field, $this->name);
        }

        return $result;
    }
}
