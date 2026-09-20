<?php

use App\Models\HomepageContent;
use App\Services\HomepageContentCatalog;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $section = HomepageContent::query()->where('key', 'resume')->first();

        if (! $section) {
            return;
        }

        $defaults = app(HomepageContentCatalog::class)->resumeDefaults();
        $current = is_array($section->content) ? $section->content : [];
        $education = is_array($current['education'] ?? null) ? $current['education'] : [];
        $experience = is_array($current['experience'] ?? null) ? $current['experience'] : [];
        $hasHighlights = collect($experience)->every(
            static fn (mixed $item): bool => is_array($item) && ! empty($item['highlights'])
        );

        // The first CMS seed contained only a shortened timeline. Restore the
        // canonical bilingual copy once, while leaving a complete edited
        // resume untouched on future deployments.
        if (count($education) < count($defaults['education'])
            || count($experience) < count($defaults['experience'])
            || ! $hasHighlights) {
            $section->forceFill(['content' => $defaults])->save();
        }
    }

    public function down(): void
    {
        // The migration repairs content and intentionally has no destructive rollback.
    }
};
