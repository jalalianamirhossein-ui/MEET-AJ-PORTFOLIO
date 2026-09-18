<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\CategoryResource;
use App\Filament\Resources\ContactRequestResource;
use App\Filament\Resources\RequestResource;
use App\Filament\Resources\ServiceRequestResource;
use App\Filament\Resources\TagResource;
use App\Models\Article;
use App\Models\Category;
use App\Models\Request as ContactRequest;
use App\Models\Tag;
use Filament\Support\Icons\Heroicon;
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
                ->icon(Heroicon::OutlinedDocumentText)
                ->url(ArticleResource::getUrl()),
            Stat::make('Published', Article::query()->published()->count())
                ->description('Visible on the public site')
                ->icon(Heroicon::OutlinedCheckBadge)
                ->color('success')
                ->url(ArticleResource::getUrl()),
            Stat::make('Drafts', Article::query()->where('status', 'draft')->count())
                ->description('Not publicly reachable')
                ->icon(Heroicon::OutlinedPencilSquare)
                ->color('gray')
                ->url(ArticleResource::getUrl()),
            Stat::make('Categories', Category::query()->count())
                ->icon(Heroicon::OutlinedRectangleStack)
                ->url(CategoryResource::getUrl()),
            Stat::make('Tags', Tag::query()->count())
                ->icon(Heroicon::OutlinedTag)
                ->url(TagResource::getUrl()),
        ];

        if (auth()->user()?->isAdmin()) {
            $stats[] = Stat::make('Contact requests', ContactRequest::query()->contacts()->count())
                ->description('Homepage contact form')
                ->icon(Heroicon::OutlinedEnvelope)
                ->color('info')
                ->url(ContactRequestResource::getUrl());
            $stats[] = Stat::make('Service requests', ContactRequest::query()->serviceRequests()->count())
                ->description('Service quote forms')
                ->icon(Heroicon::OutlinedBriefcase)
                ->url(ServiceRequestResource::getUrl());
            $stats[] = Stat::make('New / unread', ContactRequest::query()->unread()->count())
                ->description('Awaiting a first response')
                ->icon(Heroicon::OutlinedBellAlert)
                ->color('danger')
                ->url(RequestResource::getUrl());
        }

        return $stats;
    }
}
