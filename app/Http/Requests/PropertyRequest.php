<?php
namespace App\Http\Requests;
use App\Services\MediaService;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
class PropertyRequest extends FormRequest
{
    public function authorize(): bool { return $this->user()?->can('access-admin') ?? false; }
    protected function prepareForValidation(): void
    {
        $this->merge(['slug' => Str::slug($this->input('slug') ?: $this->input('title')), 'currency' => strtoupper($this->input('currency', 'IDR'))]);
        if ($this->input('listing_type') !== 'rent') { $this->merge(['max_guests' => null]); }
        if (\App\Models\PropertyType::whereKey($this->input('property_type_id'))->where('slug', 'land')->exists()) {
            $this->merge(array_fill_keys(['bedrooms', 'bathrooms', 'building_area', 'floors', 'furnishing', 'max_guests'], null));
        }
    }
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'], 'slug' => ['required', 'max:200', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique('properties')->ignore($this->route('property')?->id)],
            'property_code' => ['required', 'string', 'max:100', Rule::unique('properties')->ignore($this->route('property')?->id)],
            'property_type_id' => ['required', 'exists:property_types,id'], 'location_id' => ['required', 'exists:locations,id'],
            'listing_type' => ['required', Rule::in(['sale', 'rent'])], 'description' => ['required', 'string', 'max:50000'],
            'short_description' => ['nullable', 'string', 'max:600'], 'price' => ['nullable', 'required_if:show_price,1', 'numeric', 'min:0', 'max:9999999999999999'],
            'currency' => ['required', Rule::in(config('propertyme.currencies'))], 'price_unit' => ['nullable', Rule::in(['night', 'month', 'year', 'are', 'm²'])],
            'show_price' => ['required', 'boolean'], 'bedrooms' => ['nullable', 'integer', 'between:0,500'], 'bathrooms' => ['nullable', 'integer', 'between:0,500'],
            'land_area' => ['nullable', 'numeric', 'between:0,9999999999'], 'building_area' => ['nullable', 'numeric', 'between:0,9999999999'],
            'floors' => ['nullable', 'integer', 'between:0,500'], 'max_guests' => ['nullable', 'integer', 'between:0,1000'],
            'furnishing' => ['nullable', 'string', 'max:255'], 'ownership_type' => ['nullable', 'string', 'max:255'],
            'location_description' => ['nullable', 'string', 'max:3000'], 'latitude' => ['nullable', 'required_with:longitude', 'numeric', 'between:-90,90'], 'longitude' => ['nullable', 'required_with:latitude', 'numeric', 'between:-180,180'],
            'show_exact_location' => ['required', 'boolean'], 'status' => ['required', Rule::in(['available', 'reserved', 'sold', 'rented', 'inactive'])],
            'publication_status' => ['required', Rule::in(['draft', 'published'])], 'is_featured' => ['required', 'boolean'],
            'development_status' => ['nullable', Rule::in(['completed', 'off-plan', 'under-construction'])],
            'year_built' => ['nullable', 'integer', 'between:1900,2100'], 'lease_years' => ['nullable', 'integer', 'between:1,1000'], 'handover' => ['nullable', 'string', 'max:100'],
            'seo_title' => ['nullable', 'string', 'max:70'], 'meta_description' => ['nullable', 'string', 'max:170'], 'canonical_url' => ['nullable', 'url:http,https', 'max:255'],
            'amenities' => ['nullable', 'array'], 'amenities.*' => ['integer', 'exists:amenities,id'],
            'gallery' => ['nullable', 'array', 'max:20'], 'gallery.*' => MediaService::RULES,
            'images' => ['nullable', 'array'], 'images.*.alt_text' => ['nullable', 'string', 'max:255'], 'images.*.sort_order' => ['required', 'integer', 'between:0,1000'],
            'primary_image' => ['nullable', 'integer'], 'remove_images' => ['nullable', 'array'], 'remove_images.*' => ['integer'],
        ] + \App\Support\TranslationFields::rules(\App\Models\Property::class);
    }
}
