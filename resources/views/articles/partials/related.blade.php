                @if ($related->isNotEmpty())
                  <section class="article-related" aria-labelledby="related-heading">
                    <h2 id="related-heading" data-en="Related Articles" data-fa="مقالات مرتبط">Related Articles</h2>
                    <ol>
                      @foreach ($related as $item)
                        <li>
                          <a href="{{ $item->path() }}">{{ $item->title }}</a>
                          @if ($item->category)
                            <span class="article-related-meta">{{ $item->category->name }}</span>
                          @endif
                        </li>
                      @endforeach
                    </ol>
                  </section>
                @endif
