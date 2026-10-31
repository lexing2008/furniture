<?php

namespace App\Services;

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\FilterVariant;
use App\Models\FilterVariantTranslation;
use App\Models\Region;
use App\Models\SeoTemplate;
use App\Models\SeoTemplateTranslation;

/**
 * SEO title, description and article for category pages and single filter-variant pages.
 */
class CategorySeoService
{
    private const FIELDS = ['seo_title', 'seo_description', 'seo_block'];

    /**
     * @return array{seo_title: ?string, seo_description: ?string, seo_block: ?string}
     */
    public function forCategory(Category $category, ?CategoryTranslation $translation, Region $region): array
    {
        $locale = app()->getLocale();

        $category->loadMissing('seoTemplate.translations');
        $categoryTemplate = $this->templateTranslation($category->seoTemplate, $locale);
        $translationTemplate = null;

        if ($translation !== null) {
            if ((int) $translation->seo_template_id === (int) $category->seo_template_id) {
                $translationTemplate = $categoryTemplate;
            } else {
                $translation->loadMissing('seoTemplate.translations');
                $translationTemplate = $this->templateTranslation($translation->seoTemplate, $locale);
            }
        }

        return $this->resolve(
            $translation,
            $translationTemplate,
            $categoryTemplate,
            [
                '{name}' => mb_strtolower((string) ($translation?->name ?? $category->nameIn($locale))),
                '{name_buy}' => mb_strtolower((string) ($translation?->name_buy ?? '')),
                '{region}' => $region->name,
                '{region}' => $region->name,
                '{region_in}' => $region->name_in ?: $region->name,
            ],
        );
    }

    /**
     * @return array{seo_title: ?string, seo_description: ?string, seo_block: ?string}
     */
    public function forVariant(
        FilterVariant $variant,
        ?FilterVariantTranslation $translation,
        Category $category,
        ?CategoryTranslation $categoryTranslation,
        Region $region,
    ): array {
        $locale = app()->getLocale();

        $variant->loadMissing(['translations', 'seoTemplate.translations']);
        $translation?->loadMissing('seoTemplate.translations');

        $primaryTemplate = $this->templateTranslation(
            $translation?->seoTemplate ?? $variant->seoTemplate,
            $locale,
        );
        $variantTemplate = $this->templateTranslation($variant->seoTemplate, $locale);
        $categorySeo = $this->forCategory($category, $categoryTranslation, $region);

        $replacements = [
            '{name}' => mb_strtolower((string) ($translation?->name_full ?: $variant->nameIn($locale))),
            '{name_buy}' => mb_strtolower((string) ($translation?->name_buy ?? '')),
            '{region}' => $region->name,
            '{region_in}' => $region->name_in ?: $region->name,
        ];

        $result = [];

        foreach (self::FIELDS as $field) {
            $text = $this->firstFilled(
                $primaryTemplate?->{$field},
                $variantTemplate?->{$field},
            );

            $result[$field] = $text === null
                ? $categorySeo[$field]
                : $this->replace($text, $replacements);
        }

        return $result;
    }

    /**
     * @param  array<string, string>  $replacements
     * @return array{seo_title: ?string, seo_description: ?string, seo_block: ?string}
     */
    private function resolve(
        ?CategoryTranslation $translation,
        ?SeoTemplateTranslation $primaryTemplate,
        ?SeoTemplateTranslation $fallbackTemplate,
        array $replacements,
    ): array {
        $result = [];

        foreach (self::FIELDS as $field) {
            $text = $this->firstFilled(
                $translation?->{$field},
                $primaryTemplate?->{$field},
                $fallbackTemplate?->{$field},
            );

            $result[$field] = $text === null ? null : $this->replace($text, $replacements);
        }

        return $result;
    }

    private function templateTranslation(?SeoTemplate $template, string $locale): ?SeoTemplateTranslation
    {
        if ($template === null) {
            return null;
        }

        $template->loadMissing('translations');

        return $template->translations->firstWhere('lang', $locale);
    }

    private function firstFilled(?string ...$values): ?string
    {
        foreach ($values as $value) {
            if ($value !== null && trim($value) !== '') {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function replace(string $text, array $replacements): string
    {
        return strtr($text, $replacements);
    }
}
