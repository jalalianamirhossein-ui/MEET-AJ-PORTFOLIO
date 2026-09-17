                <div class="article-share" role="group" aria-label="Share this article">
                  <p class="article-share-label" data-en="Share" data-fa="اشتراک‌گذاری">Share</p>
                  <ul>
                    <li>
                      <a href="{{ $share['linkedin'] }}" target="_blank" rel="noopener noreferrer" data-en="LinkedIn" data-fa="لینکدین">LinkedIn</a>
                    </li>
                    <li>
                      <a href="{{ $share['whatsapp'] }}" target="_blank" rel="noopener noreferrer" data-en="WhatsApp" data-fa="واتساپ">WhatsApp</a>
                    </li>
                    <li>
                      <a href="{{ $share['telegram'] }}" target="_blank" rel="noopener noreferrer" data-en="Telegram" data-fa="تلگرام">Telegram</a>
                    </li>
                    <li>
                      <button type="button" class="article-copy-link" data-copy-link="{{ $share['url'] }}" data-en="Copy link" data-fa="کپی لینک">Copy link</button>
                    </li>
                  </ul>
                  <p class="article-share-status" role="status" aria-live="polite" hidden></p>
                </div>
