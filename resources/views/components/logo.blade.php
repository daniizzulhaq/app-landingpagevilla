@props(['light' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 '.($light ? 'text-white' : 'text-navy')]) }}>
@if($settings->get('company.logo'))
    <img src="{{ \App\Support\Images::url($settings->get('company.logo')) }}" alt="{{ $settings->get('company.name', 'PropertyME') }}" class="h-10 max-w-48 object-contain" width="180" height="40">
@else
    <img src="{{ asset('images/propertyme-logo.png') }}" alt="" aria-hidden="true" class="h-10 w-16 shrink-0 object-contain {{ $light ? 'brightness-0 invert' : '' }}" width="1711" height="919" decoding="async">
    <span><span class="block whitespace-nowrap text-[23px] font-light leading-none tracking-[-.035em]">Property<span class="font-semibold">ME</span><span class="text-gold">.</span></span><span class="mt-1.5 block whitespace-nowrap text-[7px] uppercase tracking-[.38em]">Bali real estate & beyond</span></span>
@endif
</span>
