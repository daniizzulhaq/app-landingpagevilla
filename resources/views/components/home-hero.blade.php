@props(['locations', 'types'])
<section class="relative flex min-h-[940px] flex-col overflow-hidden bg-navy text-white sm:min-h-[850px] lg:min-h-[900px] lg:h-[100svh]">
    <img src="{{ \App\Support\Images::url($settings->get('homepage.hero_image')) }}" data-fallback="{{ asset('images/architecture.svg') }}" alt="Tropical villa and infinity pool overlooking the ocean" width="1536" height="1024" fetchpriority="high" class="absolute inset-0 h-full w-full object-cover object-center">
    <div class="absolute inset-0 bg-deep/45"></div><div class="absolute inset-0 bg-gradient-to-t from-deep/70 via-transparent to-deep/20"></div>
    <div class="container-wide relative z-10 flex flex-1 flex-col justify-center pb-10 pt-40 text-center lg:pt-36">
        <p class="eyebrow justify-center text-white/80">{{ $settings->get('homepage.hero_eyebrow') }}</p>
        <h1 class="mx-auto mt-6 max-w-5xl font-sans text-[2.5rem] font-medium leading-[1.12] tracking-[-.045em] sm:text-5xl lg:text-[64px]">{{ $settings->get('homepage.hero_headline') }}</h1>
        <p class="mx-auto mt-5 max-w-2xl text-[13px] leading-7 text-white/85 sm:text-[15px]">{{ $settings->get('homepage.hero_subheadline') }}</p>
        <div x-data="{ intent: 'sale' }" class="mx-auto mt-9 w-full max-w-[960px]">
            <div class="mx-auto mb-3 inline-flex max-w-full rounded-full border border-white/30 bg-white/90 p-1 text-navy backdrop-blur" role="group" aria-label="Choose your property journey">
                @foreach(['sale'=>'Buy','sell'=>'Sell','rent'=>'Rent','manage'=>'Management'] as $value=>$label)
                    <button type="button" @click="intent=@js($value)" :aria-pressed="intent === @js($value)" :class="intent === @js($value) ? 'bg-navy text-white shadow-sm' : 'text-navy/65 hover:text-navy'" class="min-h-10 rounded-full px-4 text-[11px] font-medium transition sm:px-6 sm:text-xs">{{ __($label) }}</button>
                @endforeach
            </div>
            <form x-show="intent === 'sale' || intent === 'rent'" method="GET" action="{{ route('properties.index') }}" class="hero-search-shell">
                <input type="hidden" name="listing_type" :value="intent === 'rent' ? 'rent' : 'sale'" value="sale">
                <div class="hero-search-field"><label for="hero-location">{{ __('Location') }}</label><select name="location" id="hero-location"><option value="">{{ __('Anywhere in Bali') }}</option>@foreach($locations as $location)<option value="{{ $location->id }}">{{ $location->name }}</option>@endforeach</select></div>
                <div class="hero-search-field"><label for="hero-type">{{ __('Property type') }}</label><select name="property_type" id="hero-type"><option value="">{{ __('All property types') }}</option>@foreach($types as $type)<option value="{{ $type->id }}">{{ $type->name }}</option>@endforeach</select></div>
                <div class="hero-search-field"><label for="hero-price">{{ __('Maximum price · IDR') }}</label><select name="max_price" id="hero-price"><option value="">{{ __('Any budget') }}</option><template x-for="budget in (intent === 'rent' ? [{value:2000000,label:@js(__('Up to IDR 2 million'))},{value:5000000,label:@js(__('Up to IDR 5 million'))},{value:10000000,label:@js(__('Up to IDR 10 million'))},{value:50000000,label:@js(__('Up to IDR 50 million'))}] : [{value:3000000000,label:@js(__('Up to IDR 3 billion'))},{value:5000000000,label:@js(__('Up to IDR 5 billion'))},{value:10000000000,label:@js(__('Up to IDR 10 billion'))},{value:20000000000,label:@js(__('Up to IDR 20 billion'))}])" :key="intent + budget.value"><option :value="budget.value" x-text="budget.label"></option></template></select></div>
                <button type="submit" class="hero-search-submit"><x-icon name="search" class="h-4 w-4"/>{{ __('Search Properties') }}</button>
            </form>
            <div x-show="intent === 'sell'" x-cloak class="hero-intent-panel"><p>{{ __('Let us introduce your property to its next chapter.') }}</p><x-whatsapp-cta type="list-sell-rent" :label="__('List My Property on WhatsApp')" variant="whatsapp" class="rounded-full"/></div>
            <div x-show="intent === 'manage'" x-cloak class="hero-intent-panel"><p>{{ __('Thoughtful hospitality and dedicated care for your villa.') }}</p><x-whatsapp-cta type="property-management" :label="__('Discuss Villa Management')" variant="whatsapp" class="rounded-full"/></div>
            <div class="mt-5 flex flex-wrap justify-center gap-2">
                @foreach(['Villa'=>'Luxury villas','Land'=>'Land plots','Apartment'=>'Apartments'] as $typeName=>$label)@if($type = $types->firstWhere('slug',Str::slug($typeName)))<a href="{{ route('properties.index',['property_type'=>$type->id]) }}" class="hero-quick-tag">{{ __($label) }}</a>@endif @endforeach
                <a href="{{ route('properties.index',['listing_type'=>'rent']) }}" class="hero-quick-tag">{{ __('Villa stays') }}</a><a href="{{ route('services.show','invest-build') }}" class="hero-quick-tag">{{ __('Invest & build') }}</a>
            </div>
            <a href="{{ $settings->get('homepage.hero_cta_url','/services') ?: route('services.index') }}" class="mt-6 inline-flex min-h-9 items-center gap-3 border-b border-white/40 text-[10px] font-medium uppercase tracking-widest text-white/85">{{ $settings->get('homepage.hero_cta_label','Explore Our Services') }}<x-icon class="h-3 w-3"/></a>
        </div>
    </div>
    <div class="container-wide relative z-10 hidden pb-10 sm:block"><div class="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
        @foreach([
            ['building','Buy Your Dream Property','Explore the collection',route('properties.index',['listing_type'=>'sale'])],
            ['key','List With PropertyME','Sell or rent your property',route('services.show','list-sell-rent')],
            ['pin','Find Your Bali Stay','Explore villa rentals',route('properties.index',['listing_type'=>'rent'])],
            ['shield','Property Management','A little peace of mind',route('services.show','property-management')]
        ] as [$icon,$title,$subtitle,$url])<a href="{{ $url }}" class="group flex items-center gap-3 rounded-full border border-white/25 bg-white/15 px-4 py-3 text-left backdrop-blur-md transition hover:bg-white/25"><span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full border border-white/15 bg-white/10"><x-icon :name="$icon" class="h-4 w-4"/></span><span class="min-w-0 flex-1"><span class="block text-[11px] font-semibold leading-5">{{ __($title) }}</span><span class="block text-[10px] text-white/65">{{ __($subtitle) }}</span></span><x-icon name="arrow-right" class="h-4 w-4 text-gold"/></a>@endforeach
    </div></div>
</section>
