@php
    use Jenssegers\Agent\Agent;
    $agent = new Agent();

    $gallery = $track->ecMedia;
@endphp

<x-trackLayout :track="$track" :gallery="$gallery">
    <x-trackHeader :track="$track" :agent="$agent"/>
    <main class="max-w-screen-xl m-auto pb-20">
        <x-mapsection :track="$track"/>
        <x-trackContentSection :track="$track" />
        @if ($gallery->count() > 0)
            <div class="max-w-screen-xl m-auto py-6 px-4 relative">
                <x-carouselSection :track="$track" :gallery="$gallery"/>
            </div>
        @endif
        @if ($agent->isMobile())
            <x-trackMobileDownloadSection :track="$track" :agent="$agent"/>
        @endif
    </main>
</x-trackLayout>