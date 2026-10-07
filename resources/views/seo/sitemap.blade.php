{!! '<'.'?xml version="1.0" encoding="UTF-8"?>' !!}
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:xhtml="http://www.w3.org/1999/xhtml">
@foreach ($urls as $url)
  <url>
    <loc>{{ $url['loc'] }}</loc>
    @foreach ($url['alternates'] ?? [] as $language => $target)
    <xhtml:link rel="alternate" hreflang="{{ $language }}" href="{{ $target }}" />
    @endforeach
    @if (!empty($url['lastmod']))
    <lastmod>{{ $url['lastmod'] }}</lastmod>
    @endif
  </url>
@endforeach
</urlset>
