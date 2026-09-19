<section id="testimonials" class="testimonials section{{ $testimonials->isEmpty() ? ' is-empty' : '' }}">
  <div class="container section-title" data-aos="fade-up">
    <h2 data-en="Testimonials" data-fa="نظرات">Testimonials</h2>
    <p data-en="What clients and colleagues say about my work and professional approach." data-fa="نظرات مشتریان و همکاران درباره کار و رویکرد حرفه‌ای من.">
      What clients and colleagues say about my work and professional approach.
    </p>
  </div>
  <div class="container" data-aos="fade-up" data-aos-delay="100">
    <div class="testimonials-slider swiper init-swiper" id="testimonials-carousel">
      <script type="application/json" class="swiper-config">
        {
          "loop": true,
          "speed": 550,
          "grabCursor": true,
          "simulateTouch": true,
          "watchOverflow": true,
          "observer": true,
          "observeParents": true,
          "autoHeight": false,
          "slidesPerView": 3,
          "spaceBetween": 24,
          "autoplay": { "delay": 5000, "pauseOnMouseEnter": true, "disableOnInteraction": false },
          "pagination": { "el": ".testimonials-dots", "clickable": true },
          "navigation": { "nextEl": ".testimonials-next", "prevEl": ".testimonials-prev" },
          "breakpoints": {
            "0": { "slidesPerView": 1, "spaceBetween": 16 },
            "768": { "slidesPerView": 2, "spaceBetween": 20 },
            "1200": { "slidesPerView": 3, "spaceBetween": 24 }
          }
        }
      </script>
      <div class="swiper-wrapper">
        @foreach ($testimonials as $testimonial)
          <div class="swiper-slide">
            <blockquote class="testimonial-card">
              <p>
                <span data-en="{{ $testimonial->quote_en }}" data-fa="{{ $testimonial->quote_fa ?: $testimonial->quote_en }}">{{ $testimonial->quote_en }}</span>
              </p>
              <footer>
                <img src="{{ $testimonial->avatarUrl() }}" loading="lazy" class="testimonial-img" alt="{{ $testimonial->author_name }}" width="52" height="52" />
                <cite>
                  @if ($testimonial->role_en || $testimonial->role_fa)
                    <strong class="testimonial-role" data-en="{{ $testimonial->role_en }}" data-fa="{{ $testimonial->role_fa ?: $testimonial->role_en }}">{{ $testimonial->role_en }}</strong>
                  @endif
                  @if ($testimonial->company_en || $testimonial->company_fa)
                    <span class="testimonial-company" data-en="{{ $testimonial->company_en }}" data-fa="{{ $testimonial->company_fa ?: $testimonial->company_en }}">{{ $testimonial->company_en }}</span>
                  @endif
                  <span class="testimonial-author" data-en="{{ $testimonial->author_name }}" data-fa="{{ $testimonial->author_name }}">{{ $testimonial->author_name }}</span>
                </cite>
              </footer>
            </blockquote>
          </div>
        @endforeach
      </div>
      <div class="testimonials-nav">
        <button type="button" class="testimonials-prev" aria-label="Previous testimonial" data-en-aria-label="Previous testimonial" data-fa-aria-label="نظر قبلی">
          <i class="bi bi-chevron-left" aria-hidden="true"></i>
        </button>
        <div class="testimonials-dots swiper-pagination"></div>
        <button type="button" class="testimonials-next" aria-label="Next testimonial" data-en-aria-label="Next testimonial" data-fa-aria-label="نظر بعدی">
          <i class="bi bi-chevron-right" aria-hidden="true"></i>
        </button>
      </div>
    </div>
  </div>
</section>
