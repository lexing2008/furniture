<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\AdImage;
use App\Models\TmpFile;
use App\Support\AdImagePath;
use App\Support\Slug;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * Uploads ad images to the public disk as WebP and links temporary uploads to ads.
 */
class AdImageService
{
    private const MAX_SIDE = 1200;

    private const WEBP_QUALITY = 92;

    /**
     * Store an uploaded image as WebP no larger than 1200x1200 and create both previews.
     */
    public function upload(UploadedFile $file, int $userId): TmpFile
    {
        return $this->uploadFromPath($file->getRealPath(), $userId);
    }

    /**
     * Store an image by its local source path. Used by device uploads and remote URLs.
     */
    public function uploadFromPath(string $sourcePath, int $userId): TmpFile
    {
        $uuid = Str::uuid()->toString();
        $path = AdImagePath::main($uuid);
        $medium = AdImagePath::medium($path);
        $listing = AdImagePath::listing($path);
        $small = AdImagePath::small($path);
        $disk = Storage::disk('public');

        $disk->makeDirectory(AdImagePath::DIRECTORY);
        $disk->makeDirectory(AdImagePath::DIRECTORY.'/'.AdImagePath::MEDIUM_SIZE);
        $disk->makeDirectory(AdImagePath::DIRECTORY.'/'.AdImagePath::LISTING_SIZE);
        $disk->makeDirectory(AdImagePath::DIRECTORY.'/'.AdImagePath::SMALL_SIZE);

        try {
            $this->convertToWebp($sourcePath, $disk->path($path), self::MAX_SIDE, self::MAX_SIDE, self::WEBP_QUALITY);
            $this->convertToWebp($sourcePath, $disk->path($medium), 630, 467, 80);
            $this->convertToWebp($sourcePath, $disk->path($listing), 325, 237, 80);
            $this->convertToWebp($sourcePath, $disk->path($small), 100, 100, 75);
        } catch (Throwable $exception) {
            $disk->delete(AdImagePath::variants($path));

            throw ValidationException::withMessages([
                'image' => __('Не удалось обработать изображение.'),
            ]);
        }

        return TmpFile::create([
            'user_id' => $userId,
            'type' => TmpFile::TYPE_AD_IMAGE,
            'path' => $path,
            'uuid' => $uuid,
        ]);
    }

    /**
     * Delete a temporary upload together with its previews.
     */
    public function deleteTemporary(TmpFile $file): void
    {
        Storage::disk('public')->delete(AdImagePath::variants($file->path));
        $file->delete();
    }

    /**
     * Apply the submitted image order and remove the images the owner deleted.
     *
     * @param  array<int, array{id: int, order: int}>  $existing
     * @param  array<int, array{temp_file_id: int, order: int}>  $new
     * @param  array<int, int>  $deleted
     * @return array<int, string> Paths to delete from the disk after the transaction
     */
    public function sync(Ad $ad, int $userId, string $slug, array $existing, array $new, array $deleted): array
    {
        $images = $ad->images()->get()->keyBy('id');
        $nameSlug = Slug::makeLimited((string) $ad->title, 150);
        $pathsToDelete = [];

        $disk = Storage::disk('public');
        $disk->makeDirectory(AdImagePath::DIRECTORY.'/'.AdImagePath::MEDIUM_SIZE);
        $disk->makeDirectory(AdImagePath::DIRECTORY.'/'.AdImagePath::LISTING_SIZE);
        $disk->makeDirectory(AdImagePath::DIRECTORY.'/'.AdImagePath::SMALL_SIZE);

        foreach ($deleted as $id) {
            $image = $images->get((int) $id);

            if ($image === null) {
                continue;
            }

            array_push($pathsToDelete, ...AdImagePath::variants($image->path));
            $image->delete();
        }

        foreach ($existing as $item) {
            $image = $images->get((int) $item['id']);

            if ($image === null) {
                continue;
            }

            $uuid = $image->uuid ?: $this->uuidFromPath($image->path);
            $target = AdImagePath::forAd($slug, $nameSlug, $uuid);
            $this->moveVariants($image->path, $target);
            $image->update([
                'path' => $target,
                'uuid' => $uuid,
                'order' => (int) $item['order'],
            ]);
            $this->ensureVariants($image);
        }

        $usedTmpFiles = [];

        foreach ($new as $item) {
            $tmp = TmpFile::query()
                ->whereKey((int) $item['temp_file_id'])
                ->where('user_id', $userId)
                ->where('type', TmpFile::TYPE_AD_IMAGE)
                ->firstOrFail();

            $uuid = $tmp->uuid ?: $this->uuidFromPath($tmp->path);
            $target = AdImagePath::forAd($slug, $nameSlug, $uuid);
            $this->moveVariants($tmp->path, $target);

            $image = $ad->images()->create([
                'path' => $target,
                'uuid' => $uuid,
                'order' => (int) $item['order'],
            ]);
            $this->ensureVariants($image);

            $usedTmpFiles[] = $tmp->id;
        }

        if ($usedTmpFiles !== []) {
            TmpFile::query()->whereKey($usedTmpFiles)->delete();
        }

        return $pathsToDelete;
    }

