@props([
    'logoHref' => '/#hero',
])
<header id="header" class="header dark-background d-flex flex-column">
  @include('partials.site-sidebar-chrome', ['logoHref' => $logoHref])
  {{ $slot }}
</header>
