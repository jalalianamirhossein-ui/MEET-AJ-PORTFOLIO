@php
    $topic = $article->primaryFilterSlug();
    $accent = $article->accentColor();
    $accentStyle = '--topic: '.$accent.'; --article-primary: '.$accent.';';
    $categoryEn = $article->categoryLabelEn();
    $categoryFa = $article->categoryLabelFa();
    $titleEn = $article->englishCardTitle() ?: $article->title;
    $titleFa = data_get($article->presentation, 'card_title_fa') ?: $article->title;
    $excerptEn = $article->englishCardExcerpt() ?: $article->excerpt;
    $excerptFa = data_get($article->presentation, 'card_excerpt_fa') ?: data_get($article->presentation, 'excerpt_translations.fa') ?: $excerptEn;
    $cardTags = $article->relationLoaded('tags') ? $article->tags->take(3) : collect();
    $variant = $variant ?? 'library';
    $isRelated = $variant === 'related';
@endphp
              @if ($isRelated)
                <article class="article-teaser article-teaser--related" data-topic="{{ $topic }}" style="{{ $accentStyle }}">
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
                      <h3 class="article-teaser-title" @if(empty($pageLocale)) data-i18n-lock @else data-fa="{{ $titleFa }}" @endif data-en="{{ $titleEn }}">{{ ($pageLocale ?? null) === 'fa' ? $titleFa : $titleEn }}</h3>
                      <p class="article-teaser-meta">
                        @if ($article->published_at)
                          <span>
                            <i class="bi bi-calendar3" aria-hidden="true"></i>
                            <x-localized-date :date="$article->published_at" :locale="$pageLocale ?? null" />
                          </span>
                        @endif
                        <span>{{ $article->readingMinutes() }} <span data-en="min read" data-fa="دقیقه مطالعه">min read</span></span>
                      </p>
                    </div>
                  </a>
                </article>
              @else
              <div
                class="col-lg-4 col-md-6 portfolio-item isotope-item article-grid-item {{ $article->filterClass() }} filter-{{ $topic }} {{ $article->brandFilterClasses() }}"
                data-topic="{{ $topic }}"
                style="{{ $accentStyle }}"
                >
                <article class="article-teaser{{ $article->slug === 'truenas-zfs-enterprise' ? ' article-teaser--banner' : '' }}" data-topic="{{ $topic }}" style="{{ $accentStyle }}">
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
                      <a href="{{ $article->path() }}" data-en="{{ $titleEn }}" data-fa="{{ $titleFa }}">{{ ($pageLocale ?? null) === 'fa' ? $titleFa : $titleEn }}</a>
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
                          <x-localized-date :date="$article->published_at" :locale="$pageLocale ?? null" />
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
