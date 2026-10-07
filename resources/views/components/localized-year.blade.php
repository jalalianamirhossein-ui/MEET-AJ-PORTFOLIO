@php
    $date = now();
    $formatter = app(\App\Services\DisplayDate::class);
    $english = $formatter->format($date, 'en', true);
    $persian = $formatter->format($date, 'fa', true);
@endphp
<span data-current-year data-en="{{ $english }}" data-fa="{{ $persian }}">{{ request()->cookie('lang') === 'fa' ? $persian : $english }}</span>
