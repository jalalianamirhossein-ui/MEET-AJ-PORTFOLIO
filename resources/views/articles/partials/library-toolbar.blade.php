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
@endphp
        <div class="article-library-toolbar container">
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
              @if ($q !== '')
                <a class="article-search-reset" href="{{ url('/articles') }}{{ $tagSlug !== '' ? '?tag='.urlencode($tagSlug) : '' }}" data-en="Reset" data-fa="بازنشانی">Reset</a>
              @endif
            </div>
          </form>
          @if (isset($tags) && $tags->isNotEmpty())
          <nav class="article-tag-nav" aria-label="Filter by tag">
              <span class="article-tag-nav-label" data-en="Tags" data-fa="برچسب‌ها">Tags</span>
              <div class="article-chip-row">
              <a
                class="article-tag{{ $tagSlug === '' ? ' is-active' : '' }}"
                href="{{ url('/articles') }}"
                @if ($tagSlug === '') aria-current="page" @endif
                data-en="All tags"
                data-fa="همه برچسب‌ها"
              >All tags</a>
              @foreach ($tags as $tag)
                <a
                  class="article-tag{{ $tagSlug === $tag->slug ? ' is-active' : '' }}"
                  data-topic="{{ $tagTopics[$tag->slug] ?? 'other' }}"
                  href="{{ url('/articles') }}?tag={{ urlencode($tag->slug) }}"
                  @if ($tagSlug === $tag->slug) aria-current="page" @endif
                >{{ $tag->name }}</a>
              @endforeach
              </div>
            </nav>
          @endif
        </div>
