@php
  $logoHref = $logoHref ?? '/#hero';
@endphp
      <div class="brand-lang" id="lang-mount"></div>
      <div class="profile-img">
        <img
          src="/assets/img/my-profile-img.jpg"
          alt="AmirHossein Jalalian Profile Picture"
          class="img-fluid rounded-circle"
          width="200"
          height="200"
          decoding="async"
          fetchpriority="high"
          sizes="120px"
        />
      </div>
      <div class="logo-section d-flex align-items-center justify-content-center">
        <a href="{{ $logoHref }}" class="logo d-flex align-items-center">
          <img
            src="/assets/img/logo.png"
            alt="Aj-Network"
            width="40"
            height="40"
            decoding="async"
            fetchpriority="high"
            sizes="40px"
          />
          <div class="sitename">Meet AJ</div>
        </a>
      </div>
      <div class="social-links text-center">
        <div class="social-row social-row-main">
          <a href="https://www.linkedin.com/in/amirhussein-jalalian-050702188/" class="linkedin" target="_blank" rel="noopener" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
          <a href="https://github.com/jalalianamirhossein-ui" class="github" target="_blank" rel="noopener" aria-label="GitHub"><i class="bi bi-github"></i></a>
          <a href="https://wa.me/989197276219" class="whatsapp" target="_blank" rel="noopener" aria-label="WhatsApp"><i class="bi bi-whatsapp"></i></a>
          <a href="https://t.me/Aj_mercury" class="telegram" target="_blank" rel="noopener" aria-label="Telegram"><i class="bi bi-telegram"></i></a>
        </div>
        <div class="social-row social-row-secondary">
          <a href="https://twitter.com/RealAjMercury" class="twitter" target="_blank" rel="noopener" aria-label="X (Twitter)">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
              <path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
            </svg>
          </a>
          <a href="https://stackoverflow.com/users/24522280/amir-jalalian" class="stackoverflow" target="_blank" rel="noopener" aria-label="Stack Overflow"><i class="bi bi-stack-overflow"></i></a>
          <a href="https://www.facebook.com/amir.jalalian.37" class="facebook" target="_blank" rel="noopener" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
          <a href="https://instagram.com/aj.mercury" class="instagram" target="_blank" rel="noopener" aria-label="Instagram"><i class="bi bi-instagram"></i></a>
        </div>
      </div>
