@props(['light' => false])
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-3 '.($light ? 'text-white' : 'text-navy')]) }}>
@if($settings->get('company.logo'))
    <img src="{{ \App\Support\Images::url($settings->get('company.logo')) }}" alt="{{ $settings->get('company.name', 'PropertyME') }}" class="h-10 max-w-48 object-contain" width="180" height="40">
@else
    <svg class="h-10 w-9 shrink-0 text-gold" viewBox="0 0 38 44" fill="none" aria-hidden="true"><path d="M3 40V12L19 3l16 9v28H3Z" stroke="currentColor" stroke-width="1.4"/><path d="M11 40V17l8-5 8 5v23M19 12v28M3 25l8-4m16 0 8 4" stroke="currentColor" stroke-width="1.1"/></svg>
    <span><span class="block whitespace-nowrap text-[23px] font-light leading-none tracking-[-.035em]">Property<span class="font-semibold">ME</span><span class="text-gold">.</span></span><span class="mt-1.5 block whitespace-nowrap text-[7px] uppercase tracking-[.38em]">Bali real estate & beyond</span></span>
@endif
</span>
