<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\ArticleResource;
use App\Filament\Resources\HomepageContentResource;
use App\Filament\Resources\RequestResource;
use Filament\Widgets\Widget;

class WorkspaceActions extends Widget
{
    protected static ?int $sort = 0;

    protected static bool $isLazy = false;

    protected int | string | array $columnSpan = ['default' => 'full'];

    protected string $view = 'filament.widgets.workspace-actions';

    protected function getViewData(): array
    {
        $actions = [];

        if (ArticleResource::canCreate()) {
            $actions[] = ['label' => 'Write an article', 'description' => 'Start a new technical draft', 'icon' => 'heroicon-o-pencil-square', 'url' => ArticleResource::getUrl('create')];
        }

        if (HomepageContentResource::canViewAny()) {
            $actions[] = ['label' => 'Manage homepage', 'description' => 'Update portfolio content', 'icon' => 'heroicon-o-home', 'url' => HomepageContentResource::getUrl()];
        }

        if (RequestResource::canViewAny()) {
            $actions[] = ['label' => 'Review requests', 'description' => 'Follow up on incoming enquiries', 'icon' => 'heroicon-o-inbox', 'url' => RequestResource::getUrl()];
        }

        return ['actions' => $actions];
    }
}