    /**
     * Generate the listing preview when missing and persist dimensions of every variant.
     */
    public function ensureVariants(AdImage $image): void
    {
        $disk = Storage::disk('public');
        $listingPath = AdImagePath::listing($image->path);
        $mainPath = $image->path;
        $sourcePath = $mainPath;

        if (! $disk->exists($listingPath)) {
            if (! $disk->exists($sourcePath)) {
                $sourcePath = AdImagePath::medium($mainPath);
            }

            if ($disk->exists($sourcePath)) {
                try {
                    $disk->makeDirectory(dirname($listingPath));
                    $this->convertToWebp($disk->path($sourcePath), $disk->path($listingPath), 325, 237, 80);
                } catch (Throwable) {
                    // Existing legacy images may not be readable; keep the original without a preview.
                }
            }
        }

        $dimensions = [];

        foreach ([
            [null, 'width', 'height'],
            [AdImagePath::SMALL_SIZE, 'thumb_100_width', 'thumb_100_height'],
            [AdImagePath::LISTING_SIZE, 'thumb_325_width', 'thumb_325_height'],
            [AdImagePath::MEDIUM_SIZE, 'thumb_630_width', 'thumb_630_height'],
        ] as [$size, $widthColumn, $heightColumn]) {
            $path = $size === null ? $image->path : AdImagePath::thumbnail($image->path, $size);
            $fullPath = $disk->path($path);

            if (! is_file($fullPath)) {
                continue;
            }

            $sizeInfo = @getimagesize($fullPath);

            if ($sizeInfo === false) {
                continue;
            }

            $dimensions[$widthColumn] = $sizeInfo[0];
            $dimensions[$heightColumn] = $sizeInfo[1];
        }

        if ($dimensions !== []) {
            $image->forceFill($dimensions)->save();
        }
    }

    private function uuidFromPath(string $path): string
    {
        $name = basename($path, '.webp');

        if (preg_match('/[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $name, $matches)) {
            return $matches[0];
        }

        return $name;
    }

    /**
     * Move the main image and both previews to the path with the category prefix.
     */
    private function moveVariants(string $from, string $to): void
    {
        if ($from === $to) {
            return;
        }

        $disk = Storage::disk('public');
        $fromPaths = AdImagePath::variants($from);
        $toPaths = AdImagePath::variants($to);

        foreach ($fromPaths as $index => $fromPath) {
            if (! $disk->exists($fromPath)) {
                continue;
            }

            $toPath = $toPaths[$index];
            $disk->makeDirectory(dirname($toPath));

            if ($disk->exists($toPath)) {
                $disk->delete($fromPath);

                continue;
            }

            $disk->move($fromPath, $toPath);
        }
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function convertToWebp(string $source, string $destination, int $maxWidth, int $maxHeight, int $quality): array
    {
        if (class_exists(\Imagick::class)) {
            try {
                return $this->convertWithImagick($source, $destination, $maxWidth, $maxHeight, $quality);
            } catch (Throwable) {
                // GD is the fallback for installations without WebP support in Imagick
            }
        }

        return $this->convertWithGd($source, $destination, $maxWidth, $maxHeight, $quality);
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function convertWithImagick(string $source, string $destination, int $maxWidth, int $maxHeight, int $quality): array
    {
        $image = new \Imagick($source);
        $image->autoOrient();

        [$width, $height] = [$image->getImageWidth(), $image->getImageHeight()];
        $scale = min(1, $maxWidth / $width, $maxHeight / $height);

        if ($scale < 1) {
            $image->resizeImage(
                max(1, (int) round($width * $scale)),
                max(1, (int) round($height * $scale)),
                \Imagick::FILTER_LANCZOS,
                1,
            );
        }

        $image->setImageFormat('webp');
        $image->setImageCompressionQuality($quality);
        $image->stripImage();
        $image->writeImage($destination);
        $result = [$image->getImageWidth(), $image->getImageHeight()];
        $image->clear();

        return $result;
    }

    /**
     * @return array{0: int, 1: int}
     */
    private function convertWithGd(string $source, string $destination, int $maxWidth, int $maxHeight, int $quality): array
    {
        $contents = file_get_contents($source);
        $image = $contents === false ? false : @imagecreatefromstring($contents);

        if ($image === false) {
            throw new \RuntimeException('Unsupported image');
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $scale = min(1, $maxWidth / $width, $maxHeight / $height);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        imagecopyresampled($canvas, $image, 0, 0, 0, 0, $targetWidth, $targetHeight, $width, $height);

        if (! imagewebp($canvas, $destination, $quality)) {
            throw new \RuntimeException('WebP could not be written');
        }

        imagedestroy($canvas);
        imagedestroy($image);

        return [$targetWidth, $targetHeight];
    }
}
