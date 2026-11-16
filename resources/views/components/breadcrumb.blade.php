@props(['items' => []])
<nav aria-label="Breadcrumb" class="mb-8 flex flex-wrap items-center gap-2 text-[10px] tracking-wide text-muted"><a href="{{ route('home') }}" class="hover:text-navy">{{ __('Home') }}</a>@foreach($items as $label => $url)<span aria-hidden="true">/</span>@if($url)<a href="{{ $url }}" class="hover:text-navy">{{ __($label) }}</a>@else<span aria-current="page" class="text-navy">{{ __($label) }}</span>@endif @endforeach</nav>
@php $crumbs = [['@type' => 'ListItem', 'position' => 1, 'name' => 'Home', 'item' => route('home')]]; @endphp
@foreach($items as $label => $url) @php $crumbs[] = ['@type' => 'ListItem', 'position' => count($crumbs)+1, 'name' => $label, 'item' => $url ?: url()->current()]; @endphp @endforeach
<script type="application/ld+json">{!! json_encode(['@context'=>'https://schema.org','@type'=>'BreadcrumbList','itemListElement'=>$crumbs], JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_SLASHES) !!}</script>
