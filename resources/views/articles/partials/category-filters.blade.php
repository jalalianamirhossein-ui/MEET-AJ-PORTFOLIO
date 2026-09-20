@php
    $brandFilters = \App\Models\Tag::query()->whereIn('slug', \App\Models\Tag::BRAND_FILTERS)->get();
    $filterGroups = $brandFilters
        ->map(fn ($tag): array => ['slug' => $tag->slug, 'en' => $tag->displayName(), 'fa' => $tag->displayName(), 'color' => $tag->accentColor(), 'topic' => $tag->slug, 'sort_order' => 0, 'id' => $tag->id])
        ->sortBy(fn ($filter) => array_search($filter['slug'], \App\Models\Tag::BRAND_FILTERS, true))->values();
    $legacyFilterGroups = collect($filterCategories ?? [])
        ->groupBy(fn ($category) => strtolower((string) $category->slug))
        ->map(function ($group): array {
            $english = $group->firstWhere('language', 'en') ?? $group->first();
            $persian = $group->firstWhere('language', 'fa');

            return [
                'slug' => strtolower((string) $english->slug),
                'en' => (string) $english->name,
                'fa' => (string) ($persian?->name ?? $english->name),
                'color' => $english->accentColor(),
                'topic' => $english->topicKey(),
                'sort_order' => (int) $group->min('sort_order'),
                'id' => (int) $group->min('id'),
            ];
        })
        ->sortBy(fn (array $filter): array => [$filter['sort_order'], $filter['id']])
        ->values();
@endphp
            <div class="article-filter-bar">
              <span
                class="article-filter-label"
                id="article-filter-label"
                data-en="Category"
                data-fa="دسته‌بندی"
                >Category</span
              >
              <ul
                class="portfolio-filters isotope-filters article-chip-row"
                role="group"
                aria-labelledby="article-filter-label"
              >
                <li>
                  <button
                    type="button"
                    data-filter="*"
                    data-topic="all"
                    class="article-chip filter-active"
                    aria-pressed="true"
                    style="--topic: var(--color-primary, #2563eb);"
                  >
                    <span class="article-chip-label" data-en="All articles" data-fa="همه مقالات">All articles</span>
                  </button>
                </li>
                @foreach ($filterGroups as $filter)
                  <li>
                    <button
                      type="button"
                      data-filter=".filter-{{ $filter['slug'] }}"
                      data-topic="{{ $filter['topic'] }}"
                      class="article-chip"
                      aria-pressed="false"
                      style="--topic: {{ $filter['color'] }};"
                    >
                      <span class="article-chip-label" data-en="{{ $filter['en'] }}" data-fa="{{ $filter['fa'] }}">{{ $filter['en'] }}</span>
                    </button>
                  </li>
                @endforeach
              </ul>
            </div>
