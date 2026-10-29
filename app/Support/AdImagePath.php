<?php

namespace App\Support;

/**
 * Main ad images and the previews generated next to them.
 */
final class AdImagePath
{
    public const DIRECTORY = 'furniture/manual';

    public const MEDIUM_SIZE = 630;

    public const LISTING_SIZE = 325;

    public const SMALL_SIZE = 100;

    public static function main(string $name): string
    {
        return self::DIRECTORY.'/'.$name.'.webp';
    }

    /**
     * Final path of an ad image: furniture/manual/{category}/{name}.{uuid}.webp.
     */
    public static function forAd(string $categorySlug, string $nameSlug, string $uuid): string
    {
        return self::DIRECTORY.'/'.$categorySlug.'/'.$nameSlug.'.'.$uuid.'.webp';
    }

    public static function medium(string $mainPath): string
    {
        return self::thumbnail($mainPath, self::MEDIUM_SIZE);
    }

    public static function listing(string $mainPath): string
    {
        return self::thumbnail($mainPath, self::LISTING_SIZE);
    }

    public static function small(string $mainPath): string
    {
        return self::thumbnail($mainPath, self::SMALL_SIZE);
    }

    /**
     * All files belonging to one image: the main image and its previews.
     *
     * @return array<int, string>
     */
    public static function variants(string $mainPath): array
    {
        return [$mainPath, self::medium($mainPath), self::listing($mainPath), self::small($mainPath)];
    }

    public static function thumbnail(string $mainPath, int $size): string
    {
        return dirname($mainPath).'/'.$size.'/'.basename($mainPath);
    }
}
