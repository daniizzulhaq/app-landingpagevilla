@props(['type' => 'general', 'property' => null, 'label' => 'Let’s Talk', 'variant' => 'gold'])
@php
    $waUrl = $whatsapp->url($type, $property);
    $displayLabel = ! $waUrl && str_contains(strtolower($label),'whatsapp') ? __('Contact Our Team') : __($label);
@endphp
<a href="{{ $waUrl ?? route('contact').'#contact-channels' }}" @if($waUrl) target="_blank" rel="noopener noreferrer" @endif data-contact-channel="{{ $waUrl ? 'whatsapp' : 'contact' }}" {{ $attributes->merge(['class' => 'button button-'.$variant]) }}><x-icon :name="$waUrl ? 'whatsapp' : 'mail'" class="h-5 w-5"/><span>{{ $displayLabel }}</span><x-icon name="arrow-up-right" class="h-3.5 w-3.5"/></a>
