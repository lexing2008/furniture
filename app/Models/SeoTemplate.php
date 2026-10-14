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

    public function categoryTranslations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }
}
