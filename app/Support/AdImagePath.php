<?php

namespace App\Support;

/**
 * Main ad images and the previews generated next to them.
 */
final class AdImagePath
{
    public const DIRECTORY = 'furniture/manual';

    public const MEDIUM_SIZE = 630;

    public const SMALL_SIZE = 100;

    public static function main(string $name): string
    {
        return self::DIRECTORY.'/'.$name.'.webp';
    }

    public static function medium(string $mainPath): string
    {
        return self::thumbnail($mainPath, self::MEDIUM_SIZE);
    }

    public static function small(string $mainPath): string
    {
        return self::thumbnail($mainPath, self::SMALL_SIZE);
    }

    /**
     * All files belonging to one image: the main image and both previews.
     *
     * @return array<int, string>
     */
    public static function variants(string $mainPath): array
    {
        return [$mainPath, self::medium($mainPath), self::small($mainPath)];
    }

    public static function thumbnail(string $mainPath, int $size): string
    {
        return self::DIRECTORY.'/'.$size.'/'.basename($mainPath);
    }
}
