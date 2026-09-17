        <div class="container article-search-results">
          <p class="article-search-summary" role="status">
            @if ($activeTag && $q === '')
              <span data-en="Articles tagged {{ $activeTag->name }}" data-fa="مقالات با برچسب {{ $activeTag->name }}">Articles tagged {{ $activeTag->name }}</span>
            @elseif ($q !== '')
              <span data-en="{{ $results->total() }} result(s) for “{{ $q }}”" data-fa="{{ $results->total() }} نتیجه برای «{{ $q }}»">{{ $results->total() }} result(s) for “{{ $q }}”</span>
            @else
              <span data-en="{{ $results->total() }} matching article(s)" data-fa="{{ $results->total() }} مقاله مطابق">{{ $results->total() }} matching article(s)</span>
            @endif
            <a class="article-search-clear" href="{{ url('/articles') }}" data-en="Clear" data-fa="پاک کردن">Clear</a>
          </p>
          @if ($results->isEmpty())
            <p class="article-search-empty" data-en="No published articles match this search." data-fa="مقاله منتشرشده‌ای با این جستجو پیدا نشد.">No published articles match this search.</p>
          @else
            <ol class="article-result-list">
              @foreach ($results as $article)
                <li>
                  <a class="article-result-title" href="{{ $article->path() }}">{{ $article->title }}</a>
                  @if ($article->category)
                    <p class="article-result-meta">{{ $article->category->name }}</p>
                  @endif
                  @if ($article->excerpt)
                    <p class="article-result-excerpt">{{ $article->excerpt }}</p>
                  @endif
                  @if ($article->tags->isNotEmpty())
                    <ul class="article-tags">
                      @foreach ($article->tags as $tag)
                        <li><a href="{{ $tag->path() }}">{{ $tag->name }}</a></li>
                      @endforeach
                    </ul>
                  @endif
                </li>
              @endforeach
            </ol>
            @if ($results->hasPages())
              <nav class="article-pagination" aria-label="Search results pages">
                @if ($results->onFirstPage())
                  <span class="article-page-link is-disabled" aria-disabled="true" data-en="Previous" data-fa="قبلی">Previous</span>
                @else
                  <a class="article-page-link" href="{{ $results->previousPageUrl() }}" data-en="Previous" data-fa="قبلی">Previous</a>
                @endif
                <ol>
                  @foreach ($results->getUrlRange(1, $results->lastPage()) as $page => $url)
                    <li>
                      @if ($page === $results->currentPage())
                        <span class="article-page-link is-current" aria-current="page">{{ $page }}</span>
                      @else
                        <a class="article-page-link" href="{{ $url }}">{{ $page }}</a>
                      @endif
                    </li>
                  @endforeach
                </ol>
                @if ($results->hasMorePages())
                  <a class="article-page-link" href="{{ $results->nextPageUrl() }}" data-en="Next" data-fa="بعدی">Next</a>
                @else
                  <span class="article-page-link is-disabled" aria-disabled="true" data-en="Next" data-fa="بعدی">Next</span>
                @endif
              </nav>
            @endif
          @endif
        </div>
