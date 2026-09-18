@php
    $tagTopics = [
        'devops' => 'devops',
        'linux' => 'linux',
        'microsoft' => 'microsoft',
        'windows-server' => 'microsoft',
        'mikrotik' => 'mikrotik',
        'networking' => 'networking',
        'security' => 'security',
        'vmware' => 'vmware',
    ];
    $showCategoryFilters = ! empty($showCategoryFilters);
    $isSearching = ! empty($searching);
@endphp
        <div class="article-library-toolbar">
          <div class="article-filter-panel">
          <form class="article-search" method="get" action="{{ url('/articles') }}" role="search">
            <label class="article-search-label" for="article-q" data-en="Search articles" data-fa="جستجوی مقالات">Search articles</label>
            <div class="article-search-row">
              <span class="article-search-field">
                <i class="bi bi-search" aria-hidden="true"></i>
                <input
                  id="article-q"
                  type="search"
                  name="q"
                  value="{{ $q }}"
                  maxlength="120"
                  autocomplete="off"
                  enterkeyhint="search"
                  data-en-placeholder="Title, topic, or technology"
                  data-fa-placeholder="عنوان، موضوع یا فناوری"
                  placeholder="Title, topic, or technology"
                />
              </span>
              @if ($tagSlug !== '')
                <input type="hidden" name="tag" value="{{ $tagSlug }}" />
              @endif
              <button class="btn btn-primary" type="submit" data-en="Search" data-fa="جستجو">Search</button>
            </div>
          </form>
          @if (isset($tags) && $tags->isNotEmpty())
          <nav class="article-tag-nav" aria-label="Filter by tag" data-en-aria-label="Filter by tag" data-fa-aria-label="فیلتر بر اساس برچسب">
              <span class="article-tag-nav-label" data-en="Tags" data-fa="برچسب‌ها">Tags</span>
              <div class="article-chip-row">
              <a
                class="article-tag article-chip{{ $tagSlug === '' ? ' is-active' : '' }}"
                href="{{ url('/articles') }}"
                @if ($tagSlug === '') aria-current="page" @endif
              ><span class="article-chip-label" data-en="All tags" data-fa="همه برچسب‌ها">All tags</span></a>
              @foreach ($tags as $tag)
                <a
                  class="article-tag article-chip{{ $tagSlug === $tag->slug ? ' is-active' : '' }}"
                  data-topic="{{ $tagTopics[$tag->slug] ?? 'other' }}"
                  style="--topic: {{ \App\Models\Category::accentColorForSlug($tagTopics[$tag->slug] ?? 'others') }};"
                  href="{{ url('/articles') }}?tag={{ urlencode($tag->slug) }}"
                  @if ($tagSlug === $tag->slug) aria-current="page" @endif
                ><span class="article-chip-label">{{ $tag->name }}</span></a>
              @endforeach
              </div>
            </nav>
          @endif
          @if ($showCategoryFilters)
            @include('articles.partials.category-filters')
          @endif
          @if ($isSearching)
          <div class="article-search-status">
            <p class="article-search-summary" role="status">
              @if ($activeTag && $q === '')
                <span data-en="Articles tagged {{ $activeTag->name }}" data-fa="مقالات با برچسب {{ $activeTag->name }}">Articles tagged {{ $activeTag->name }}</span>
              @elseif ($q !== '')
                <span data-en="{{ $results->total() }} result(s) for “{{ $q }}”" data-fa="{{ $results->total() }} نتیجه برای «{{ $q }}»">{{ $results->total() }} result(s) for “{{ $q }}”</span>
              @else
                <span data-en="{{ $results->total() }} matching article(s)" data-fa="{{ $results->total() }} مقاله مطابق">{{ $results->total() }} matching article(s)</span>
              @endif
            </p>
            @if ($q !== '' || $tagSlug !== '')
              <a class="article-search-clear" href="{{ url('/articles') }}" data-en="Clear search" data-fa="پاک کردن جستجو">Clear search</a>
            @endif
          </div>
          @endif
          </div>
        </div>
