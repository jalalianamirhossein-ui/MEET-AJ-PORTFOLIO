@php
  $titleFa = data_get($service->presentation, 'title_fa') ?: $service->title;
  $shortFa = data_get($service->presentation, 'short_description_fa') ?: $service->short_description;
  $image = $service->featured_image;
  if (is_string($image) && $image !== '' && ! str_starts_with($image, '/') && ! str_starts_with($image, 'http')) {
      $image = '/storage/'.$image;
  }
@endphp
<article class="col-lg-4 col-md-6 service-item service-catalog-card d-flex" data-aos="fade-up">
  <div class="icon flex-shrink-0" aria-hidden="true">
    @if ($image)
      <img src="{{ $image }}" alt="" width="40" height="40">
    @else
      <i class="{{ $service->iconClass() }}"></i>
    @endif
  </div>
  <div class="service-catalog-body">
    <h3 class="title">
      <a href="{{ $service->path() }}" data-en="{{ $service->title }}" data-fa="{{ $titleFa }}">{{ $service->title }}</a>
    </h3>
    <p class="description" data-en="{{ $service->short_description }}" data-fa="{{ $shortFa }}">{{ $service->short_description }}</p>
    <p class="service-catalog-price" data-en="{{ $service->displayPrice('en') }}" data-fa="{{ $service->displayPrice('fa') }}">{{ $service->displayPrice('en') }}</p>
    <div class="service-catalog-actions">
      <a class="btn btn-primary service-catalog-cta" href="{{ $service->path() }}" data-en="View Details" data-fa="مشاهده جزئیات">View Details</a>
      <a class="btn btn-outline-primary service-catalog-cta" href="{{ $service->path() }}#service-request" data-en="Request Service" data-fa="درخواست خدمت">Request Service</a>
    </div>
  </div>
</article>
