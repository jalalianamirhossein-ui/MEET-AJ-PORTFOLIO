                @if ($related->isNotEmpty())
                  <section class="article-related related-articles" aria-labelledby="related-heading">
                    <h2 id="related-heading" data-en="Related Articles" data-fa="مقالات مرتبط">Related Articles</h2>
                    <div class="article-related-grid">
                      @foreach ($related as $item)
                        @include('components.article-card', ['article' => $item, 'variant' => 'related'])
                      @endforeach
                    </div>
                  </section>
                @endif
