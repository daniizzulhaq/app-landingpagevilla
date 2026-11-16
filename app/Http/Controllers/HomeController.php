<?php

namespace App\Http\Controllers;

use App\Models\{Property, Portfolio, Service, Testimonial, Location, PropertyType};
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class HomeController extends Controller
{
    public function __invoke(Request $request)
    {
        $filters = $request->validate([
            'featured_listing' => ['nullable', Rule::in(['sale', 'rent'])],
            'featured_collection' => ['nullable', Rule::in(['all', 'leasehold', 'freehold', 'land'])],
        ]);
        $featuredListing = $filters['featured_listing'] ?? 'sale';
        $featuredCollection = $filters['featured_collection'] ?? 'all';
        $query = Property::published()->with(['images', 'location', 'propertyType'])->where('is_featured', true)->where('listing_type', $featuredListing);
        if (in_array($featuredCollection, ['leasehold', 'freehold'])) {
            $query->where('ownership_type', ucfirst($featuredCollection));
        } elseif ($featuredCollection === 'land') {
            $query->whereHas('propertyType', fn ($q) => $q->where('slug', 'land'));
        }

        return view('public.home', [
            'properties' => $query->latest('published_at')->limit(6)->get(),
            'portfolios' => Portfolio::published()->with('category')->where('is_featured', true)->latest()->limit(3)->get(),
            'services' => Service::where('is_active', true)->orderBy('sort_order')->get(),
            'testimonials' => Testimonial::where('is_active', true)->where('is_demo', false)->latest()->limit(6)->get(),
            'locations' => Location::orderBy('name')->get(),
            'types' => PropertyType::orderBy('name')->get(),
            'featuredListing' => $featuredListing,
            'featuredCollection' => $featuredCollection,
        ]);
    }
}
