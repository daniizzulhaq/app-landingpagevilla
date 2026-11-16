<?php
namespace App\Support;

use App\Models\{Property, PropertyType, Location, Amenity, Portfolio, PortfolioCategory, Service, Testimonial, Page};
use App\Services\MediaService;
use Illuminate\Validation\Rule;

class Resources
{
    public static function all(): array
    {
        return [
            'property-types' => [PropertyType::class, 'Property Categories'], 'locations' => [Location::class, 'Locations'],
            'amenities' => [Amenity::class, 'Amenities'], 'portfolio' => [Portfolio::class, 'Portfolio'],
            'portfolio-categories' => [PortfolioCategory::class, 'Portfolio Categories'], 'services' => [Service::class, 'Services'],
            'testimonials' => [Testimonial::class, 'Testimonials'], 'pages' => [Page::class, 'Pages'],
            'team' => [\App\Models\TeamMember::class, 'Company Team'],
        ];
    }
    public static function definition(string $key): array { return self::all()[$key] ?? abort(404); }
    public static function field(string $label, string $type = 'text', array $options = [], string $group = 'Content'): array
    {
        return compact('label', 'type', 'options', 'group');
    }
    public static function fields(string $key): array
    {
        $f = fn ($label, $type = 'text', $options = [], $group = 'Content') => self::field($label, $type, $options, $group);
        $seo = ['seo_title' => $f('SEO title (max. 70 characters)', 'text', [], 'SEO'), 'meta_description' => $f('Meta description (max. 170 characters)', 'textarea', [], 'SEO')];
        $publication = ['publication_status' => $f('Publication', 'select', ['draft' => 'Draft', 'published' => 'Published'], 'Publishing')];
        $named = ['name' => $f('Name'), 'slug' => $f('URL slug (generated if empty)')];
        return match ($key) {
            'team' => ['name'=>$f('Full name'),'role'=>$f('Position / role'),'bio'=>$f('Professional bio','textarea'),'photo'=>$f('Approved profile photo','image',[],'Media'),'sort_order'=>$f('Display order','number',[],'Publishing'),'is_active'=>$f('Approved for publication','checkbox',[],'Publishing')],
            'property-types', 'portfolio-categories' => $named,
            'locations' => $named + ['province' => $f('Province')],
            'amenities' => ['name' => $f('Name'), 'icon' => $f('Icon label (optional)')],
            'portfolio' => [
                'title' => $f('Project title'), 'slug' => $f('URL slug (generated if empty)'),
                'category_id' => $f('Category', 'select', PortfolioCategory::orderBy('name')->pluck('name', 'id')->all()),
                'location' => $f('Location'), 'description' => $f('Project story', 'textarea'),
                'project_year' => $f('Project year', 'number'), 'project_status' => $f('Project status'),
                'featured_image' => $f('Cover image', 'image', [], 'Media'), 'is_featured' => $f('Featured on homepage', 'checkbox', [], 'Publishing'),
            ] + $publication + $seo,
            'services' => [
                'title' => $f('Title'), 'hero_headline' => $f('Hero headline (optional)'), 'slug' => $f('URL slug (generated if empty)'), 'short_description' => $f('Short description', 'textarea'),
                'content' => $f('Service content (plain text)', 'textarea'),
                'workflow_steps' => $f('Workflow (one step per line)', 'textarea'), 'faqs' => $f('FAQ (one Question | Answer per line)', 'textarea'),
                'featured_image' => $f('Cover image', 'image', [], 'Media'),
                'icon' => $f('Icon label'), 'cta_label' => $f('WhatsApp button label'),
                'sort_order' => $f('Display order', 'number', [], 'Publishing'), 'is_active' => $f('Published', 'checkbox', [], 'Publishing'),
            ],
            'testimonials' => [
                'name' => $f('Client name'), 'role' => $f('Relationship / role'), 'content' => $f('Verified testimonial', 'textarea'),
                'rating' => $f('Rating (optional)', 'select', ['' => 'No rating', 1 => '1', 2 => '2', 3 => '3', 4 => '4', 5 => '5']),
                'source' => $f('Source / attribution'), 'photo' => $f('Profile photo', 'image', [], 'Media'), 'is_active' => $f('Verified and approved for publication', 'checkbox', [], 'Publishing'),
            ],
            'pages' => ['title' => $f('Title'), 'slug' => $f('URL slug (keep core page slugs stable)'), 'content' => $f('Page content (plain text)', 'textarea'), 'featured_image' => $f('Cover image', 'image', [], 'Media'), 'is_indexable' => $f('Allow search indexing (disable for editorial drafts)', 'checkbox', [], 'Publishing')] + $publication + $seo,
            default => [],
        };
    }
    public static function rules(string $key, ?int $id): array
    {
        [$model] = self::definition($key); $table = (new $model)->getTable(); $rules = [];
        foreach (self::fields($key) as $name => $field) {
            $rules[$name] = match ($field['type']) {
                'checkbox' => ['required', 'boolean'], 'image' => MediaService::RULES,
                'textarea' => ['nullable', 'string', 'max:50000'],
                'number' => ['nullable', 'integer', 'min:0', 'max:9999'],
                'select' => ['required', Rule::in(array_keys($field['options']))],
                default => ['nullable', 'string', 'max:255'],
            };
        }
        foreach (['name', 'title', 'description', 'content', 'short_description', 'cta_label'] as $required) {
            if (isset($rules[$required])) { $rules[$required][0] = 'required'; }
        }
        if (isset($rules['slug'])) { $rules['slug'] = ['required', 'max:200', 'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/', Rule::unique($table)->ignore($id)]; }
        if ($key === 'locations') { $rules['province'] = ['required', 'string', 'max:255']; }
        if ($key === 'portfolio') {
            $rules['category_id'] = ['required', 'exists:portfolio_categories,id'];
            $rules['project_year'] = ['nullable', 'integer', 'between:1900,2100'];
            $rules += ['gallery' => ['nullable', 'array', 'max:20'], 'gallery.*' => MediaService::RULES, 'images' => ['nullable', 'array'], 'images.*.alt_text' => ['nullable', 'string', 'max:255'], 'images.*.sort_order' => ['required', 'integer', 'between:0,1000'], 'remove_images' => ['nullable', 'array'], 'remove_images.*' => ['integer']];
        }
        if ($key === 'testimonials') { $rules['rating'] = ['nullable', 'integer', 'between:1,5']; }
        if (isset($rules['seo_title'])) { $rules['seo_title'] = ['nullable', 'string', 'max:70']; $rules['meta_description'] = ['nullable', 'string', 'max:170']; }
        if ($key==='team') $rules['role']=['required','string','max:255'];
        return $rules + TranslationFields::rules($model);
    }
    public static function propertyFields(): array
    {
        $f = fn ($label, $type = 'text', $options = [], $group = 'Basic information') => self::field($label, $type, $options, $group);
        $enum = fn ($values) => array_combine($values, array_map(fn ($v) => ucfirst($v), $values));
        return [
            'title' => $f('Property name'), 'slug' => $f('URL slug (generated if empty)'), 'property_code' => $f('Unique property code'),
            'property_type_id' => $f('Property type', 'select', PropertyType::orderBy('name')->pluck('name', 'id')->all()),
            'listing_type' => $f('Listing type', 'select', ['sale' => 'For Sale', 'rent' => 'For Rent']),
            'short_description' => $f('Short description', 'textarea'), 'description' => $f('Full description (plain text)', 'textarea'),
            'price' => $f('Price', 'decimal', [], 'Pricing'), 'currency' => $f('Currency', 'select', array_combine(config('propertyme.currencies'), config('propertyme.currencies')), 'Pricing'),
            'price_unit' => $f('Price unit', 'select', ['' => 'Total price', 'night' => 'Per night', 'month' => 'Per month', 'year' => 'Per year', 'are' => 'Per are', 'm²' => 'Per m²'], 'Pricing'),
            'show_price' => $f('Display price (otherwise: Price on request)', 'checkbox', [], 'Pricing'),
            'location_id' => $f('Area', 'select', Location::orderBy('name')->pluck('name', 'id')->all(), 'Location'),
            'location_description' => $f('Location details / address', 'textarea', [], 'Location'),
            'latitude' => $f('Latitude', 'decimal', [], 'Location'), 'longitude' => $f('Longitude', 'decimal', [], 'Location'), 'show_exact_location' => $f('Show exact location on public map', 'checkbox', [], 'Location'),
            'bedrooms' => $f('Bedrooms (leave empty for land)', 'number', [], 'Specifications'), 'bathrooms' => $f('Bathrooms', 'number', [], 'Specifications'),
            'land_area' => $f('Land area (m²)', 'decimal', [], 'Specifications'), 'building_area' => $f('Building area (m²)', 'decimal', [], 'Specifications'),
            'floors' => $f('Floors', 'number', [], 'Specifications'), 'max_guests' => $f('Maximum guests (villa rental only)', 'number', [], 'Specifications'),
            'furnishing' => $f('Furnishing', 'text', [], 'Specifications'), 'ownership_type' => $f('Legal / ownership information', 'text', [], 'Specifications'),
            'development_status' => $f('Development status', 'select', ['' => 'Not specified', 'completed' => 'Completed', 'off-plan' => 'Off-plan', 'under-construction' => 'Under construction'], 'Specifications'),
            'year_built' => $f('Year built', 'number', [], 'Specifications'), 'lease_years' => $f('Lease years (if applicable)', 'number', [], 'Specifications'), 'handover' => $f('Expected handover (e.g. Q4 2027)', 'text', [], 'Specifications'),
            'status' => $f('Availability', 'select', $enum(['available', 'reserved', 'sold', 'rented', 'inactive']), 'Publishing'),
            'publication_status' => $f('Publication', 'select', $enum(['draft', 'published']), 'Publishing'), 'is_featured' => $f('Featured on homepage', 'checkbox', [], 'Publishing'),
            'seo_title' => $f('SEO title (max. 70 characters)', 'text', [], 'SEO'), 'meta_description' => $f('Meta description (max. 170 characters)', 'textarea', [], 'SEO'), 'canonical_url' => $f('Canonical URL (optional)', 'url', [], 'SEO'),
        ];
    }
}
