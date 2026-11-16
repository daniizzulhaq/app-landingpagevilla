@php $translationFields=\App\Support\TranslationFields::for(get_class($record)); @endphp
@if($translationFields)
<section class="admin-card"><div class="flex flex-wrap items-start justify-between gap-4"><div><h2 class="text-2xl text-navy">Indonesian Content</h2><p class="mt-3 text-xs leading-6 text-muted">Displayed when visitors select ID. Leave a field empty to use the English content. Slugs, prices and contact details stay shared; translate display titles only when needed.</p></div><span class="status-badge">Bahasa Indonesia</span></div><div class="mt-7 grid gap-6 sm:grid-cols-2">
@php $translated=$record->exists ? $record->translationFields('id') : []; @endphp
@foreach($translationFields as $translationField)
<div class="{{ in_array($translationField,['content','description','short_description','bio','faqs','workflow_steps','meta_description']) ? 'sm:col-span-2' : '' }}"><label for="translation-{{ $translationField }}" class="admin-label">{{ Str::headline($translationField) }} · Indonesia</label>
@if(in_array($translationField,['content','description','short_description','bio','faqs','workflow_steps','meta_description']))<textarea id="translation-{{ $translationField }}" name="translations[{{ $translationField }}]" rows="{{ in_array($translationField,['content','description']) ? 7 : 3 }}" class="field">{{ old('translations.'.$translationField,$translated[$translationField] ?? '') }}</textarea>@else<input id="translation-{{ $translationField }}" name="translations[{{ $translationField }}]" class="field" value="{{ old('translations.'.$translationField,$translated[$translationField] ?? '') }}">@endif
@error('translations.'.$translationField)<p class="mt-2 text-xs text-red-700">{{ $message }}</p>@enderror</div>
@endforeach</div></section>
@endif
