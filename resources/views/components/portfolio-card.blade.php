@props(['project'])
<article class="group min-w-0">
    <a href="{{ route('portfolio.show',$project->slug) }}" class="relative block aspect-[4/3] overflow-hidden rounded-2xl bg-line"><img src="{{ $project->featured_image ? \App\Support\Images::url($project->featured_image) : asset('images/architecture.svg') }}" data-fallback="{{ asset('images/architecture.svg') }}" alt="{{ $project->title }}" width="800" height="600" loading="lazy" class="image-zoom"></a>
    <p class="mt-5 text-[9px] uppercase tracking-[.17em] text-muted">{{ $project->category->name }} @if($project->location) · {{ $project->location }} @endif</p>
    <a href="{{ route('portfolio.show',$project->slug) }}" class="mt-2 flex items-center justify-between gap-5"><h3 class="text-3xl leading-tight text-navy">{{ $project->title }}</h3><x-icon class="h-5 w-5 text-gold"/></a>
</article>
