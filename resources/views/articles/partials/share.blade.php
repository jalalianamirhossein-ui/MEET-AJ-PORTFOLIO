                <div
                  class="article-share"
                  role="group"
                  aria-label="Share this article"
                  data-en-aria-label="Share this article"
                  data-fa-aria-label="اشتراک‌گذاری این مقاله"
                >
                  <p class="article-share-label" data-en="Share" data-fa="اشتراک‌گذاری">Share</p>
                  <ul class="article-share-actions">
                    <li>
                      <a
                        class="article-share-btn"
                        href="{{ $share['linkedin'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Share on LinkedIn"
                        data-en-aria-label="Share on LinkedIn"
                        data-fa-aria-label="اشتراک‌گذاری در لینکدین"
                      ><i class="bi bi-linkedin" aria-hidden="true"></i></a>
                    </li>
                    <li>
                      <a
                        class="article-share-btn"
                        href="{{ $share['twitter'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Share on X"
                        data-en-aria-label="Share on X"
                        data-fa-aria-label="اشتراک‌گذاری در X"
                      ><i class="bi bi-twitter-x" aria-hidden="true"></i></a>
                    </li>
                    <li>
                      <a
                        class="article-share-btn"
                        href="{{ $share['telegram'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Share on Telegram"
                        data-en-aria-label="Share on Telegram"
                        data-fa-aria-label="اشتراک‌گذاری در تلگرام"
                      ><i class="bi bi-telegram" aria-hidden="true"></i></a>
                    </li>
                    <li>
                      <a
                        class="article-share-btn"
                        href="{{ $share['whatsapp'] }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Share on WhatsApp"
                        data-en-aria-label="Share on WhatsApp"
                        data-fa-aria-label="اشتراک‌گذاری در واتساپ"
                      ><i class="bi bi-whatsapp" aria-hidden="true"></i></a>
                    </li>
                    <li>
                      <button
                        type="button"
                        class="article-share-btn article-copy-link"
                        data-copy-link="{{ $share['url'] }}"
                        aria-label="Copy link"
                        data-en-aria-label="Copy link"
                        data-fa-aria-label="کپی لینک"
                      ><i class="bi bi-link-45deg" aria-hidden="true"></i></button>
                    </li>
                  </ul>
                  <p class="article-share-status" role="status" aria-live="polite" hidden></p>
                </div>
