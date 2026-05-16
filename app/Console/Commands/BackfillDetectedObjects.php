<?php

namespace App\Console\Commands;

use App\Models\ImageMetadata;
use App\Services\GoogleVisionService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class BackfillDetectedObjects extends Command
{
    protected $signature = 'items:backfill-vision
                            {--limit=50 : Max rows to process}
                            {--force : Re-run Vision even when detected_objects already exists}';

    protected $description = 'Run Google Vision on existing items missing detected_objects';

    public function handle(GoogleVisionService $vision): int
    {
        if (! $vision->isEnabled()) {
            $this->error('Google Vision is disabled. Enable it in Admin → Settings (Google Vision tab) or set GOOGLE_VISION_ENABLED=true in .env.');

            return self::FAILURE;
        }

        if ($vision->getApiKey() === '') {
            $this->error('Google Vision API key is missing. Configure it in Admin → Settings or GOOGLE_VISION_API_KEY in .env.');

            return self::FAILURE;
        }

        $query = ImageMetadata::query()->orderBy('id');
        if (! $this->option('force')) {
            $query->where(function ($q) {
                $q->whereNull('detected_objects')
                    ->orWhere('detected_objects', '[]')
                    ->orWhere('detected_objects', 'null');
            });
        }

        $limit = (int) $this->option('limit');
        $rows = $query->limit($limit)->get();

        if ($rows->isEmpty()) {
            $this->info('No items need backfill.');

            return self::SUCCESS;
        }

        $updated = 0;
        $skipped = 0;

        foreach ($rows as $row) {
            $relativePath = $this->resolveStoragePath($row);
            if ($relativePath === null || ! Storage::disk('public')->exists($relativePath)) {
                $this->warn("Skip id={$row->id}: image file not found");
                $skipped++;

                continue;
            }

            $fullPath = Storage::disk('public')->path($relativePath);
            $detected = $vision->detectObjects($fullPath);

            if ($detected === null) {
                $this->warn("Skip id={$row->id}: Vision returned no results");
                $skipped++;

                continue;
            }

            $row->detected_objects = $detected;
            $row->save();
            $updated++;
            $this->line("Updated id={$row->id} upload_id={$row->upload_id} (".count($detected).' labels)');
        }

        $this->info("Done. Updated: {$updated}, skipped: {$skipped}.");

        return self::SUCCESS;
    }

    private function resolveStoragePath(ImageMetadata $row): ?string
    {
        if (! empty($row->filename)) {
            return 'user-items/'.$row->filename;
        }

        $filePath = (string) ($row->file_path ?? '');
        if ($filePath === '') {
            return null;
        }

        $filePath = ltrim(str_replace('/storage/', '', $filePath), '/');

        return $filePath !== '' ? $filePath : null;
    }
}
