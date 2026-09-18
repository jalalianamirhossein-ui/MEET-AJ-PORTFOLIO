@php
  $titleFa = data_get($service->presentation, 'title_fa') ?: $service->title;
  $descFa = data_get($service->presentation, 'description_fa') ?: $service->description;
  $seoTitle = $service->seo_title ?: $service->title.' — Pricing & Scope';
  $seoDesc = $service->seo_description ?: $service->short_description;
  $ogTitle = $service->og_title ?: $seoTitle;
  $ogDesc = $service->og_description ?: $seoDesc;
  $canonical = $service->canonicalUrl();
  $origin = rtrim((string) config('app.url'), '/');
  $offer = $service->price_type === 'custom_quote' || $service->price === null
    ? ['@type' => 'Offer', 'availability' => 'https://schema.org/OnlineOnly', 'priceCurrency' => $service->price_currency ?: 'AED']
    : ['@type' => 'Offer', 'price' => (string) (0 + $service->price), 'priceCurrency' => $service->price_currency ?: 'AED'];
  $schema = [
    '@context' => 'https://schema.org',
    '@type' => 'Service',
    'name' => $service->title,
    'description' => $seoDesc,
    'url' => $canonical,
    'provider' => ['@type' => 'Person', 'name' => 'AmirHossein Jalalian', 'url' => $origin.'/'],
    'offers' => $offer,
  ];
  $exclusions = data_get($service->presentation, 'exclusions') ?: [];
  $deliverables = data_get($service->presentation, 'deliverables') ?: [];
  $sla = data_get($service->presentation, 'sla') ?: [];
  $addons = data_get($service->presentation, 'addons') ?: [];
  $ctaEn = data_get($service->presentation, 'cta_en') ?: 'Ready to get started? Contact us for a detailed proposal.';
  $ctaFa = data_get($service->presentation, 'cta_fa') ?: $ctaEn;
  $formSubject = data_get($service->presentation, 'form_subject') ?: $service->title.' Quote Request';
  $shortFa = data_get($service->presentation, 'short_description_fa') ?: $service->short_description;
  $hasNumericPrice = $service->price !== null && $service->price !== '' && $service->price_type !== 'custom_quote';
  $amountEn = $hasNumericPrice ? number_format((float) $service->price) : null;
  $amountFa = $amountEn ? strtr($amountEn, ['0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹', ',' => '٬']) : null;
  $currencyEn = $service->price_currency ?: 'AED';
