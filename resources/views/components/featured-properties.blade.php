@props(['properties', 'featuredListing', 'featuredCollection'])
<section id="featured-properties" class="border-b border-line bg-white py-16 md:py-20">
    <div class="container-wide">
        <div class="flex flex-wrap items-end justify-between gap-5 border-b border-line pb-7"><div><p class="eyebrow text-muted">{{ __('A place for your next chapter') }}</p><h2 class="mt-4 font-sans text-3xl font-medium tracking-[-.035em] text-navy md:text-[42px]">{{ $settings->get('homepage.featured_title','Featured properties') }}</h2></div><a href="{{ route('properties.index') }}" class="text-link">{{ __('View All Properties') }}<x-icon class="h-4 w-4"/></a></div>
        <div class="my-7 flex flex-wrap justify-between gap-4">
            <div class="flex gap-2" aria-label="Featured listing type">@foreach(['sale'=>'For sale','rent'=>'For rent'] as $value=>$label)<a href="{{ route('home',['featured_listing'=>$value,'featured_collection'=>$featuredCollection]) }}#featured-properties" class="collection-tab {{ $featuredListing===$value ? 'active' : '' }}" @if($featuredListing===$value) aria-current="true" @endif>{{ __($label) }}</a>@endforeach</div>
            <div class="flex flex-wrap gap-2" aria-label="Featured property collection">@foreach(['all'=>'All properties','leasehold'=>'Leasehold','freehold'=>'Freehold','land'=>'Land plots'] as $value=>$label)<a href="{{ route('home',['featured_listing'=>$featuredListing,'featured_collection'=>$value]) }}#featured-properties" class="collection-tab {{ $featuredCollection===$value ? 'active' : '' }}" @if($featuredCollection===$value) aria-current="true" @endif>{{ __($label) }}</a>@endforeach</div>
        </div>
        @if($properties->isNotEmpty())<div class="grid items-stretch gap-6 sm:grid-cols-2 lg:grid-cols-3">@foreach($properties as $property)<x-property-card :property="$property"/>@endforeach</div>@else<x-empty-state title="{{ __('Your next chapter is taking shape.') }}" message="Our property collection is being prepared. Tell us what you have in mind, and our team can discuss your requirements."/>@endif
    </div>
</section>
