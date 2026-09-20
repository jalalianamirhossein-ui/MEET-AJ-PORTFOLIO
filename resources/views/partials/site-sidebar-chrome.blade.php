@php
  $logoHref = $logoHref ?? '/#hero';
  $homepageContent = $homepageContent ?? app(\App\Services\HomepageContentCatalog::class)->forView();
  $siteContent = isset($homepageContent) ? ($homepageContent['site']?->content ?? []) : [];
  $socials = data_get($siteContent, 'socials', []);
@endphp
      <div class="brand-lang" id="lang-mount"></div>
      <div class="profile-img">
        <img
          src="{{ data_get($siteContent, 'profile_image', '/assets/img/my-profile-img.jpg') }}"
          alt="{{ data_get($siteContent, 'site_name', 'Meet AJ') }} Profile Picture"
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
            src="{{ data_get($siteContent, 'logo_image', '/assets/img/logo.png') }}"
            alt="Aj-Network"
            width="40"
            height="40"
            decoding="async"
            fetchpriority="high"
            sizes="40px"
          />
          <div class="sitename">{{ data_get($siteContent, 'site_name', 'Meet AJ') }}</div>
        </a>
      </div>
      <div class="social-links text-center">
        <div class="social-row social-row-main">
          @foreach (array_slice($socials, 0, 4) as $social)
            <a href="{{ $social['url'] ?? '#' }}" class="{{ $social['key'] ?? 'social' }}" target="_blank" rel="noopener" aria-label="{{ $social['label'] ?? '' }}"><i class="{{ $social['icon'] ?? 'bi bi-link-45deg' }}"></i></a>
          @endforeach
        </div>
        <div class="social-row social-row-secondary">
          @foreach (array_slice($socials, 4, 4) as $social)
            <a href="{{ $social['url'] ?? '#' }}" class="{{ $social['key'] ?? 'social' }}" target="_blank" rel="noopener" aria-label="{{ $social['label'] ?? '' }}"><i class="{{ $social['icon'] ?? 'bi bi-link-45deg' }}"></i></a>
          @endforeach
        </div>
      </div>
