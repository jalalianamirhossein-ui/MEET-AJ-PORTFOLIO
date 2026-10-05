@props(['date', 'locale' => null])
@php
    $formatter = app(\App\Services\DisplayDate::class);
    $english = $formatter->format($date, 'en');
    $persian = $formatter->format($date, 'fa');
    $current = $locale ?? (request()->cookie('lang') === 'fa' ? 'fa' : 'en');
@endphp
<time {{ $attributes }} datetime="{{ $date->format(DATE_ATOM) }}" data-en="{{ $english }}" data-fa="{{ $persian }}">{{ $current === 'fa' ? $persian : $english }}</time>
