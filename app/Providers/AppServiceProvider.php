<?php

namespace App\Providers;

use App\Models\Article;
use App\Models\Category;
use App\Models\Request as ContactRequest;
use App\Models\Service;
use App\Models\Tag;
use App\Models\User;
use App\Policies\ArticlePolicy;
use App\Policies\CategoryPolicy;
use App\Policies\RequestPolicy;
use App\Policies\ServicePolicy;
use App\Policies\TagPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Gate::policy(Article::class, ArticlePolicy::class);
        Gate::policy(Category::class, CategoryPolicy::class);
        Gate::policy(Tag::class, TagPolicy::class);
        Gate::policy(ContactRequest::class, RequestPolicy::class);
        Gate::policy(Service::class, ServicePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
    }
}
