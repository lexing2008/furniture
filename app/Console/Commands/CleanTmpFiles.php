<?php

namespace App\Console\Commands;

use App\Models\TmpFile;
use App\Support\AdImagePath;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CleanTmpFiles extends Command
{
    protected $signature = 'tmp-files:clean';

    protected $description = 'Delete temporary files older than 7 days';

    public function handle(): int
    {
        $deleted = 0;

        TmpFile::query()
            ->where('created_at', '<', now()->subDays(7))
            ->chunkById(100, function ($files) use (&$deleted) {
                foreach ($files as $file) {
                    Storage::disk('public')->delete(AdImagePath::variants($file->path));
                    $file->delete();
                    $deleted++;
                }
            });

        $this->info("Deleted temporary files: {$deleted}.");

        return self::SUCCESS;
    }
}
