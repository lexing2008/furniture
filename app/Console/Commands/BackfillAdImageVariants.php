<?php

namespace App\Console\Commands;

use App\Models\AdImage;
use App\Services\AdImageService;
use Illuminate\Console\Command;
use Throwable;

class BackfillAdImageVariants extends Command
{
    protected $signature = 'ads:image-variants';

    protected $description = 'Generate missing ad image previews and persist their dimensions';

    public function handle(AdImageService $imageService): int
    {
        $processed = 0;
        $failed = 0;

        AdImage::query()->chunkById(100, function ($images) use ($imageService, &$processed, &$failed) {
            foreach ($images as $image) {
                try {
                    $imageService->ensureVariants($image);
                    $processed++;
                } catch (Throwable $exception) {
                    $failed++;
                    report($exception);
                }
            }
        });

        $this->info("Processed images: {$processed}. Failed: {$failed}.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
