@props(['testimonial'])
<figure class="border border-line bg-white p-8">
    @if($testimonial->is_demo)<p class="mb-4 text-[9px] font-semibold uppercase tracking-widest text-amber-800">Demo text · Not a client review</p>@endif
    <span class="font-serif text-6xl leading-none text-gold" aria-hidden="true">“</span>
    <blockquote class="mt-3 font-serif text-2xl leading-relaxed text-navy">{{ $testimonial->content }}</blockquote>
    <figcaption class="mt-8 flex items-start gap-4 border-t border-line pt-5 text-xs">
        @if($testimonial->photo)<img src="{{ \App\Support\Images::url($testimonial->photo) }}" alt="{{ $testimonial->name }}" width="48" height="48" loading="lazy" class="h-12 w-12 shrink-0 rounded-full object-cover">@endif
        <div><span class="font-semibold">{{ $testimonial->name }}</span>
            @if($testimonial->role)<span class="mt-1 block text-muted">{{ $testimonial->role }}</span>@endif
            @if($testimonial->rating)<span class="mt-2 block text-gold" aria-label="{{ $testimonial->rating }} out of 5 stars">{{ str_repeat('★',$testimonial->rating) }}</span>@endif
            @if($testimonial->source)<span class="mt-2 block text-[10px] text-muted">Source: {{ $testimonial->source }}</span>@endif
        </div>
    </figcaption>
</figure>
