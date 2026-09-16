              <div
                class="col-lg-4 col-md-6 portfolio-item isotope-item {{ $article->filterClass() }}"
              >
                <article class="article-teaser">
                <div class="portfolio-content">
                  <img
                    src="{{ $article->thumbnailUrl() }}"
                    width="720"
                    height="720"
                    decoding="async"
                    loading="lazy"
                    class="img-fluid"
                    alt="{{ data_get($article->presentation, 'image_alt') ?: $article->title }}"
                  />
                </div>
                  <div class="portfolio-info">
                    @if (data_get($article->presentation, 'category_label_en') || $article->category)
                      <p class="article-teaser-category"
                        data-en="{{ data_get($article->presentation, 'category_label_en') ?: $article->category?->name }}"
                        data-fa="{{ data_get($article->presentation, 'category_label_fa') ?: data_get($article->presentation, 'category_label_en') ?: $article->category?->name }}"
                      >{{ data_get($article->presentation, 'category_label_en') ?: $article->category?->name }}</p>
                    @endif
                    <h4
                      data-en="{{ data_get($article->presentation, 'card_title_en') ?: $article->title }}"
                      data-fa="{{ data_get($article->presentation, 'card_title_fa') ?: $article->title }}"
                    >
                      {{ data_get($article->presentation, 'card_title_en') ?: $article->title }}
                    </h4>
                    <p class="article-teaser-excerpt"
                      data-en="{{ data_get($article->presentation, 'card_excerpt_en') ?: $article->excerpt }}"
                      data-fa="{{ data_get($article->presentation, 'card_excerpt_fa') ?: $article->excerpt }}"
                    >
                      {{ data_get($article->presentation, 'card_excerpt_en') ?: $article->excerpt }}
                    </p>
                    <div class="portfolio-links">
                      <a
                        href="{{ $article->galleryUrl() }}"
                        title="{{ $article->title }}"
                        data-gallery="portfolio-gallery-{{ $article->slug }}"
                        class="glightbox preview-link"
                        aria-label="{{ 'Preview image: ' . (data_get($article->presentation, 'card_title_en') ?: $article->title) }}"
                        ><i class="bi bi-zoom-in" aria-hidden="true"></i
                      ></a>
                      <a
                        href="{{ $article->path() }}"
                        class="article-teaser-cta"
                        data-en="More Details"
                        data-fa="جزئیات بیشتر"
                      >More Details</a>
                    </div>
                  </div>
                </article>
              </div>
