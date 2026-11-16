<?php
namespace Database\Factories;
use App\Models\{Property, PropertyType, Location};
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
class PropertyFactory extends Factory
{
    protected $model = Property::class;
    public function definition(): array
    {
        return ['property_code' => 'TEST-'.Str::upper(Str::random(10)), 'title' => 'Test Property '.Str::random(6), 'slug' => 'test-property-'.Str::lower(Str::random(12)), 'property_type_id' => PropertyType::firstOrCreate(['slug'=>'villa'],['name'=>'Villa'])->id, 'location_id' => Location::firstOrCreate(['slug'=>'canggu'],['name'=>'Canggu','province'=>'Bali'])->id, 'listing_type'=>'sale', 'description'=>'This is an isolated automated-test fixture, not a production listing.', 'price'=>'2500000000.00', 'currency'=>'IDR', 'show_price'=>true, 'bedrooms'=>3, 'bathrooms'=>3, 'land_area'=>400, 'building_area'=>250, 'status'=>'available', 'publication_status'=>'published', 'published_at'=>now()->subMinute(), 'is_featured'=>false, 'show_exact_location'=>false];
    }
    public function draft(): static { return $this->state(fn()=>['publication_status'=>'draft','published_at'=>null]); }
}
