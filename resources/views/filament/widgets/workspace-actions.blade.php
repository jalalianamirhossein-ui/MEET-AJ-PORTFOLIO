<x-filament-widgets::widget>
    <section class="meetaj-workspace" aria-labelledby="workspace-heading">
        <div class="meetaj-workspace-copy">
            <p class="meetaj-admin-eyebrow">MEET AJ / WORKSPACE</p>
            <h2 id="workspace-heading">Your next great update starts here.</h2>
            <p>Publish knowledge, keep your portfolio current, and stay close to your clients.</p>
            <a class="meetaj-site-link" href="{{ url('/') }}" target="_blank" rel="noopener">
                View website <x-filament::icon icon="heroicon-o-arrow-up-right" />
            </a>
        </div>
        @if (count($actions))
            <nav class="meetaj-workspace-actions" aria-label="Workspace shortcuts">
                @foreach ($actions as $action)
                    <a class="meetaj-workspace-action" href="{{ $action['url'] }}">
                        <x-filament::icon :icon="$action['icon']" />
                        <span><strong>{{ $action['label'] }}</strong><small>{{ $action['description'] }}</small></span>
                        <x-filament::icon icon="heroicon-o-chevron-right" class="meetaj-action-arrow" />
                    </a>
                @endforeach
            </nav>
        @endif
    </section>
</x-filament-widgets::widget>
