            <div class="article-filter-bar">
              <span
                class="article-filter-label"
                id="article-filter-label"
                data-en="Category"
                data-fa="دسته‌بندی"
                >Category</span
              >
              <ul
                class="portfolio-filters isotope-filters article-chip-row"
                role="group"
                aria-labelledby="article-filter-label"
              >
                <li>
                  <button
                    type="button"
                    data-filter="*"
                    data-topic="all"
                    class="article-chip filter-active"
                    aria-pressed="true"
                    style="--topic: var(--color-primary, #2563eb);"
                  >
                    <span class="article-chip-label" data-en="All articles" data-fa="همه مقالات">All articles</span>
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    data-filter=".filter-microsoft"
                    data-topic="microsoft"
                    class="article-chip"
                    aria-pressed="false"
                    style="--topic: {{ \App\Models\Category::accentColorForSlug('microsoft') }};"
                  >
                    <span class="article-chip-label" data-en="Microsoft" data-fa="مایکروسافت">Microsoft</span>
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    data-filter=".filter-linux"
                    data-topic="linux"
                    class="article-chip"
                    aria-pressed="false"
                    style="--topic: {{ \App\Models\Category::accentColorForSlug('linux') }};"
                  >
                    <span class="article-chip-label" data-en="Linux" data-fa="لینوکس">Linux</span>
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    data-filter=".filter-mikrotik"
                    data-topic="mikrotik"
                    class="article-chip"
                    aria-pressed="false"
                    style="--topic: {{ \App\Models\Category::accentColorForSlug('mikrotik') }};"
                  >
                    <span class="article-chip-label" data-en="MikroTik" data-fa="میکروتیک">MikroTik</span>
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    data-filter=".filter-vmware"
                    data-topic="vmware"
                    class="article-chip"
                    aria-pressed="false"
                    style="--topic: {{ \App\Models\Category::accentColorForSlug('vmware') }};"
                  >
                    <span class="article-chip-label" data-en="VMware" data-fa="مجازی‌سازی">VMware</span>
                  </button>
                </li>
                <li>
                  <button
                    type="button"
                    data-filter=".filter-others"
                    data-topic="other"
                    class="article-chip"
                    aria-pressed="false"
                    style="--topic: {{ \App\Models\Category::accentColorForSlug('others') }};"
                  >
                    <span class="article-chip-label" data-en="Other" data-fa="سایر مقالات">Other</span>
                  </button>
                </li>
              </ul>
            </div>
