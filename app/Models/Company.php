<?php

namespace App\Models;

use App\Casts\PurifiedHtml;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'region_id', 'legal_name', 'tax_id', 'display_name', 'description',
    'is_online_store', 'website', 'email', 'socials',
])]
class Company extends Model
{
    /** Social networks of a company: key => title */
    public const SOCIALS = [
        'facebook' => 'Facebook',
        'instagram' => 'Instagram',
        'youtube' => 'YouTube',
        'vk' => 'ВКонтакте',
        'telegram' => 'Telegram',
        'tiktok' => 'TikTok',
        'linkedin' => 'LinkedIn',
        'x' => 'X (Twitter)',
        'ok' => 'Одноклассники',
        'pinterest' => 'Pinterest',
    ];

    protected function casts(): array
    {
        return [
            'description' => PurifiedHtml::class.':company',
            'is_online_store' => 'boolean',
            'socials' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function region(): BelongsTo
    {
        return $this->belongsTo(Region::class);
    }

    public function phones(): HasMany
    {
        return $this->hasMany(CompanyPhone::class)->orderBy('id');
    }

    /**
     * Replaces the phones and the categories of the company with the submitted ones.
     *
     * @param  array<int, array{code: string, number: string, description: ?string}>  $phones
     * @param  array<int, int>  $categoryIds
     */
    public function syncRelations(array $phones, array $categoryIds): void
    {
        $this->syncPhones($phones);
        $this->syncCategories($categoryIds);
    }

    /**
     * Replaces the phones of the company with the submitted ones.
     *
     * @param  array<int, array{code: string, number: string, description: ?string}>  $phones
     */
    public function syncPhones(array $phones): void
    {
        $this->phones()->delete();
        $this->phones()->createMany($phones);
    }

    /**
     * @param  array<int, int>  $categoryIds
     */
    public function syncCategories(array $categoryIds): void
    {
        $this->categories()->sync($categoryIds);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'category_company');
    }
}
