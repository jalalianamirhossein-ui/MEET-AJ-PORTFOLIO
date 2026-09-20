<?php

namespace Database\Seeders;

use App\Services\LegacyArticleImporter;
use App\Services\LegacyServiceImporter;
use App\Services\LegacySitePublisher;
use App\Services\HomepageContentCatalog;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        app(LegacySitePublisher::class)->buildViews();
        app(HomepageContentCatalog::class)->sync();
        app(LegacyArticleImporter::class)->import(false);
        app(LegacyServiceImporter::class)->import(false);
    }
}
