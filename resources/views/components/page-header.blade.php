@props(['title', 'subtitle' => null])

<div class="ormsa-page-header">
    <div>
        <h1 class="ormsa-page-header-title">{{ $title }}</h1>
        @if($subtitle)
            <p class="ormsa-page-header-sub">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($slot)
        @if(!$slot->isEmpty())
            <div class="ormsa-page-header-actions">{{ $slot }}</div>
        @endif
    @endisset
</div>
