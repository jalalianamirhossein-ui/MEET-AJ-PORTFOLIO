<?php

namespace App\Console\Commands;

use App\Services\LegacyArticleImporter;
use Illuminate\Console\Command;

class ImportLegacyArticles extends Command
{
    protected $signature = 'articles:import-legacy {--dry-run : Report without writing} {--refresh : Replace previously imported legacy articles}';

    protected $description = 'Import legacy HTML articles into the CMS';

    public function handle(LegacyArticleImporter $importer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        if ($this->option('refresh') && ! $dryRun) {
            \App\Models\ArticleRedirect::query()->delete();
            \App\Models\Article::query()->delete();
            $this->warn('Cleared previously imported articles and redirects.');
        }
        $result = $importer->import($dryRun);
        $this->table(['slug', 'status'], array_map(fn ($row) => [$row['slug'], $row['status']], $result['report']));
        $this->info(($dryRun ? 'Dry run' : 'Imported').": {$result['imported']} imported, {$result['skipped']} skipped, {$result['redirects']} redirects.");

        return self::SUCCESS;
    }
}
