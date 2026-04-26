@props(['title', 'subtitle' => null])

<div class="d-flex flex-wrap justify-content-between align-items-start gap-3 mb-4">
    <div>
        <h1 class="ormsa-heading h4 mb-1 fw-semibold">{{ $title }}</h1>
        @if($subtitle)
            <p class="text-secondary mb-0 small">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($slot)
        @if(!$slot->isEmpty())
            <div class="d-flex flex-wrap gap-2 align-items-center">{{ $slot }}</div>
        @endif
    @endisset
</div>
