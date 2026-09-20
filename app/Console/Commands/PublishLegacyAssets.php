<?php

namespace App\Console\Commands;

use App\Services\LegacySitePublisher;
use Illuminate\Console\Command;

class PublishLegacyAssets extends Command
{
    protected $signature = 'site:publish-assets {--views : Also rebuild Blade views from original HTML}';

    protected $description = 'Copy allowlisted original assets into public/ and optionally rebuild Blade views';

    public function handle(LegacySitePublisher $publisher): int
    {
        $copied = $publisher->publishAssets();
        $this->info('Published '.count($copied).' public asset files.');
        if ($this->option('views')) {
            $views = $publisher->buildViews();
            foreach ($views as $view) {
                $this->line($view);
            }
            $this->info('Wrote '.count($views).' Blade views.');
        }

        return self::SUCCESS;
    }
}
