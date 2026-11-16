@php
    $fieldId=str_replace(['[',']','.'],['_','','_'],$name);
    $value=old($name,$value ?? null);
    $conditional=($resource ?? '')==='properties' ? match($name){ 'max_guests'=>"listing === 'rent' && propertyType !== landId", 'bedrooms','bathrooms','building_area','floors','furnishing'=>"propertyType !== landId", 'price'=>"showPrice", default=>null } : null;
@endphp
<div class="{{ $definition['type']==='textarea' ? 'sm:col-span-2' : '' }}" @if($conditional) x-show="{{ $conditional }}" @endif>
@if($definition['type']==='checkbox')
    <input type="hidden" name="{{ $name }}" value="0"><label for="{{ $fieldId }}" class="flex min-h-12 cursor-pointer items-center gap-3 border border-line bg-ivory/40 px-4 py-3 text-xs leading-5 text-navy"><input type="checkbox" name="{{ $name }}" id="{{ $fieldId }}" value="1" @checked($value) class="h-4 w-4 accent-[#142635]" @if($name==='show_price') @change="showPrice=$event.target.checked" @endif>{{ $definition['label'] }}</label>
@else
    <label class="admin-label" for="{{ $fieldId }}">{{ $definition['label'] }}</label>
    @if($definition['type']==='textarea')<textarea name="{{ $name }}" id="{{ $fieldId }}" rows="{{ in_array($name,['content','description']) ? 9 : 4 }}" class="field" maxlength="50000">{{ $value }}</textarea>
    @elseif($definition['type']==='select')<select name="{{ $name }}" id="{{ $fieldId }}" class="field" @if($name==='listing_type') @change="listing=$event.target.value" @elseif($name==='property_type_id') @change="propertyType=$event.target.value" @endif>@foreach($definition['options'] as $optionValue=>$optionLabel)<option value="{{ $optionValue }}" @selected((string)$value===(string)$optionValue)>{{ $optionLabel }}</option>@endforeach</select>
    @elseif($definition['type']==='image')<div x-data="imagePreview()">@if($value && is_string($value))<img src="{{ \App\Support\Images::url($value) }}" class="mb-4 h-32 w-44 object-cover" width="176" height="128" alt="Current {{ $definition['label'] }}">@endif<input class="field text-xs file:mr-3 file:border-0 file:bg-ivory file:px-3 file:py-1.5 file:text-xs file:text-navy" type="file" name="{{ $name }}" id="{{ $fieldId }}" accept="image/jpeg,image/png,image/webp" @change="select($event)"><template x-for="url in previews" :key="url"><img :src="url" alt="New image preview" class="mt-3 h-32 w-44 object-cover"></template><p class="mt-2 text-[10px] text-muted">JPEG, PNG or WebP · up to 8 MB · 16 megapixels maximum. Converted to WebP.</p></div>
    @else<input name="{{ $name }}" id="{{ $fieldId }}" class="field" type="{{ $definition['type']==='decimal' ? 'number' : $definition['type'] }}" @if(in_array($definition['type'],['number','decimal'])) step="{{ in_array($name,['latitude','longitude']) ? '0.0000001' : ($definition['type']==='decimal' ? '0.01' : '1') }}" @unless(in_array($name,['latitude','longitude'])) min="0" @endunless @endif value="{{ $value }}" @if($name==='password' || $name==='current_password') autocomplete="{{ $name==='password' ? 'new-password' : 'current-password' }}" @endif>@endif
@endif
@error($name)<p class="mt-2 text-xs text-red-700">{{ $message }}</p>@enderror
</div>
