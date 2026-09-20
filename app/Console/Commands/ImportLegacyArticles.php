<?php

namespace App\Console\Commands;

use App\Services\LegacyArticleImporter;
use Illuminate\Console\Command;

class ImportLegacyArticles extends Command
{
    protected $signature = 'articles:import-legacy {--dry-run : Report without writing} {--refresh : Replace previously imported legacy articles} {--update-existing : Update existing articles when their source HTML changed} {--slug=* : Limit the import or update to one or more article slugs}';

    protected $description = 'Import legacy HTML articles into the CMS';

    public function handle(LegacyArticleImporter $importer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $updateExisting = (bool) $this->option('update-existing');
        $onlySlugs = array_values(array_filter(array_map('strval', (array) $this->option('slug'))));
        if ($this->option('refresh') && ! $dryRun) {
            \App\Models\ArticleRedirect::query()->delete();
            \App\Models\Article::query()->delete();
            $this->warn('Cleared previously imported articles and redirects.');
        }
        $result = $importer->import($dryRun, $updateExisting, $onlySlugs);
        $this->table(['slug', 'status'], array_map(fn ($row) => [$row['slug'], $row['status']], $result['report']));
        $this->info(($dryRun ? 'Dry run' : 'Imported').": {$result['imported']} imported, {$result['updated']} updated, {$result['skipped']} skipped, {$result['redirects']} redirects.");

        return self::SUCCESS;
    }
}
