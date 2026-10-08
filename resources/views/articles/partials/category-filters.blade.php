@php
    $brandFilters = \App\Models\Tag::query()->whereIn('slug', \App\Models\Tag::BRAND_FILTERS)->get();
    $brandCategories = \App\Models\Category::query()
        ->whereIn('slug', array_merge(\App\Models\Tag::BRAND_FILTERS, ['others']))
        ->whereIn('language', ['en', 'fa'])
        ->orderByRaw("case when language = 'en' then 0 else 1 end")
        ->orderBy('id')
        ->get()
        ->groupBy('slug')
        ->map(fn ($categories) => $categories->first());
    $filterGroups = $brandFilters
        ->map(function ($tag) use ($brandCategories): array {
            $category = $brandCategories->get($tag->slug === 'other' ? 'others' : $tag->slug);
            $labelEn = $tag->displayName('en');
            $labelFa = $tag->displayName('fa');

            return ['slug' => $tag->slug, 'en' => $labelEn, 'fa' => $labelFa, 'color' => $category?->accentColor() ?? $tag->accentColor(), 'topic' => $tag->slug, 'sort_order' => 0, 'id' => $tag->id];
        })
        ->sortBy(fn ($filter) => array_search($filter['slug'], \App\Models\Tag::BRAND_FILTERS, true))->values();
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
