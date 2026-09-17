@php
    $topics = [
        'filter-linux' => 'linux',
        'filter-microsoft' => 'microsoft',
        'filter-mikrotik' => 'mikrotik',
        'filter-vmware' => 'vmware',
        'filter-others' => 'other',
    ];
    $topic = $topics[$article->filterClass()] ?? 'other';
    $categoryEn = $article->categoryLabelEn();
    $categoryFa = $article->categoryLabelFa();
    $titleEn = data_get($article->presentation, 'card_title_en') ?: $article->title;
    $titleFa = data_get($article->presentation, 'card_title_fa') ?: $article->title;
    $excerptEn = data_get($article->presentation, 'card_excerpt_en') ?: $article->excerpt;
    $excerptFa = data_get($article->presentation, 'card_excerpt_fa') ?: $excerptEn;
    $cardTags = $article->relationLoaded('tags') ? $article->tags->take(3) : collect();
    $variant = $variant ?? 'library';
    $isRelated = $variant === 'related';
@endphp
              @if ($isRelated)
                <article class="article-teaser article-teaser--related" data-topic="{{ $topic }}">
                  <a class="article-teaser-link" href="{{ $article->path() }}">
                    <div class="article-teaser-media">
                      <img
                        src="{{ $article->thumbnailUrl() }}"
                        width="500"
                        height="500"
                        decoding="async"
                        loading="lazy"
                        class="img-fluid"
                        alt="{{ data_get($article->presentation, 'image_alt') ?: $article->title }}"
                      />
                    </div>
                    <div class="portfolio-info">
                      @if ($categoryEn)
                        <p class="article-teaser-category"
                          data-en="{{ $categoryEn }}"
                          data-fa="{{ $categoryFa }}"
                        >{{ $categoryEn }}</p>
                      @endif
                      <h3 class="article-teaser-title"
                        data-en="{{ $titleEn }}"
                        data-fa="{{ $titleFa }}"
                      >{{ $titleEn }}</h3>
                      @if ($article->published_at)
                        <p class="article-teaser-meta">
                          <i class="bi bi-calendar3" aria-hidden="true"></i>
                          <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('M j, Y') }}</time>
                        </p>
                      @endif
                    </div>
                  </a>
                </article>
              @else
              <div
                class="col-lg-4 col-md-6 portfolio-item isotope-item article-grid-item {{ $article->filterClass() }}"
                data-topic="{{ $topic }}"
              >
                <article class="article-teaser" data-topic="{{ $topic }}">
                  <div class="portfolio-content article-teaser-media">
                    <img
                      src="{{ $article->thumbnailUrl() }}"
                      width="500"
                      height="500"
                      decoding="async"
                      loading="lazy"
                      class="img-fluid"
                      alt="{{ data_get($article->presentation, 'image_alt') ?: $article->title }}"
                    />
                    <a
                      href="{{ $article->galleryUrl() }}"
                      title="{{ $article->title }}"
                      data-gallery="portfolio-gallery-{{ $article->slug }}"
                      class="glightbox preview-link"
                      aria-label="{{ 'Preview image: '.$titleEn }}"
                      data-en-aria-label="{{ 'Preview image: '.$titleEn }}"
                      data-fa-aria-label="{{ 'پیش‌نمایش تصویر: '.$titleFa }}"
                      ><i class="bi bi-zoom-in" aria-hidden="true"></i
                    ></a>
                  </div>
                  <div class="portfolio-info">
                    @if ($categoryEn)
                      <p class="article-teaser-category"
                        data-en="{{ $categoryEn }}"
                        data-fa="{{ $categoryFa }}"
                      >{{ $categoryEn }}</p>
                    @endif
                    <h3 class="article-teaser-title">
                      <a
                        href="{{ $article->path() }}"
                        data-en="{{ $titleEn }}"
                        data-fa="{{ $titleFa }}"
                      >{{ $titleEn }}</a>
                    </h3>
                    @if ($excerptEn)
                      <p class="article-teaser-excerpt"
                        data-en="{{ $excerptEn }}"
                        data-fa="{{ $excerptFa }}"
                      >{{ $excerptEn }}</p>
                    @endif
                    @if ($cardTags->isNotEmpty())
                      <ul class="article-teaser-tags">
                        @foreach ($cardTags as $tag)
                          <li><a href="{{ $tag->path() }}">{{ $tag->name }}</a></li>
                        @endforeach
                      </ul>
                    @endif
                    <div class="article-teaser-foot">
                      @if ($article->published_at)
                        <p class="article-teaser-meta">
                          <i class="bi bi-calendar3" aria-hidden="true"></i>
                          <time datetime="{{ $article->published_at->toDateString() }}">{{ $article->published_at->format('M j, Y') }}</time>
                        </p>
                      @endif
                      <div class="portfolio-links">
                        <a
                          href="{{ $article->path() }}"
                          class="article-teaser-cta"
                          data-en="Read article"
                          data-fa="خواندن مقاله"
                        >Read article</a>
                      </div>
                    </div>
                  </div>
                </article>
              </div>
              @endif
