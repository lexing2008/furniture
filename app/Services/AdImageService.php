<?php

namespace App\Services;

use App\Models\Ad;
use App\Models\TmpFile;
use App\Support\AdImagePath;
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
        $uuid = Str::uuid()->toString();
        $path = AdImagePath::main($uuid);
        $medium = AdImagePath::medium($path);
        $small = AdImagePath::small($path);
        $disk = Storage::disk('public');

        $disk->makeDirectory(AdImagePath::DIRECTORY);
        $disk->makeDirectory(AdImagePath::DIRECTORY.'/'.AdImagePath::MEDIUM_SIZE);
        $disk->makeDirectory(AdImagePath::DIRECTORY.'/'.AdImagePath::SMALL_SIZE);

        try {
            $this->convertToWebp($file->getRealPath(), $disk->path($path), self::MAX_SIDE, self::MAX_SIDE, self::WEBP_QUALITY);
            $this->convertToWebp($file->getRealPath(), $disk->path($medium), 630, 467, 80);
            $this->convertToWebp($file->getRealPath(), $disk->path($small), 100, 100, 75);
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
        $pathsToDelete = [];

        $disk = Storage::disk('public');
        $disk->makeDirectory(AdImagePath::DIRECTORY.'/'.AdImagePath::MEDIUM_SIZE);
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
            $target = AdImagePath::main($slug.'-'.$uuid);
            $this->moveVariants($image->path, $target);
            $image->update([
                'path' => $target,
                'uuid' => $uuid,
                'order' => (int) $item['order'],
            ]);
        }

        $usedTmpFiles = [];

        foreach ($new as $item) {
            $tmp = TmpFile::query()
                ->whereKey((int) $item['temp_file_id'])
                ->where('user_id', $userId)
                ->where('type', TmpFile::TYPE_AD_IMAGE)
                ->firstOrFail();

            $uuid = $tmp->uuid ?: $this->uuidFromPath($tmp->path);
            $target = AdImagePath::main($slug.'-'.$uuid);
            $this->moveVariants($tmp->path, $target);

            $ad->images()->create([
                'path' => $target,
                'uuid' => $uuid,
                'order' => (int) $item['order'],
            ]);

            $usedTmpFiles[] = $tmp->id;
        }

        if ($usedTmpFiles !== []) {
            TmpFile::query()->whereKey($usedTmpFiles)->delete();
        }

        return $pathsToDelete;
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

            if ($disk->exists($toPath)) {
                $disk->delete($fromPath);

                continue;
            }

            $disk->move($fromPath, $toPath);
        }
    }

    private function convertToWebp(string $source, string $destination, int $maxWidth, int $maxHeight, int $quality): void
    {
        if (class_exists(\Imagick::class)) {
            try {
                $this->convertWithImagick($source, $destination, $maxWidth, $maxHeight, $quality);

                return;
            } catch (Throwable) {
                // GD is the fallback for installations without WebP support in Imagick
            }
        }

        $this->convertWithGd($source, $destination, $maxWidth, $maxHeight, $quality);
    }

    private function convertWithImagick(string $source, string $destination, int $maxWidth, int $maxHeight, int $quality): void
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
        $image->clear();
    }

    private function convertWithGd(string $source, string $destination, int $maxWidth, int $maxHeight, int $quality): void
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
    }
}