@endphp
<!doctype html>
<html lang="en" dir="ltr">
  <head>
    <meta charset="UTF-8" />
    <meta name="csrf-token" content="{{ csrf_token() }}" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title data-en="{{ $seoTitle }}" data-fa="{{ data_get($service->presentation, 'seo_title_fa') ?: $seoTitle }}">{{ $seoTitle }}</title>
    <meta name="description" content="{{ $seoDesc }}" />
    <meta name="robots" content="index,follow" />
    <link rel="canonical" href="{{ $canonical }}" />
    <meta property="og:type" content="website" />
    <meta property="og:site_name" content="Meet AJ" />
    <meta property="og:title" content="{{ $ogTitle }}" />
    <meta property="og:description" content="{{ $ogDesc }}" />
    <meta property="og:url" content="{{ $canonical }}" />
    <meta property="og:image" content="{{ $origin }}/assets/img/hero-bg.jpg" />
    <meta name="twitter:card" content="summary_large_image" />
    <meta name="twitter:title" content="{{ $ogTitle }}" />
    <meta name="twitter:description" content="{{ $ogDesc }}" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&family=Vazirmatn:wght@400;500;600;700;800&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="/assets/css/services.css?v=1000" />
    <link href="/assets/css/lang-toggle.css?v=1401" rel="stylesheet" />
    <link id="rtl-style" href="/assets/css/rtl.css?v=1404" rel="stylesheet" disabled />
    <link href="/assets/css/visual-upgrade.css?v=1707" rel="stylesheet" />
    <link href="/assets/css/site-modules.css?v=1823" rel="stylesheet" />
    <link rel="stylesheet" href="/assets/vendor/bootstrap-icons/bootstrap-icons.css" />
    <link href="/assets/vendor/aos/aos.css" rel="stylesheet" />
    <script type="application/ld+json">{!! json_encode($schema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_HEX_AMP) !!}</script>
  </head>
  <body class="service-page service-landing">
    <a class="skip-link" href="#main-content">Skip to main content</a>
    <a href="/#services" class="btn btn-outline back-button">
      <i class="bi bi-arrow-left" aria-hidden="true"></i>
      <span data-en="Back to Services" data-fa="بازگشت به خدمات">Back to Services</span>
    </a>
    <main id="main-content">
      <section class="service-hero" data-aos="fade-up">
        <div class="service-hero-inner">
          <i class="service-hero-mark {{ $service->iconClass() }}" aria-hidden="true"></i>
          <div class="service-hero-copy">
            <p class="service-kicker"><i class="{{ $service->iconClass() }}" aria-hidden="true"></i> <span data-en="{{ $service->priceTypeLabel('en') }}" data-fa="{{ $service->priceTypeLabel('fa') }}">{{ $service->priceTypeLabel('en') }}</span></p>
            <h1 class="service-title" data-en="{{ $service->title }}" data-fa="{{ $titleFa }}">{{ $service->title }}</h1>
            <p class="service-subtitle" data-en="{{ $service->description }}" data-fa="{{ $descFa }}">{{ $service->description }}</p>
          </div>
          <div class="service-hero-aside">
            <p class="service-hero-price" aria-label="{{ $service->displayPrice('en') }}">
              @if ($hasNumericPrice)
                <span class="price-currency" data-en="{{ $currencyEn }}" data-fa="درهم">{{ $currencyEn }}</span>
                <span class="price-number" data-en="{{ $amountEn }}" data-fa="{{ $amountFa }}">{{ $amountEn }}</span>
              @else
                <span class="price-number" data-en="{{ $service->displayPrice('en') }}" data-fa="{{ $service->displayPrice('fa') }}">{{ $service->displayPrice('en') }}</span>
              @endif
            </p>
            @if (data_get($service->presentation, 'unit_en'))
              <p class="service-hero-unit muted" data-en="{{ data_get($service->presentation, 'unit_en') }}" data-fa="{{ data_get($service->presentation, 'unit_fa') }}">{{ data_get($service->presentation, 'unit_en') }}</p>
            @endif
            <div class="service-hero-actions">
              <a class="btn btn-primary" href="#service-request" data-en="Request a Quote" data-fa="درخواست پیش‌فاکتور">Request a Quote</a>
              @if ($service->features)
                <a class="btn btn-outline" href="#included" data-en="See what’s included" data-fa="مشاهده موارد شامل">See what’s included</a>
              @endif
            </div>
          </div>
        </div>
      </section>

      @if ($service->short_description)
        <section class="service-block" data-aos="fade-up">
          <header class="section-header">
            <p class="section-kicker" data-en="Overview" data-fa="نمای کلی">Overview</p>
            <h2 data-en="Service scope" data-fa="محدوده خدمت">Service scope</h2>
          </header>
          <p class="service-lead" data-en="{{ $service->short_description }}" data-fa="{{ $shortFa }}">{{ $service->short_description }}</p>
        </section>
      @endif

      @if ($service->features)
        <section class="service-block" id="included" data-aos="fade-up">
          <header class="section-header">
            <p class="section-kicker" data-en="Included" data-fa="شامل">Included</p>
            <h2 data-en="What is included" data-fa="چه چیزهایی شامل می‌شود">What is included</h2>
          </header>
          <ul class="service-include-grid service-lines">
            @foreach ($service->features as $item)
              <li data-en="{{ $item['en'] ?? '' }}" data-fa="{{ $item['fa'] ?? ($item['en'] ?? '') }}">{{ $item['en'] ?? '' }}</li>
            @endforeach
          </ul>
        </section>
      @endif

      <section class="service-block service-block--pricing" id="pricing" data-aos="fade-up">
        <header class="section-header">
          <p class="section-kicker" data-en="Pricing" data-fa="قیمت‌گذاری">Pricing</p>
          <h2 data-en="Investment" data-fa="سرمایه‌گذاری">Investment</h2>
        </header>
        <div class="service-price-panel">
          <div class="service-price-anchor">
            <p class="service-price-amount" aria-label="{{ $service->displayPrice('en') }}">
              @if ($hasNumericPrice)
                <span class="price-currency" data-en="{{ $currencyEn }}" data-fa="درهم">{{ $currencyEn }}</span>
                <span class="price-number" data-en="{{ $amountEn }}" data-fa="{{ $amountFa }}">{{ $amountEn }}</span>
              @else
                <span class="price-number" data-en="{{ $service->displayPrice('en') }}" data-fa="{{ $service->displayPrice('fa') }}">{{ $service->displayPrice('en') }}</span>
              @endif
            </p>
            @if (data_get($service->presentation, 'unit_en'))
              <p class="muted" data-en="{{ data_get($service->presentation, 'unit_en') }}" data-fa="{{ data_get($service->presentation, 'unit_fa') }}">{{ data_get($service->presentation, 'unit_en') }}</p>
            @endif
            @if (data_get($service->presentation, 'irr_note_en'))
              <p class="muted" data-en="{{ data_get($service->presentation, 'irr_note_en') }}" data-fa="{{ data_get($service->presentation, 'irr_note_fa') }}">{{ data_get($service->presentation, 'irr_note_en') }}</p>
            @endif
          </div>
          @if ($exclusions)
            <div class="service-price-notes">
              <h3 data-en="Exclusions & Assumptions" data-fa="فرضیات و خارج از شمول">Exclusions & Assumptions</h3>
              <ul class="service-plain-list">
                @foreach ($exclusions as $item)
                  <li data-en="{{ $item['en'] ?? '' }}" data-fa="{{ $item['fa'] ?? ($item['en'] ?? '') }}">{{ $item['en'] ?? '' }}</li>
                @endforeach
              </ul>
            </div>
          @endif
        </div>
      </section>

      @if ($deliverables)
        <section class="service-block" data-aos="fade-up">
          <header class="section-header">
            <p class="section-kicker" data-en="Deliverables" data-fa="اقلام قابل تحویل">Deliverables</p>
            <h2 data-en="What you receive" data-fa="آنچه تحویل می‌گیرید">What you receive</h2>
          </header>
          <ul class="service-include-grid service-lines">
            @foreach ($deliverables as $item)
              <li data-en="{{ $item['en'] ?? '' }}" data-fa="{{ $item['fa'] ?? ($item['en'] ?? '') }}">{{ $item['en'] ?? '' }}</li>
            @endforeach
          </ul>
        </section>
      @endif

      @if ($service->process)
        <section class="service-block" data-aos="fade-up">
          <header class="section-header">
            <p class="section-kicker" data-en="Process" data-fa="فرآیند">Process</p>
            <h2 data-en="How the engagement runs" data-fa="روند همکاری">How the engagement runs</h2>
          </header>
          <ol class="service-process">
            @foreach ($service->process as $index => $step)
              <li data-aos="fade-up" data-aos-delay="{{ min(400, $index * 80) }}">
                <span class="service-process-num">{{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                <div>
                  <h3 data-en="{{ $step['en'] ?? '' }}" data-fa="{{ $step['fa'] ?? ($step['en'] ?? '') }}">{{ $step['en'] ?? '' }}</h3>
                  <p data-en="{{ $step['duration_en'] ?? '' }}" data-fa="{{ $step['duration_fa'] ?? ($step['duration_en'] ?? '') }}">{{ $step['duration_en'] ?? '' }}</p>
                </div>
              </li>
            @endforeach
          </ol>
        </section>
      @endif

      @if ($sla)
        <section class="service-block" data-aos="fade-up">
          <header class="section-header">
            <p class="section-kicker" data-en="SLA" data-fa="SLA">SLA</p>
            <h2 data-en="SLA & Warranty" data-fa="SLA و وارانتی">SLA & Warranty</h2>
          </header>
          <ul class="service-include-grid service-compact">
            @foreach ($sla as $item)
              <li data-en="{{ $item['en'] ?? '' }}" data-fa="{{ $item['fa'] ?? ($item['en'] ?? '') }}">{{ $item['en'] ?? '' }}</li>
            @endforeach
          </ul>
        </section>
      @endif

      @if ($addons)
        <section class="service-block" data-aos="fade-up">
          <header class="section-header">
            <p class="section-kicker" data-en="Optional" data-fa="اختیاری">Optional</p>
            <h2 data-en="Optional Add-ons" data-fa="افزونه‌های اختیاری">Optional Add-ons</h2>
          </header>
          <ul class="service-include-grid service-compact">
            @foreach ($addons as $item)
              <li data-en="{{ $item['en'] ?? '' }}" data-fa="{{ $item['fa'] ?? ($item['en'] ?? '') }}">{{ $item['en'] ?? '' }}</li>
            @endforeach
          </ul>
        </section>
      @endif

      @if ($service->faq)
        <section class="service-block" data-aos="fade-up">
          <header class="section-header">
            <p class="section-kicker" data-en="FAQ" data-fa="پرسش‌ها">FAQ</p>
            <h2 data-en="FAQ" data-fa="پرسش‌های پرتکرار">FAQ</h2>
          </header>
          <div class="service-faq">
            @foreach ($service->faq as $i => $item)
              <div class="faq-item">
                <button class="faq-question" type="button" onclick="toggleFAQ(this)" aria-expanded="false" aria-controls="faq-answer-{{ $i }}">
                  <span data-en="{{ $item['question_en'] ?? '' }}" data-fa="{{ $item['question_fa'] ?? ($item['question_en'] ?? '') }}">{{ $item['question_en'] ?? '' }}</span>
                  <i class="bi bi-chevron-down" aria-hidden="true"></i>
                </button>
                <div class="faq-answer" id="faq-answer-{{ $i }}" role="region" aria-hidden="true">
                  <p data-en="{{ $item['answer_en'] ?? '' }}" data-fa="{{ $item['answer_fa'] ?? ($item['answer_en'] ?? '') }}">{{ $item['answer_en'] ?? '' }}</p>
                </div>
              </div>
            @endforeach
          </div>
        </section>
      @endif

      <section class="service-block service-quote" id="service-request" data-aos="fade-up">
        <header class="section-header">
          <p class="section-kicker" data-en="Next step" data-fa="گام بعد">Next step</p>
          <h2 data-en="Need this service?" data-fa="به این خدمت نیاز دارید؟">Need this service?</h2>
        </header>
        <p class="service-lead" data-en="{{ $ctaEn }}" data-fa="{{ $ctaFa }}">{{ $ctaEn }}</p>
        <button class="btn btn-primary" type="button" onclick="showContactForm()" data-en="Request a Quote" data-fa="درخواست پیش‌فاکتور">Request a Quote</button>
        <div class="contact-form" id="contactForm" hidden>
          <form class="php-email-form" onsubmit="submitForm(event)" novalidate>
            <div class="form-group honeypot" aria-hidden="true" style="display: none">
              <label for="website-field">Website</label>
              <input type="text" name="website" id="website-field" tabindex="-1" autocomplete="off" />
            </div>
            <div class="form-group form-float">
              <input type="text" name="name" id="contact-name" placeholder=" " required minlength="2" maxlength="50" autocomplete="name" />
              <label for="contact-name" data-en="Full Name" data-fa="نام کامل">Full Name</label>
            </div>
            <div class="form-group form-float">
              <input type="email" name="email" id="contact-email" placeholder=" " required maxlength="100" autocomplete="email" />
              <label for="contact-email" data-en="Email Address" data-fa="آدرس ایمیل">Email Address</label>
            </div>
            <div class="form-group form-float">
              <input type="tel" name="phone" id="contact-phone" placeholder=" " autocomplete="tel" />
              <label for="contact-phone" data-en="Phone Number" data-fa="شماره تلفن">Phone Number</label>
            </div>
            <div class="form-group form-float">
              <select name="service" id="contact-service" required>
                @foreach ($catalog as $option)
                  <option value="{{ $option->slug }}" @selected($option->id === $service->id) data-en="{{ $option->title }}" data-fa="{{ data_get($option->presentation, 'title_fa') ?: $option->title }}">{{ $option->title }}</option>
                @endforeach
              </select>
              <label for="contact-service" data-en="Service" data-fa="خدمت">Service</label>
            </div>
            <div class="form-group form-float form-span">
              <input type="text" name="subject" id="contact-subject" placeholder=" " required minlength="5" maxlength="100" value="{{ $formSubject }}" />
              <label for="contact-subject" data-en="Subject" data-fa="موضوع">Subject</label>
            </div>
            <div class="form-group form-float form-span">
              <textarea name="message" id="contact-message" rows="7" placeholder=" " required minlength="10" maxlength="1000"></textarea>
              <label for="contact-message" data-en="Project Details" data-fa="جزئیات پروژه">Project Details</label>
            </div>
            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
            <input type="hidden" name="csrf_token" id="csrf_token" value="{{ csrf_token() }}" />
            <div class="form-error form-span" id="form-error" role="alert" aria-live="assertive" hidden></div>
            <button type="submit" class="btn btn-primary form-span" data-en="Submit Request" data-fa="ارسال درخواست">Submit Request</button>
          </form>
        </div>
      </section>
    </main>
    <div id="toast" class="toast" hidden role="status" aria-live="polite" aria-atomic="true" data-en="Request submitted successfully! We'll contact you soon." data-fa="درخواست با موفقیت ارسال شد.">Request submitted successfully! We'll contact you soon.</div>
    <script src="/assets/vendor/aos/aos.js"></script>
    <script src="/assets/js/contact-form.js?v=1403"></script>
    <script src="/assets/js/i18n.js?v=1403"></script>
    <script>
      AOS.init({ once: true, disable: window.matchMedia('(prefers-reduced-motion: reduce)').matches });
      function copyText(en, fa) {
        return document.documentElement.lang === "fa" ? fa : en;
      }
      function toggleFAQ(element) {
        const answer = element.nextElementSibling;
        const icon = element.querySelector("i");
        const isOpen = answer.classList.contains("show");
        document.querySelectorAll(".faq-answer.show").forEach((item) => { item.classList.remove("show"); item.setAttribute("aria-hidden", "true"); });
        document.querySelectorAll(".faq-question").forEach((item) => { const chevron = item.querySelector("i"); if (chevron) chevron.style.transform = "rotate(0deg)"; item.setAttribute("aria-expanded", "false"); });
        if (!isOpen) {
          answer.classList.add("show");
          if (icon) icon.style.transform = "rotate(180deg)";
          element.setAttribute("aria-expanded", "true");
          answer.setAttribute("aria-hidden", "false");
        }
      }
      function showContactForm() {
        const form = document.getElementById("contactForm");
        form.hidden = false;
        form.removeAttribute("hidden");
        form.classList.add("show", "is-open");
        const name = document.getElementById("contact-name");
        form.scrollIntoView({ behavior: window.matchMedia('(prefers-reduced-motion: reduce)').matches ? 'auto' : 'smooth' });
        if (name) name.focus();
      }
      async function submitForm(event) {
        event.preventDefault();
        const form = event.target;
        const formData = new FormData(form);
        const submitBtn = form.querySelector('button[type="submit"]');
        const errorEl = document.getElementById("form-error");
        const toast = document.getElementById("toast");
        const name = (formData.get("name") || "").trim();
        const email = (formData.get("email") || "").trim();
        const message = (formData.get("message") || "").trim();
        const subject = (formData.get("subject") || "").trim();
        const showError = (en, fa) => {
          errorEl.hidden = false;
          errorEl.textContent = copyText(en, fa);
        };
        if (name.length < 2 || name.length > 50) { showError("Invalid name (2-50 characters required)", "نام نامعتبر است (۲ تا ۵۰ نویسه)"); return; }
        if (!email || email.length > 100) { showError("Invalid email address", "نشانی ایمیل نامعتبر است"); return; }
        if (subject.length < 5 || subject.length > 100) { showError("Invalid subject (5-100 characters required)", "موضوع نامعتبر است (۵ تا ۱۰۰ نویسه)"); return; }
        if (message.length < 10 || message.length > 1000) { showError("Invalid message (10-1000 characters required)", "پیام نامعتبر است (۱۰ تا ۱۰۰۰ نویسه)"); return; }
        errorEl.hidden = true;
        submitBtn.disabled = true;
        submitBtn.setAttribute("aria-busy", "true");
        submitBtn.textContent = copyText("Sending...", "در حال ارسال...");
        try {
          if (!window.MeetAjForms?.postForm) {
            throw new Error("form-helper");
          }
          const result = await window.MeetAjForms.postForm(form);
          if (result.ok) {
            form.reset();
            document.getElementById("contact-service").value = "{{ $service->slug }}";
            document.getElementById("contact-subject").value = @json($formSubject);
            toast.hidden = false;
            toast.classList.add("show");
            window.setTimeout(() => {
              toast.classList.remove("show");
              toast.hidden = true;
            }, 5000);
          } else if (result.status === 419) {
            showError("Security token error. Please refresh and try again.", "خطای امنیتی. صفحه را تازه‌سازی کنید و دوباره تلاش کنید.");
          } else {
            errorEl.hidden = false;
            errorEl.textContent = result.message || copyText("Request failed.", "ارسال درخواست ناموفق بود.");
          }
        } catch (e) {
          showError("Network error. Please try again.", "خطای شبکه. دوباره تلاش کنید.");
        } finally {
          submitBtn.disabled = false;
          submitBtn.removeAttribute("aria-busy");
          submitBtn.textContent = copyText(submitBtn.getAttribute("data-en") || "Submit Request", submitBtn.getAttribute("data-fa") || "ارسال درخواست");
        }
      }
      if (location.hash === "#service-request" || location.hash === "#contactForm") {
        showContactForm();
      }
      document.querySelectorAll('a[href="#service-request"]').forEach((link) => {
        link.addEventListener("click", () => setTimeout(showContactForm, 0));
      });
    </script>
  </body>
</html>
