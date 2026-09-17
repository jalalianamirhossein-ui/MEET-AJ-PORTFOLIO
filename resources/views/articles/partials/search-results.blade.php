        <div class="container article-search-results article-result-list">
          @if ($results->isEmpty())
            <div class="article-search-empty">
              <i class="bi bi-search" aria-hidden="true"></i>
              <p class="article-search-empty-title" data-en="No articles found" data-fa="مقاله‌ای پیدا نشد">No articles found</p>
              <p class="article-search-empty-hint" data-en="Try another keyword, or open the full library." data-fa="واژه دیگری را امتحان کنید یا همه مقالات را ببینید.">Try another keyword, or open the full library.</p>
              <a class="btn btn-primary" href="{{ url('/articles') }}" data-en="Show all articles" data-fa="نمایش همه مقالات">Show all articles</a>
            </div>
          @else
            <div class="article-grid article-grid-results">
              @foreach ($results as $article)
                @include('components.article-card', ['article' => $article])
              @endforeach
            </div>
            @if ($results->hasPages())
              <nav class="article-pagination" aria-label="Search results pages" data-en-aria-label="Search results pages" data-fa-aria-label="صفحات نتایج جستجو">
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
