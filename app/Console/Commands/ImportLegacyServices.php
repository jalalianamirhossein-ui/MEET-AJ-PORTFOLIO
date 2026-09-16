<?php

namespace App\Console\Commands;

use App\Models\Service;
use App\Services\LegacyServiceImporter;
use Illuminate\Console\Command;

class ImportLegacyServices extends Command
{
    protected $signature = 'services:import-legacy {--dry-run : Report without writing} {--refresh : Replace previously imported services}';

    protected $description = 'Import the six original HTML service pages into the CMS';

    public function handle(LegacyServiceImporter $importer): int
    {
        $dryRun = (bool) $this->option('dry-run');
        if ($this->option('refresh') && ! $dryRun) {
            Service::query()->delete();
            $this->warn('Cleared previously imported services.');
        }
        $result = $importer->import($dryRun);
        $this->table(['slug', 'status'], array_map(fn ($row) => [$row['slug'], $row['status']], $result['report']));
        $this->info(($dryRun ? 'Dry run' : 'Imported').": {$result['imported']} imported, {$result['skipped']} skipped.");

        return self::SUCCESS;
    }
}
