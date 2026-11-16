@props(['kind' => 'property'])
<div {{ $attributes->merge(['class'=>'mb-7 flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-5 py-4 text-xs leading-6 text-amber-950']) }} role="note">
    <x-icon name="image" class="mt-1 h-4 w-4 shrink-0"/><p><strong>Demo {{ $kind }}.</strong> This is illustrative preview content, not a real listing, completed project or client review. Images are AI-generated; prices and specifications are examples.</p>
</div>
