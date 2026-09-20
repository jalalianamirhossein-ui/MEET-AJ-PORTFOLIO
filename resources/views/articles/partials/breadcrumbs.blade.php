              @php
                  $crumbCategoryEn = $article->categoryLabelEn();
                  $crumbCategoryFa = $article->categoryLabelFa();
              @endphp
              <nav
                class="article-breadcrumb"
                aria-label="Breadcrumb"
                data-en-aria-label="Breadcrumb"
                data-fa-aria-label="مسیر صفحه"
              >
                <ol>
                  <li><a href="/" data-en="Home" data-fa="صفحه اصلی">Home</a></li>
                  <li><a href="/articles" data-en="Articles" data-fa="مقالات">Articles</a></li>
                  @if ($crumbCategoryEn)
                    <li aria-current="page">
                      <span data-en="{{ $crumbCategoryEn }}" data-fa="{{ $crumbCategoryFa }}">{{ $crumbCategoryEn }}</span>
                    </li>
                  @endif
                </ol>
              </nav>
