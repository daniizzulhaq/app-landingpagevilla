<?php
namespace App\Http\Controllers;
use App\Models\{Property, PropertyType, Location};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
class PropertyController extends Controller
{
    public function index(Request $request)
    {
        if (($request->filled('min_price') || $request->filled('max_price') || in_array($request->query('sort'), ['price_asc', 'price_desc'])) && ! $request->filled('currency')) { $request->merge(['currency' => 'IDR']); }
        $filters = $request->validate([
            'q' => ['nullable', 'string', 'max:100'], 'location' => ['nullable', 'integer', 'exists:locations,id'], 'property_type' => ['nullable', 'integer', 'exists:property_types,id'],
            'listing_type' => ['nullable', Rule::in(['sale', 'rent'])], 'currency' => ['nullable', Rule::in(config('propertyme.currencies'))],
            'ownership_type' => ['nullable', Rule::in(['Leasehold','Freehold'])], 'development_status' => ['nullable', Rule::in(['completed','off-plan','under-construction'])], 'price_unit' => ['nullable', Rule::in(['night','month','year'])],
            'min_price' => ['nullable', 'numeric', 'min:0'], 'max_price' => ['nullable', 'numeric', 'min:0'],
            'bedrooms' => ['nullable', 'integer', 'between:0,500'], 'sort' => ['nullable', Rule::in(['newest', 'price_asc', 'price_desc'])],
        ]);
        $query = Property::published()->with(['images', 'location', 'propertyType']);
        if (! empty($filters['q'])) {
            $q = '%'.$filters['q'].'%';
            $query->where(function ($sub) use ($q) {
                $sub->where('title','like',$q)->orWhere('short_description','like',$q)->orWhere('property_code','like',$q);
                if (app()->getLocale()!=='en') {
                    $sub->orWhereHas('contentTranslations',fn($translated)=>$translated->where('locale',app()->getLocale())
                        ->where(fn($fields)=>$fields->where('fields->title','like',$q)->orWhere('fields->short_description','like',$q)));
                }
            });
        }
        foreach (['location' => 'location_id', 'property_type' => 'property_type_id', 'listing_type' => 'listing_type', 'currency' => 'currency', 'ownership_type'=>'ownership_type', 'development_status'=>'development_status', 'price_unit'=>'price_unit'] as $filter => $column) { if (! empty($filters[$filter])) { $query->where($column, $filters[$filter]); } }
        if (isset($filters['bedrooms'])) { $query->where('bedrooms', '>=', $filters['bedrooms']); }
        if (isset($filters['min_price'])) { $query->where('show_price', true)->where('price', '>=', $filters['min_price']); }
        if (isset($filters['max_price'])) { $query->where('show_price', true)->where('price', '<=', $filters['max_price']); }
        $sort = $filters['sort'] ?? 'newest';
        if ($sort !== 'newest') { $query->orderByRaw('CASE WHEN show_price = 1 AND price IS NOT NULL THEN 0 ELSE 1 END')->orderByRaw('CASE WHEN show_price = 1 THEN price ELSE NULL END '.($sort === 'price_asc' ? 'ASC' : 'DESC')); }
        $properties = $query->orderByDesc('published_at')->orderByDesc('id')->paginate(9)->withQueryString();
        return view('public.properties', compact('properties', 'filters') + ['locations' => Location::orderBy('name')->get(), 'types' => PropertyType::orderBy('name')->get(), 'buyRent' => $request->routeIs('buy-rent')]);
    }
    public function show(string $slug)
    {
        $property = Property::published()->with(['images', 'location', 'propertyType', 'amenities'])->where('slug', $slug)->firstOrFail();
        $related = Property::published()->with(['images', 'location', 'propertyType'])->whereKeyNot($property->id)->where('listing_type', $property->listing_type)->where('location_id', $property->location_id)->latest('published_at')->limit(3)->get();
        return view('public.property', compact('property', 'related'));
    }
}
