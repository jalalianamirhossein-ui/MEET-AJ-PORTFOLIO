@php
  $titleFa = data_get($service->presentation, 'title_fa') ?: $service->title;
  $shortFa = data_get($service->presentation, 'short_description_fa') ?: $service->short_description;
  $detailEn = $service->description ?: $service->short_description;
  $detailFa = data_get($service->presentation, 'description_fa') ?: $shortFa;
  $image = $service->featured_image;
  if (is_string($image) && $image !== '' && ! str_starts_with($image, '/') && ! str_starts_with($image, 'http')) {
      $image = '/storage/'.$image;
  }
  $icon = $service->iconClass();
@endphp
<article
  class="service-catalog-card"
  data-aos="fade-up"
  data-service-slug="{{ $service->slug }}"
  data-service-subject-en="{{ $service->title }} inquiry"
  data-service-subject-fa="{{ $titleFa }} — درخواست"
>
  <div class="service-card-mark" aria-hidden="true">
    @if ($image)
      <img src="{{ $image }}" alt="" width="40" height="40">
    @else
      <i class="{{ $icon }}"></i>
    @endif
  </div>
  <h3 class="title" data-en="{{ $service->title }}" data-fa="{{ $titleFa }}">{{ $service->title }}</h3>
  <p class="description" data-en="{{ $service->short_description }}" data-fa="{{ $shortFa }}">{{ $service->short_description }}</p>
  <div class="service-details-source" hidden aria-hidden="true">
    <p class="service-details-lead" data-en="{{ $detailEn }}" data-fa="{{ $detailFa }}">{{ $detailEn }}</p>
    @if ($service->features)
      <ul class="service-details-features">
        @foreach ($service->features as $item)
          <li data-en="{{ $item['en'] ?? '' }}" data-fa="{{ $item['fa'] ?? ($item['en'] ?? '') }}">{{ $item['en'] ?? '' }}</li>
        @endforeach
      </ul>
    @endif
  </div>
  <div class="service-catalog-actions">
    <button
      type="button"
      class="btn btn-primary service-catalog-cta service-details-open"
      aria-haspopup="dialog"
      aria-controls="service-drawer"
      data-en="View Details"
      data-fa="مشاهده جزئیات"
    >
      View Details
    </button>
    <a
      class="btn btn-outline-primary service-catalog-cta service-contact-link"
      href="#contact"
      data-en="Contact Me"
      data-fa="تماس با من"
    >
      Contact Me
    </a>
  </div>
</article>
