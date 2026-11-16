@php
    $settings = app(\App\Services\Settings::class);
    $whatsapp = app(\App\Services\WhatsAppService::class);
    $hostedPreview = \App\Support\PublicContent::hostedDemoEnabled();
    $demoMode = $hostedPreview || (app()->environment(['local','testing']) && $settings->enabled('demo.content_enabled'));
    $metaTitle = trim($__env->yieldContent('title')) ?: $settings->get('seo.title', 'PropertyME | Bali Real Estate');
    $metaDescription = trim($__env->yieldContent('description')) ?: $settings->get('seo.description');
    $canonical = trim($__env->yieldContent('canonical')) ?: url()->current();
    if(app()->getLocale()==='id') $canonical .= (str_contains($canonical,'?') ? '&' : '?').'lang=id';
    $filteredQuery = request()->except('lang');
    $ogImage = trim($__env->yieldContent('og_image')) ?: ($settings->get('seo.og_image') ? \App\Support\Images::url($settings->get('seo.og_image')) : asset('images/social-card.png'));
    $organization = ['@context'=>'https://schema.org','@type'=>'Organization','name'=>$settings->get('company.name','PropertyME'),'url'=>url('/')];
    if ($settings->get('company.email')) $organization['email'] = $settings->get('company.email');
    if ($settings->get('company.whatsapp')) $organization['telephone'] = '+'.$settings->get('company.whatsapp');
    if ($settings->get('company.instagram')) $organization['sameAs'] = [$settings->get('company.instagram')];
    $organization['logo'] = $settings->get('company.logo') ? \App\Support\Images::url($settings->get('company.logo')) : asset('images/propertyme-logo.png');
@endphp
<!DOCTYPE html><html lang="{{ app()->getLocale() }}"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1"><meta name="theme-color" content="#142635"><title>{{ $metaTitle }}</title><meta name="description" content="{{ $metaDescription }}"><link rel="canonical" href="{{ $canonical }}">
@if($demoMode || $filteredQuery || View::hasSection('noindex'))<meta name="robots" content="noindex,follow">@else<meta name="robots" content="index,follow">@endif
@unless(View::hasSection('noindex'))<link rel="alternate" hreflang="en" href="{{ url()->current() }}"><link rel="alternate" hreflang="id" href="{{ url()->current() }}?lang=id"><link rel="alternate" hreflang="x-default" href="{{ url()->current() }}">@endunless
<meta property="og:locale" content="{{ app()->getLocale()==='id' ? 'id_ID' : 'en_US' }}"><meta property="og:type" content="website"><meta property="og:site_name" content="{{ $settings->get('company.name','PropertyME') }}"><meta property="og:title" content="{{ $metaTitle }}"><meta property="og:description" content="{{ $metaDescription }}"><meta property="og:url" content="{{ $canonical }}"><meta property="og:image" content="{{ $ogImage }}"><meta name="twitter:card" content="summary_large_image"><meta name="twitter:title" content="{{ $metaTitle }}"><meta name="twitter:description" content="{{ $metaDescription }}"><meta name="twitter:image" content="{{ $ogImage }}">
<link rel="icon" type="{{ $settings->get('company.favicon') ? 'image/webp' : 'image/png' }}" href="{{ $settings->get('company.favicon') ? \App\Support\Images::url($settings->get('company.favicon')) : asset('images/propertyme-logo.png') }}">
<link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link rel="preconnect" href="https://images.unsplash.com"><link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400;1,500&family=Inter:wght@300;400;500;600&display=swap" rel="stylesheet">
@vite(['resources/css/app.css','resources/js/app.js'])
<script type="application/ld+json">{!! json_encode($organization, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES) !!}</script>@stack('head')
</head><body class="{{ request()->routeIs('properties.show') ? 'pb-24 sm:pb-0' : '' }}"><a href="#main-content" class="sr-only z-50 bg-gold p-4 text-navy focus:fixed focus:left-4 focus:top-4 focus:not-sr-only">{{ __('Skip to content') }}</a><x-navbar/><main id="main-content">@if($hostedPreview)<div class="container-wide pt-10"><x-demo-notice kind="content"/></div>@endif @yield('content')</main><x-footer/>
<x-contact-widget :property="request()->routeIs('properties.show') ? ($property ?? null) : null"/>
<x-comparison-bar/>
@stack('scripts')</body></html>
