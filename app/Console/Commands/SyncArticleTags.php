<?php

namespace App\Console\Commands;

use App\Services\ArticleTagAssigner;
use Illuminate\Console\Command;

class SyncArticleTags extends Command
{
    protected $signature = 'articles:sync-tags';

    protected $description = 'Create catalog tags and attach them from existing article titles, categories, and slugs';

    public function handle(ArticleTagAssigner $assigner): int
    {
        $count = $assigner->syncPublishedLibrary();
        $this->info('Synced tags for '.$count.' article(s).');

        return self::SUCCESS;
    }
}
