<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\RequestResource;
use App\Filament\Resources\TagResource;
use App\Models\Article;
use App\Models\Category;
use App\Models\Request as ContactRequest;
use App\Models\Tag;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CmsStatsOverview extends StatsOverviewWidget
{
    protected static ?int $sort = 1;

    protected ?string $heading = 'Portfolio snapshot';

    protected ?string $description = 'Counts from the live CMS database. German articles stay draft-only.';

    protected function getStats(): array
    {
        $stats = [
            Stat::make('Articles', Article::query()->count())
                ->description('All languages, including drafts')
                ->url(ArticleResource::getUrl()),
            Stat::make('Published', Article::query()->published()->count())
                ->description('Visible on the public site')
                ->color('success')
                ->url(ArticleResource::getUrl()),
            Stat::make('Drafts', Article::query()->where('status', 'draft')->count())
                ->description('Not publicly reachable')
                ->color('gray')
                ->url(ArticleResource::getUrl()),
            Stat::make('Categories', Category::query()->count())
                ->url(CategoryResource::getUrl()),
            Stat::make('Tags', Tag::query()->count())
                ->url(TagResource::getUrl()),
        ];

        if (auth()->user()?->isAdmin()) {
            $stats[] = Stat::make('Requests', ContactRequest::query()->count())
                ->description('All inbound messages')
                ->url(RequestResource::getUrl());
            $stats[] = Stat::make('New requests', ContactRequest::query()->where('status', 'new')->count())
                ->description('Unread inbound contact')
                ->color('danger')
                ->url(RequestResource::getUrl());
        }

        return $stats;
    }
}
