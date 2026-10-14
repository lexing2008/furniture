<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['lang', 'name', 'seo_title', 'seo_description', 'seo_block'])]
class SeoTemplateTranslation extends Model
{
    public function seoTemplate(): BelongsTo
    {
        return $this->belongsTo(SeoTemplate::class);
    }

    public function language(): BelongsTo
    {
        return $this->belongsTo(Language::class, 'lang', 'code');
    }

    /**
     * Template text with placeholders replaced: "Купить {name}" -> "Купить диваны".
     */
    public function render(string $field, string $name): ?string
    {
        $text = $this->{$field};

        return $text === null || $text === '' ? null : str_replace('{name}', $name, $text);
    }
}
