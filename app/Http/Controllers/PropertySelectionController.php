<?php

namespace App\Http\Controllers;

use App\Models\Property;
use Illuminate\Http\Request;

class PropertySelectionController extends Controller
{
    public function favorites() { return view('public.collection',['mode'=>'favorites']); }
    public function compare() { return view('public.collection',['mode'=>'compare']); }

    public function data(Request $request)
    {
        $validated=$request->validate(['ids'=>['nullable','string','max:5000','regex:/^\d+(,\d+)*$/'],'mode'=>['nullable','in:favorites,compare']]);
        $ids=array_values(array_unique(array_map('intval',array_filter(explode(',',$validated['ids'] ?? '')))));
        $limit=($validated['mode'] ?? 'favorites')==='compare' ? 3 : 200;
        abort_if(count($ids)>$limit,422,'Too many selected properties.');
        $properties=Property::published()->with(['images','location','propertyType','amenities'])->whereIn('id',$ids)->get()->keyBy('id');
        $items=[];
        foreach($ids as $id) {
            $property=$properties->get($id);
            if(!$property) continue;
            $items[]=[
                'id'=>$property->id,'title'=>$property->title,'url'=>route('properties.show',$property->slug),'image'=>$property->cover_url,
                'price'=>$property->formatted_price,'currency'=>$property->currency,'priceUnit'=>$property->price_unit,
                'location'=>$property->location->name.', Bali','type'=>$property->propertyType->name,
                'listingType'=>__($property->listing_type==='sale' ? 'For sale' : 'For rent'),
                'status'=>__(ucfirst($property->status)),'bedrooms'=>$property->bedrooms,'bathrooms'=>$property->bathrooms,
                'landArea'=>$property->land_area,'buildingArea'=>$property->building_area,'ownership'=>$property->ownership_type,
                'facilities'=>$property->amenities->pluck('name')->join(', '),
                'whatsapp'=>app(\App\Services\WhatsAppService::class)->url('general',$property),
                'whatsappLabel'=>__(in_array($property->status,['sold','rented','reserved']) ? 'Ask for Similar Properties' : 'Ask on WhatsApp'),
            ];
        }
        return response()->json(['properties'=>$items,'unavailable'=>count($ids)-count($items)])->header('Cache-Control','no-store');
    }
}
